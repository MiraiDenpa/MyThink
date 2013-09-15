<?php
/**
 * 使用预定义方法过滤：
 * 		eg. 过滤器 test
 * 		1. 定义方法
 * 		protected function filter_test([&]变量值，[&]参数)
 * 		2. 返回值是新的变量值，返回false表示出错（禁止程序执行）
 * 		3. 使用： $post->filter('varname', 'test', $args)
 * 检查方法于过滤相同，只是filter改成valid
 * 
 * @return $this
 */
abstract class InputStream{
	protected $_DATA;
	protected $parsed;
	protected $is_array;
	protected $is_opt = [];

	/** @var callable */
	protected $callback_error = ['Think', 'fail_error'];

	/**
	 * 替换默认错误回调
	 * @param callable $handler
	 *
	 * @return $this
	 */
	public function handel(callable $handler){
		$this->callback_error = $handler;
		return $this;
	}

	public static function _getInstance($type){
		$type = (ucfirst($type) . 'InputStream');
		return new $type;
	}

	/**
	 * 返回所有处理过的元素的数组
	 * @return array
	 */
	public function getAll(){
		return $this->parsed;
	}

	/**
	 * 返回一个元素，这个元素必须用某个方法处理过
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get($name){
		if(!isset($this->parsed[$name])){
			Think::halt('未处理变量被使用：' . $name);
		}
		return $this->parsed[$name];
	}

	/**
	 * 处理可选变量
	 * @param        $name
	 * @param string $default
	 *
	 * @return $this
	 */
	public function optional($name, $default = ''){
		if(isset($this->_DATA[$name])){
			$this->parsed[$name] = $this->_DATA[$name];
		} else{
			$this->is_opt[$name] = true;
			$this->parsed[$name] = $default;
		}
		return $this;
	}

	/**
	 * 批量处理可选变量
	 * @param $names
	 *
	 * @return $this
	 */
	public function optionalAll($names){
		foreach($names as $name => $default){
			if(isset($this->_DATA[$name])){
				$this->parsed[$name] = $this->_DATA[$name];
			} else{
				$this->is_opt[$name] = true;
				$this->parsed[$name] = $default;
			}
		}
		return $this;
	}

	/**
	 * 处理可选数组变量
	 * @param        $name
	 *
	 * @return $this
	 */
	public function optionalArray($name){
		if(!isset($this->_DATA[$name])){
			$this->is_opt[$name] = true;
			$this->parsed[$name] = [];
		} elseif(!is_array($this->_DATA[$name])){
			$this->is_opt[$name] = true;
			$this->parsed[$name] = [];
		} else{
			$this->parsed[$name] = $this->_DATA[$name];
		}
		$is_array[$name] = true;
		return $this;
	}

	/**
	 * 处理必须变量（不存在产生严重错误）
	 * @param $name
	 *
	 * @return $this
	 */
	public function required($name){
		if(!isset($this->_DATA[$name])){
			$call = $this->callback_error;
			$call(ERR_INPUT_REQUIRE, $name);
		} elseif(is_array($this->_DATA[$name])){
			$call = $this->callback_error;
			$call(ERR_INPUT_TYPE, $name);
		}
		$this->parsed[$name] = $this->_DATA[$name];
		return $this;
	}

	/**
	 * 批量处理必须变量（不存在产生严重错误）
	 * @param $names
	 *
	 * @return $this
	 */
	public function requireAll($names){
		foreach($names as $name){
			if(!isset($this->_DATA[$name])){
				$call = $this->callback_error;
				$call(ERR_INPUT_REQUIRE, $name);
			} elseif(is_array($this->_DATA[$name])){
				$call = $this->callback_error;
				$call(ERR_INPUT_TYPE, $name);
			}
			$this->parsed[$name] = $this->_DATA[$name];
		}
		return $this;
	}

