<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP内置的Dispatcher类
 * 完成URL解析、路由和调度
 * @category    Think
 * @package     Think
 * @subpackage  Core
 * @author      liu21st <liu21st@gmail.com>
 */
class Dispatcher{
	public $action_name = DEFAULT_ACTION;
	public $method_name;
	public $default_action = false;
	public $default_method = false;
	public $request_method = REQUEST_METHOD;
	public $extension_name = 'html';
	public $param = [];
	protected $meta;
	protected $GET = [];
	protected $action = '';
	/**
	 * @var callable
	 */
	protected $callback;

	public function __construct(){
		if($this->extension_name == 'form'){
			$this->callback = [&$this, 'return_json'];
		} else{
			$this->callback = [&$this, 'return_' . $this->extension_name];
		}
	}

	/**
	 * 解析URL，然后执行操作
	 *
	 * @param $pathinfo
	 *
	 * @access public
	 * @return mixed
	 */
	public function dispatch($pathinfo){
		$paths = $this->parse_path($pathinfo);

		$_GET     = $paths;
		$_REQUEST = array_merge($_GET, $_POST);

		return $this->run();
	}

	/**
	 * 重新执行上一个操作
	 *
	 * @return mixed
	 */
	public function run(){
		if(!$this->action){
			if(!$this->action_name){
				Think::halt('没有调用过parse_path。');
			} else{
				$this->action = ThinkInstance::A($this->action_name, $this);
				if(!$this->action_name){
					_404(LANG_ACTION_NOT_EXIST . ':' . $this->action_name);
				}
			}
		}

		$mtd = $this->method_name;
		$ret = [&$this->action, &$mtd];
		tag('action_begin', $ret);

		$this->action->setData($this->GET);
		$param = $this->param;
		switch(count($param)){
		case 0:
			$ret = $this->action->$mtd();
			break;
		case 1:
			$ret = $this->action->$mtd($param[0]);
			break;
		case 2:
			$ret = $this->action->$mtd($param[0], $param[1]);
			break;
		case 3:
			$ret = $this->action->$mtd($param[0], $param[1], $param[2]);
			break;
		case 4:
			$ret = $this->action->$mtd($param[0], $param[1], $param[2], $param[3]);
			break;
		case 5:
			$ret = $this->action->$mtd($param[0], $param[1], $param[2], $param[3], $param[4]);
			break;
		default:
			$ret = call_user_func_array([$this->action, $mtd], $param);
			break;
		}

		tag('action_end', $ret);
	}

	public function &getMeta(){
		return $this->meta;
	}

	/**
	 * 解析URL中PATHINFO部分
	 *
	 * @param $path_info
	 *
	 * @return array
	 */
	public function parse_path($path_info){
		$part = pathinfo($path_info);
		if(isset($part['extension'])){
			$this->extension_name = strtolower($part['extension']);
		}
		$path = str_replace('.' . $this->extension_name, '', $path_info);

		$path_info = trim($path, '/ '.URL_PATHINFO_DEPR);

		$array = $path_info? explode(URL_PATHINFO_DEPR, $path_info) : [];

		$act = array_shift($array);
		if($act){
			$this->action_name = ucfirst(strtolower($act));
		} else{
			$this->action_name    = DEFAULT_ACTION;
			$this->default_action = true;
		}

		$name = $this->action_name . 'Action';
		$meta = $this->meta = classmeta_read($name);

		$mtd = array_shift($array);
		if($mtd){
			$this->method_name = $mtd;
		} else{
			$this->method_name = $meta['default_method'];
			if(!is_string($this->method_name) || !trim($this->method_name)){
				Think::fail_error(ERR_NALLOW_MISS_PATH);
			}
			$this->default_method = true;
		}

		if(isset($meta['method'][$this->method_name])){
			$ref = $meta['method'][$this->method_name];
			if($ref['all_param'] && (count($array) < $ref['must_param'] || count($array) > $ref['all_param'])){
				return '参数数量应该在' . $ref['must_param'] . '~' . $ref['all_param'] . '之间';
			}
			$lst = $this->meta['method'][$this->method_name]['param_list'];
			foreach($array as $k => $v){
				if($v != 'null'){
					continue;
				}
				$array[$k] = $this->meta['method'][$this->method_name]['param'][$lst[$k]]['default_value'];
			}
		} elseif(!isset($meta['method']['__call'])){
			return LANG_MODULE_NOT_EXIST . ' : ' . $name . '::' . $this->method_name . '()';
		} else{
			foreach($array as $k => $v){
				if($v == 'null'){
					$array[$k] = null;
				}
			}
		}

		$this->param = $array;
		if($this->extension_name == 'form'){
			$this->callback = [&$this, 'return_json'];
		} else{
			$this->callback = [&$this, 'return_' . $this->extension_name];
		}
		if(!is_callable($this->callback)){
			return '无法处理的返回类型：' . $this->extension_name;
		}

		return 0;
	}

	/**
	 * 覆盖GET 变量
	 *
	 * @param array $new_get
	 *
	 * @return void
	 */
	public function setData(array &$new_get){
		$this->GET = & $new_get;
	}

	/**
	 * 分拣显示操作
	 * @param $templateFile
	 * @param $vars
	 *
	 * @return mixed
	 */
	public function display($templateFile, $vars){
		$callback = $this->callback;
		return $callback($templateFile, extension_to_mime($this->extension_name), $vars);
	}

	/**
	 * 显示普通html页面
	 */
	protected function return_html($templateFile, $contentType, $vars){
		$view = ThinkInstance::View();
		$view->assign($vars);
		$view->display($templateFile, $contentType);

		if(SHOW_TRACE){
			SPT(false);
		}
		return null;
	}

	/**
	 * 返回json数据
	 */
	protected function return_json($t, $c, $vars){
		Think::clear_ob();
		header('Content-Type: ' . $c . '; charset=utf-8');
		if(SHOW_TRACE){
			$vars['_PAGE_TRACE_'] = grab_page_trace();
		}
		$ret = json_encode($vars);

		if(!$ret){
			Think::fail_error(ERR_JSON_SERIALIZE, json_last_message());
		} else{
			echo $ret;
		}
	}

	/**
	 * 通过jsonp返回数据
	 */
	protected function return_jsonp($t, $c, $vars){
		Think::clear_ob();
		header('Content-Type: ' . $c . '; charset=utf-8');
		$handler = isset($_GET[VAR_JSONP_HANDLER])? $_GET[VAR_JSONP_HANDLER] : DEFAULT_JSONP_HANDLER;
		if(SHOW_TRACE){
			$vars['_PAGE_TRACE_'] = grab_page_trace();
		}

		if(!$vars){
			Think::fail_error(ERR_JSON_SERIALIZE);
		} else{
			echo $handler . '(' . json_encode($vars) . ');';
		}
	}

	/**
	 * 显示php序列化
	 */
	protected function return_php($t, $c, $vars){
		Think::clear_ob();
		header('Content-Type: ' . $c . '; charset=utf-8');
		if(SHOW_TRACE){
			$vars['_PAGE_TRACE_'] = grab_page_trace();
		}
		echo serialize($vars);
	}

	/**
	 * 二进制php序列化
	 */
	protected function return_bphp($t, $c, $vars){
		Think::clear_ob();
		header('Content-Type: ' . $c . '; charset=utf-8');
		if(SHOW_TRACE){
			$vars['_PAGE_TRACE_'] = grab_page_trace();
		}
		echo igbinary_serialize($vars);
	}
}
