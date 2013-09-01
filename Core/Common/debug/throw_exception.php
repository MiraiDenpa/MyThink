<?php

/**
 * 自定义异常处理
 * @param integer $code 异常代码
 *
 * @return void
 */
function throw_exception($code){
	if(!is_int($code)){
		Think::halt('Error类调用错误，需要整数参数(实际收到'.dump_some($code,0).')。', true);
	}
	$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
	throw new Error($code, $debug);
}