	/**
	 * 处理必须数组变量（不存在产生严重错误，但可以空的）
	 * @param $name
	 *
	 * @return $this
	 */
	public function requiredArray($name){
		if(!isset($this->_DATA[$name])){
			$call = $this->callback_error;
			$call(ERR_INPUT_REQUIRE, $name);
		} elseif(!is_array($this->_DATA[$name])){
			$call = $this->callback_error;
			$call(ERR_INPUT_TYPE, $name);
		}
		$is_array[$name]     = true;
		$this->parsed[$name] = $this->_DATA[$name];
		return $this;
	}

	/**
	 * 使用预定义方法过滤
	 *
	 * @param string     $name   变量名
	 * @param string|int $filter 过滤器名称（类型）
	 * @param mixed      $args   给过滤器的参数（只能传一个）
	 * @param int        $err    错误类型
	 *
	 * @return $this
	 */
	public function filter($name, $filter, $args = null, $err = ERR_INPUT_DENY){
		if(isset($this->is_opt[$name])){
			return $this;
		}
		if(is_int($filter)){
			$ret = filter_var($this->parsed[$name], $filter, $args);
		} else{
			$filter = 'filter_' . $filter;
			$ret    = $this->$filter($this->parsed[$name], $args);
		}
		if(false === $ret){
			$call = $this->callback_error;
			$call($err, $name);
		} else{
			$this->parsed[$name] = $ret;
		}
		return $this;
	}
	/**
	 * 使用预定义方法检查
	 *
	 * @param string     $name   变量名
	 * @param string|int $filter 过滤器名称（类型）
	 * @param mixed      $args   给过滤器的参数（只能传一个）
	 * @param int        $err    错误类型
	 *
	 * @return $this
	 */
	public function valid($name, $filter, $args = null, $err = ERR_INPUT_DENY){
		if(isset($this->is_opt[$name])){
			return $this;
		}
		if(is_int($filter)){
			$ret = filter_var($this->parsed[$name], $filter, $args);
		} else{
			$filter = 'valid_' . $filter;
			$ret    = $this->$filter($this->parsed[$name], $args);
		}
		if(false === $ret){
			$call = $this->callback_error;
			$call($err, $name);
		}
		return $this;
	}

	/**
	 * 使用回调函数过滤
	 *
	 * @param string   $name
	 * @param callable $cb  只有一个参数，返回过滤后的值，返回$expect为失败
	 * @param int      $err 错误类型
	 *
	 * @return $this
	 **/
	public function filter_callback($name, callable $cb, $err = ERR_INPUT_DENY){
		if(isset($this->is_opt[$name])){
			return $this;
		}
		$ret = filter_var($this->parsed[$name], FILTER_CALLBACK, ['options' => $cb]);
		if(!$ret){
			$call = $this->callback_error;
			$call($err, $name);
		}
		return $this;
	}

	/**
	 * 使用回调函数强制
	 *
	 * @param string   $name
	 * @param callable $cb 只有一个参数，返回过滤后的值，返回$expect为失败
	 *
	 * @return $this
	 **/
	public function sanitize_callback($name, callable $cb){
		if(isset($this->is_opt[$name])){
			return $this;
		}
		$ret = filter_var($this->parsed[$name], FILTER_CALLBACK, ['options' => $cb]);
		if(false === $ret){
			$call = $this->callback_error;
			$call(ERR_INPUT_DENY, $name);
		}
		$this->parsed[$name] = $ret;
		return $this;
	}

	/**
	 * 进行正则过滤
	 *
	 * @param string $name
	 * @param string $regex
	 *
	 * @return $this
	 *
	public function preg($name, $regex){
	if(isset($this->is_array[$name])){
	if(count($this->parsed[$name]) != count(preg_filter($regex, '1', $this->parsed[$name]))){
	throw_exception(ERR_NOTALLOW_PARAM);
	}
	} else{
	if(!preg_match($regex, $this->parsed[$name])){
	throw_exception(ERR_NOTALLOW_PARAM);
	}
	}

	return $this;
	}*/

	/**
	 * 被过滤的变量是用户发布的文本，需要组合过滤
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function content($name){
		if(isset($this->is_opt[$name])){
			return $this;
		}
		// TODO 完成
		return $this;
	}
}
