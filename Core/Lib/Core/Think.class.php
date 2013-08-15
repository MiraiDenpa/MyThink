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
 * ThinkPHP Portal类
 *
 * 介于PHP和用户代码之间的层
 * 处理所有错误、写日志等
 *
 * @category    Think
 * @package     Think
 * @subpackage  Core
 * @author      liu21st <liu21st@gmail.com>
 */
class Think{

	/**
	 * 应用程序初始化
	 * @access public
	 * @return void
	 */
	static public function start(){
		// 设定错误和异常处理
		register_shutdown_function(array('Think', 'checkFatalError'));
		set_error_handler(array('Think', 'appError'));
		set_exception_handler(array('Think', 'appException'));
		spl_autoload_register(array('Think', 'autoload'));
	}

	/**
	 * 自动加载
	 *
	 * @param $class
	 *
	 * @return bool
	 */
	public static function autoload($class){
		// 检查是否存在别名定义
		if($ret = alias_import($class)){
			return $ret;
		}
		$file = $class . '.class.php';
		if(substr($class, -8) == 'Behavior'){ // 加载行为
			if(require_one([
							 CORE_PATH . 'Behavior/' . $file,
							 EXTEND_PATH . 'Behavior/' . $file,
							 BASE_LIB_PATH . 'Behavior/' . $file,
							 LIB_PATH . 'Behavior/' . $file
							 ])
			){
				return true;
			}
		} elseif(substr($class, -5) == 'Model'){ // 加载模型
			if(require_array(array(
								  BASE_LIB_PATH . 'Model/' . $file,
								  EXTEND_PATH . 'Model/' . $file
							 ), true)
			){
				return true;
			}
		} elseif(substr($class, -6) == 'Action'){ // 加载控制器
			if(require_array(array(
								  LIB_PATH . 'Action/' . $file,
								  BASE_LIB_PATH . 'Model/' . $file,
								  EXTEND_PATH . 'Action/' . $file
							 ), true)
			){
				return true;
			}
		} elseif(substr($class, 0, 5) == 'Cache'){ // 加载缓存驱动
			if(require_array(array(
								  EXTEND_PATH . 'Driver/Cache/' . $file,
								  CORE_PATH . 'Driver/Cache/' . $file
							 ), true)
			){
				return true;
			}
		} elseif(substr($class, 0, 2) == 'Db'){ // 加载数据库驱动
			if(require_array(array(
								  EXTEND_PATH . 'Driver/Db/' . $file,
								  CORE_PATH . 'Driver/Db/' . $file
							 ), true)
			){
				return true;
			}
		} elseif(substr($class, 0, 8) == 'Template'){ // 加载模板引擎驱动
			if(require_array(array(
								  EXTEND_PATH . 'Driver/Template/' . $file,
								  CORE_PATH . 'Driver/Template/' . $file
							 ), true)
			){
				return true;
			}
		} elseif(substr($class, 0, 6) == 'TagLib'){ // 加载标签库驱动
			if(require_array(array(
								  BASE_LIB_PATH . 'TagLib/' . $file,
								  EXTEND_PATH . 'Driver/TagLib/' . $file,
								  CORE_PATH . 'Driver/TagLib/' . $file
							 ), true)
			){
				return true;
			}
		}
		return false;
	}

	/**
	 * 自定义异常处理
	 * @access public
	 *
	 * @param mixed $e 异常对象
	 */
	static public function appException($e){
		$error            = array();
		$error['message'] = $e->getMessage();
		$trace            = $e->getTrace();
		if('throw_exception' == $trace[0]['function']){
			$error['file'] = $trace[0]['file'];
			$error['line'] = $trace[0]['line'];
		} else{
			$error['file'] = $e->getFile();
			$error['line'] = $e->getLine();
		}
		Log::record($error['message'], Log::ERR);
		Think::halt($error);
	}

	/**
	 * 自定义错误处理
	 * @access public
	 *
	 * @param int    $errno   错误类型
	 * @param string $errstr  错误信息
	 * @param string $errfile 错误文件
	 * @param int    $errline 错误行数
	 *
	 * @return void
	 */
	static public function appError($errno, $errstr, $errfile, $errline){
		switch($errno){
		case E_ERROR:
		case E_PARSE:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			ob_end_clean();
			$errorStr = $errstr . ' ' . $errfile . ' 第 ' . $errline . ' 行.';
			if(LOG_RECORD){
				Log::write("[$errno] " . $errorStr, Log::ERR);
			}
			Think::halt($errorStr);
			break;
		case E_STRICT:
		case E_USER_WARNING:
		case E_USER_NOTICE:
		default:
			$errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
			trace($errorStr, '', 'NOTIC');
			break;
		}
	}

	/**
	 * 程序退出，检查是否有致命错误
	 *
	 * @return void
	 * @static
	 */
	static public function checkFatalError(){
		// 保存日志记录
		if(LOG_RECORD){
			Log::save();
		}
		if($e = error_get_last()){
			switch($e['type']){
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				ob_end_clean();
				Think::halt($e);
				break;
			}
		}
	}

	/**
	 * 错误输出
	 * @param mixed $error 错误
	 *
	 * @return void
	 */
	public static function halt($error){
		ob_get_clean();
		if(!defined('APP_DEBUG')){
			header('Content-Type:text/html; charset=utf-8');
			echo "致命错误：{$error}<br/><br/>Trace:<pre>";
			debug_print_backtrace();
			echo "</pre>";
			die;
		}
		$e = array();
		if(APP_DEBUG){
			//调试模式下输出错误信息
			if(!is_array($error)){
				$trace        = debug_backtrace();
				$e['message'] = $error;
				$e['file']    = $trace[0]['file'];
				$e['line']    = $trace[0]['line'];
				ob_start();
				debug_print_backtrace();
				$e['trace'] = ob_get_clean();
			} else{
				$e = $error;
			}
		} else{
			//否则定向到错误页面
			$error_page = ERROR_PAGE;
			if(!empty($error_page)){
				redirect($error_page);
			} else{
				if(SHOW_ERROR_MSG){
					$e['message'] = is_array($error)? $error['message'] : $error;
				} else{
					$e['message'] = ERROR_MESSAGE;
				}
			}
		}
		// 包含异常页面模板
		require TMPL_EXCEPTION_FILE;
		exit;
	}
}
