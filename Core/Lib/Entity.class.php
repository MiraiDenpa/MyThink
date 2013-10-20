<?php
abstract class Entity implements ArrayAccess{
	/**
	 * 构造一个实体
	 * @param array $array
	 *
	 * @return $this
	 * @static
	 */
	public static function buildFromArray($array){
		$cls = get_called_class();
		$obj = new $cls;
		foreach($array as $k => $v){
			$obj->$k = $v;
		}
		$obj->_init();
		return $obj;
	}
	
	protected function _init(){}

	public function mpath($path){
		$path = explode('.', $path);
		$base = array_shift($path);
		$var  = $this->$base;
		while(is_array($var) && isset($var[$path[0]])){
			$var = $var[array_shift($path)];
		}
		return empty($path)? $var : null;
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset){
		return isset($this->$offset);
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset){
		return $this->$offset;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value){
		$this->$offset = $value;
	}

	/**
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetUnset($offset){
		unset($this->$offset);
	}

	/**
	 *
	 * @return array
	 */
	public function toArray(){
		$data = get_object_vars($this);
		$ret  = array_merge($data, $data['data']);
		unset($ret['data'], $ret['exist']);
		return $ret;
	}
}
