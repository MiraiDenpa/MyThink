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
	public $action_name = 'Index';
	public $method_name = 'index';
	public $extension_name = 'html';
	public $extra_path = [];

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
		$action = ThinkInstance::A($this->action_name);
		if(!$action){
			// 是否定义Empty模块
			$action = ThinkInstance::A($this->action_name = 'Empty');
			if(!$action){
				_404(LANG_ACTION_NOT_EXIST . ':' . $this->action_name);
			}
		}

		$mtd = $this->method_name;
		if(is_callable([$action, $mtd])){
			$ret = [&$action, &$mtd];
			tag('action_begin', $ret);
			$ret = $action->$mtd();
			tag('action_end', $ret);

			return $ret;
		}
		_404(LANG_MODULE_NOT_EXIST . ':' . $this->action_name);

		return false;
	}

	/**
	 * 解析URL，设置属性，返回新GET（但$_GET全局变量不变
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
		$path = $part['dirname'] . '/' . $part['filename'];

		$path_info = trim($path, '/ ');
		if(empty($path_info)){
			return array();
		}

		$ret   = $_GET;
		$array = explode(URL_PATHINFO_DEPR, $path_info);
		if(!empty($array)){
			$this->action_name = strtolower(array_shift($array));
		}
		if(!empty($array)){
			$this->method_name = strtolower(array_shift($array));
		}

		$param_map = hidef_load('actmap');

		if(isset($param_map[$this->action_name][$this->method_name])){
			$param_map = $param_map[$this->action_name][$this->method_name];
			for($loc = 0, $count = count($param_map); $loc < $count; $loc++){
				if(!isset($array[$loc])){
					return false;
				}
				$ret[$param_map[$loc][0]] = $array[$loc];
				unset($array[$loc]);
			}
		}
		if(!empty($array)){
			$this->extra_path = $array;
		}

		return $ret;
	}
}
