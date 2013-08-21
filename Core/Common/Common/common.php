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
 * Think 基础函数库
 * @category   Think
 * @package    Common
 * @author     liu21st <liu21st@gmail.com>
 */

/**
 * 获取模版文件 格式 项目://分组@主题/模块/操作
 * @param string $name  模版资源地址
 * @param string $layer 视图层（目录）名称
 *
 * @return string
 */
function T($template = '', $layer = ''){
	if(is_file($template)){
		return $template;
	}
	// 解析模版资源地址
	if(false === strpos($template, '://')){
		$template = APP_NAME . '://' . str_replace(':', '/', $template);
	}
	$info  = parse_url($template);
	$file  = $info['host'] . (isset($info['path'])? $info['path'] : '');
	$group = isset($info['user'])? $info['user'] . '/' : (defined('GROUP_NAME')? GROUP_NAME . '/' : '');
	$app   = $info['scheme'];
	$layer = $layer? $layer : DEFAULT_V_LAYER;

	// 获取当前主题的模版路径
	if(($list = EXTEND_GROUP_LIST) && isset($list[$app])){ // 扩展分组
		$baseUrl = $list[$app] . '/' . $group . $layer . '/';
	} elseif(1 == APP_GROUP_MODE){ // 独立分组模式
		$baseUrl = dirname(BASE_LIB_PATH) . '/' . $group . $layer . '/';
	} else{
		$baseUrl = TMPL_PATH . $group;
	}
	$depr = TMPL_FILE_DEPR;
	// 分析模板文件规则
	if('' == $file){
		// 如果模板文件名为空 按照默认规则定位
		$file = MODULE_NAME . $depr . ACTION_NAME;
	} elseif(false === strpos($file, $depr)){
		$file = MODULE_NAME . $depr . $file;
	}

	return $baseUrl . $file . TMPL_TEMPLATE_SUFFIX;
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 *
 * @param string $name    变量的名称 支持指定类型
 * @param mixed  $default 不存在的时候默认值
 * @param mixed  $filter  参数过滤方法
 *
 * @return mixed
 */
function I($name, $default = '', $filter = null){
	if(strpos($name, '.')){ // 指定参数来源
		list($method, $name) = explode('.', $name, 2);
	} else{ // 默认为自动判断
		$method = 'param';
	}
	switch(strtolower($method)){
	case 'get'     :
		$input =& $_GET;
		break;
	case 'post'    :
		$input =& $_POST;
		break;
	case 'put'     :
		parse_str(file_get_contents('php://input'), $input);
		break;
	case 'param'   :
		switch($_SERVER['REQUEST_METHOD']){
		case 'POST':
			$input = $_POST;
			break;
		case 'PUT':
			parse_str(file_get_contents('php://input'), $input);
			break;
		default:
			$input = $_GET;
		}
		if(VAR_URL_PARAMS && isset($_GET[VAR_URL_PARAMS])){
			$input = array_merge($input, $_GET[VAR_URL_PARAMS]);
		}
		break;
	case 'request' :
		$input =& $_REQUEST;
		break;
	case 'session' :
		$input =& $_SESSION;
		break;
	case 'cookie'  :
		$input =& $_COOKIE;
		break;
	case 'server'  :
		$input =& $_SERVER;
		break;
	case 'globals' :
		$input =& $GLOBALS;
		break;
	default:
		return null;
	}
	// 全局过滤
	// array_walk_recursive($input,'filter_exp');
	if(VAR_FILTERS){
		$_filters = explode(',', VAR_FILTERS);
		foreach($_filters as $_filter){
			// 全局参数过滤
			array_walk_recursive($input, $_filter);
		}
	}
	if(empty($name)){ // 获取全部变量
		$data    = $input;
		$filters = isset($filter)? $filter : DEFAULT_FILTER;
		if($filters){
			$filters = explode(',', $filters);
			foreach($filters as $filter){
				$data = array_map($filter, $data); // 参数过滤
			}
		}
	} elseif(isset($input[$name])){ // 取值操作
		$data    = $input[$name];
		$filters = isset($filter)? $filter : DEFAULT_FILTER;
		if($filters){
			$filters = explode(',', $filters);
			foreach($filters as $filter){
				if(function_exists($filter)){
					$data = is_array($data)? array_map($filter, $data) : $filter($data); // 参数过滤
				} else{
					$data = filter_var($data, is_int($filter)? $filter : filter_id($filter));
					if(false === $data){
						return isset($default)? $default : null;
					}
				}
			}
		}
	} else{ // 变量默认值
		$data = isset($default)? $default : null;
	}

	return $data;
}

/**
 * 批量导入文件 成功则返回
 * @param array   $array  文件数组
 * @param boolean $return 加载成功后是否返回
 *
 * @return boolean
 */
function require_array($array, $return = false){
	foreach($array as $file){
		if(require_once ($file) && $return){
			return true;
		}
	}
	if($return){
		return false;
	}
}

/**
 * 按顺序尝试导入文件
 * 第一个成功导入的文件返回
 *
 * @param array $array 文件列表
 * @param       string [out] $hit 被引入的文件
 *
 * @return mixed
 */
function require_one(array $array, &$hit = null){
	foreach($array as $file){
		if(is_file($file)){
			$hit = $file;

			return require_once $file;
		}
	}
	Think::halt('require_one: 找不到任何一个文件：' . implode_l("\n", $array));
	exit();
}

/**
 * 批量导入文件
 * 导入全部文件，不返回
 */
function require_all(array $array){
	foreach($array as $file){
		if(is_file($file)){
			require_once $file;
		}
	}
}

/**
 * 基于命名空间方式导入函数库
 * load('@.Util.Array')
 *
 * @param string $name    函数库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext     导入的文件扩展名
 *
 * @return void
 */
function load($name, $baseUrl = '', $ext = '.php'){
	$name = str_replace(array('.', '#'), array('/', '.'), $name);
	if(empty($baseUrl)){
		if(0 === strpos($name, '@/')){
			//加载当前项目函数库
			$baseUrl = COMMON_PATH;
			$name    = substr($name, 2);
		} else{
			//加载ThinkPHP 系统函数库
			$baseUrl = EXTEND_PATH . 'Function/';
		}
	}
	if(substr($baseUrl, -1) != '/'){
		$baseUrl .= '/';
	}
	require_cache($baseUrl . $name . $ext);
}

/**
 * 远程调用模块的操作方法 URL 参数格式 [项目://][分组/]模块/操作
 *
 * @param string       $url   调用地址
 * @param string|array $vars  调用参数 支持字符串和数组
 * @param string       $layer 要调用的控制层名称
 *
 * @return mixed
 */
function R($url, $vars = array(), $layer = ''){
	$info   = pathinfo($url);
	$action = $info['basename'];
	$module = $info['dirname'];
	$class  = A($module, $layer);
	if($class){
		if(is_string($vars)){
			parse_str($vars, $vars);
		}

		return call_user_func_array(array(&$class, $action . ACTION_SUFFIX), $vars);
	} else{
		return false;
	}
}

/**
 * 动态添加行为扩展到某个标签
 * @param string $tag      标签名称
 * @param string $behavior 行为名称
 * @param string $path     行为路径
 *
 * @return void
 */
function add_tag_behavior($tag, $behavior, $path = ''){
	$array = C('tags.' . $tag);
	if(!$array){
		$array = array();
	}
	if($path){
		$array[$behavior] = $path;
	} else{
		$array[] = $behavior;
	}
	C('tags.' . $tag, $array);
}

// 根据数组生成常量定义
function array_define($array, $check = true){
	$content = "\n";
	foreach($array as $key => $val){
		$key = strtoupper($key);
		if($check){
			$content .= 'defined(\'' . $key . '\') or ';
		}
		if(is_int($val) || is_float($val)){
			$content .= "define('" . $key . "'," . $val . ');';
		} elseif(is_bool($val)){
			$val = ($val)? 'true' : 'false';
			$content .= "define('" . $key . "'," . $val . ');';
		} elseif(is_string($val)){
			$content .= "define('" . $key . "','" . addslashes($val) . "');";
		} else{
			trigger_error("Can not define constant `{$key}` of type `" . gettype($val) . "`.", E_USER_WARNING);
		}
		$content .= "\n";
	}

	return $content;
}

