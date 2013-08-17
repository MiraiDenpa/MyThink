<?php
/**
 * 显示调试信息
 * @param bool $exit 是否退出
 */
function SPT($exit = true){
	$end = false;
	if(!headers_sent()){
		$end = true;
		echo StandardHeader('调试页面');
	}
	if(!defined('FORCE_NOT_AJAX')){
		define('FORCE_NOT_AJAX', true);
	}

	// 系统默认显示信息
	$trace = array(); // 显示运行时间
	G('beginTime', $GLOBALS['_beginTime']);
	G('viewEndTime');
	// 显示详细运行时间
	$show_time = G('beginTime', 'viewEndTime') . 's ( Load:' . G('beginTime', 'loadTime') . 's Init:' .
				 G('loadTime', 'initTime') . 's Exec:' . G('initTime', 'viewStartTime') . 's Template:' .
				 G('viewStartTime', 'viewEndTime') . 's )';
	$base      = array(
		'请求信息' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $_SERVER['SERVER_PROTOCOL'] . ' ' .
				  $_SERVER['REQUEST_METHOD'],
		'运行时间' => $show_time,
		'吞吐率'  => number_format(1/G('beginTime', 'viewEndTime'), 2) . 'req/s',
		'内存开销' => MEMORY_LIMIT_ON?
				number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024, 2) . ' kb' : '不支持',
		'查询信息' => N('db_query') . ' queries ' . N('db_write') . ' writes ',
		'文件加载' => count(get_included_files()),
		'缓存信息' => N('cache_read') . ' gets ' . N('cache_write') . ' writes ',
		'会话信息' => 'SESSION_ID=' . session_id(),
	);
	// 读取项目定义的Trace文件
	$traceFile = CONF_PATH . 'trace.php';
	if(is_file($traceFile)){
		$base = array_merge($base, (array)include($traceFile));
	}
	$debug = trace();

	$debug = array_merge(['BASE'=>$base], $debug);
	$debug['INFO'][] = '* 日志被打印，后方信息无法显示 *';
	
	// 调用Trace页面模板
	include TMPL_TRACE_FILE;

	if($exit){
		echo "<pre class=\"alert alert-info\">\n调用了 `ShowPageTrace();` 程序中止 || 调用堆栈：\n";
		xdebug_filepath_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		echo '</pre>';
		if($end){
			echo '</body>';
		}
		exit();
	}
}
