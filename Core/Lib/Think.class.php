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
	 * @var OutputBuffer
	 */
	private static $main_out_buffer;

	/**
	 * 应用程序初始化
	 * @access public
	 * @return void
	 */
	static public function start(){
		trace('程序执行开始，注册处理程序。', '', 'INFO');
		// 设定错误和异常处理
		register_shutdown_function(array('Think', 'checkFatalError'));
		set_error_handler(array('Think', 'appError'));
		set_exception_handler(array('Think', 'appException'));
		//spl_autoload_register(array('Think', 'autoload'));

		header('Content-Type: text/html; charset=utf8');
		self::$main_out_buffer            = new OutputBuffer('ContentReplace');
		self::$main_out_buffer->end_flush = true;
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
		case E_RECOVERABLE_ERROR:
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$halt_string = '[' . error_code_to_type_str($errno) . '] ' . $errstr;
			if(LOG_RECORD){
				Log::write('[' . error_code_to_type_str($errno) . '] ' . $errstr . ' ON ' . $errfile . ':' .
						   $errline, Log::ERR);
			}
			Think::halt($halt_string, true, $errfile, $errline);
			break;
		case E_WARNING:
			if(strpos($errstr, 'Missing argument') === 0){
				$errstr = preg_replace_callback('#called in (.*?) on line (\d+)#', function ($mats){
					return xdebug_filepath_anchor($mats[1], $mats[2]);
				}, $errstr);
			}
			break;
		default:
			break;
		}
		$halt_string = $errstr . ' - ' . xdebug_filepath_anchor($errfile, $errline);
		trace($halt_string, error_code_to_type_str($errno), 'NOTIC');
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
				Think::halt($e);

				return;
			}
		}

		SPT(false);
		if(Think::$main_out_buffer){
			//var_dump(Think::$main_out_buffer);
			Think::$main_out_buffer->flush();
			Think::$main_out_buffer = null;
		}
	}

	/**
	 * 输出 ** 用户不正确的输入引起的 ** 错误
	 * @param int    $code
	 * @param string $extra_msg
	 *
	 * @return void
	 * @static
	 */
	public static function fail_error($code, $extra_msg = ''){
		global $dispatcher;
		$e                      = new Error($code);
		$data['message']  = $e->getMessage();
		$data['extra']  = $extra_msg;
		$data['redirect'] = $e->getUrl();
		$data['code']     = $e->getCode();
		$data['info']     = $e->getInfo();
		$data['name']     = $e->getName();
		$data['where']    = $e->getWhere();
		$dispatcher->display('!user_error', $data);
		exit;
	}

	/**
	 * 输出 ** 编程问题引起的 ** 错误
	 * @param string     $msg  错误
	 * @param bool       $html 输出的是html
	 *
	 * @param string     $file 显示错误发生于这个文件
	 * @param string|int $line
	 *
	 * @return void
	 * @exit
	 */
	public static function halt($msg, $html = false, $file = '', $line = ''){
		if(self::$main_out_buffer){
			self::$main_out_buffer->clean();
		}
		if(!defined('APP_DEBUG')){ // 
			header('Content-Type:text/html; charset=utf-8');
			if($html){
				echo "致命错误：{$msg}<br/><br/>Trace:<pre>";
			} else{
				echo "致命错误：" . nl2br(htmlspecialchars($msg)) . "<br/><br/>Trace:<pre>";
			}
			debug_print_backtrace();
			echo "</pre>";
			die;
		}
		$trace   = debug_backtrace();
		$content = ob_get_contents();

		if(!$file){
			$file = $trace[1]['file'];
		}
		if(!$line){
			$line = $trace[1]['line'];
		}

		require TMPL_EXCEPTION_FILE;
		if(self::$main_out_buffer){
			self::$main_out_buffer->flush();
		}

		if(SHOW_TRACE){
			define('FORCE_TRACE', true);
			SPT(false);
		}
		self::$main_out_buffer = null;
		while(ob_get_level()){
			ob_end_flush();
		}
		exit;
	}
}
