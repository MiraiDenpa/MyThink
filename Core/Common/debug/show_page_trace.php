<?php
/**
 * 显示调试信息
 * @param bool $exit 是否退出
 */
function SPT($exit = true){
	$end = false;
	if(!defined('FORCE_NOT_AJAX')){
		define('FORCE_NOT_AJAX', true);
	}

	// 系统默认显示信息
	$trace = array(); // 显示运行时间
	G('beginTime', $GLOBALS['_beginTime']);
	G('viewEndTime');
	// 显示详细运行时间
	$base = array(
		[
			HTML::label('请求原文'),
			REQUEST_METHOD . ' ' . $_SERVER['PATH_INFO'] . ' ' . $_SERVER['SERVER_PROTOCOL'] . "\n执行 —> " .
			ACTION_NAME . ' :: ' . METHOD_NAME . "\n返回 —> " . EXTENSION_NAME
		],
		[
			HTML::label('运行时间'),
			'载入时间: ' . G('beginTime', 'viewEndTime') . 's ( 读取:' . G('beginTime', 'loadTime') . 's 初始化:' .
			G('loadTime', 'initTime') . 's 执行程序:' . G('initTime', 'viewStartTime') . 's 解析模板:' .
			G('viewStartTime', 'viewEndTime') . 's )<br/>平均每秒请求: ' .
			number_format(1/G('beginTime', 'viewEndTime'), 2) . 'req/s'
		],
		[HTML::label('查询信息'), N('db_query') . ' 读取 ' . N('db_write') . ' 写入 '],
		[HTML::label('文件加载'), count(get_included_files()) . '个'],
		[
			HTML::label('缓存'),
			'内存: ' . N('cache_read_mem') . ' 读取 ' . N('cache_write_mem') . ' 写入<br/>文件: ' . N('cache_read_file') .
			' 读取 ' . N('cache_write_file') . ' 写入 '
		],
		[HTML::label('会话'), 'SESSION_ID=' . session_id()],
		[HTML::label('模板'), '显示: '.N('template_show').', 解析数量: '.N('template_parse')],
	);
	if(MEMORY_DEBUG){
		$base[] = [
			HTML::label('内存使用'),
			'总计内存: ' . number_format(memory_get_usage()/1024, 2) . 'KB<br/>初始内存: ' .
			number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024, 2) . 'KB<br/>内核载入内存: ' .
			number_format(($GLOBALS['_initUseMems'] - $GLOBALS['_startUseMems'])/1024, 2) . 'KB<br/>用户内存开销: ' .
			number_format((memory_get_usage() - $GLOBALS['_initUseMems'])/1024, 2) . 'KB'
		];
	}

	$debug = trace();

	$debug           = array_merge(['BASE' => $base], $debug);
	$debug['INFO'][] = ['<span class="badge">=w=</span>','* 显示日志，后方信息无法获得 *'];

	// 调用Trace页面模板
	ob_start(null, 0);
	include TMPL_TRACE_FILE;
	$content = ob_get_clean();
	$content = html_whitespace($content, true);
	echo "\n\n<!-- ThinkPageTrace -->\n";
	echo $content;
	
	if(ob_get_level()) ob_flush();
	if($exit){
		echo "<div class=\"container\">
		<div class=\"well\" style=\"text-align:center;\">调用了 `ShowPageTrace();` 程序中止 || 调用堆栈：</div>";
		xdebug_print_function_stack();
		echo '</div>';
		if($end){
			echo '</body>';
		}
		if(ob_get_level()) ob_flush();
		exit();
	}
}
