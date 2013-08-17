<?php

/**
 * 自定义异常处理
 * @param string  $msg  异常消息
 * @param string  $type 异常类型 默认为ThinkException
 * @param integer $code 异常代码 默认为0
 *
 * @return void
 */
function throw_exception($msg, $type = 'ThinkException', $code = 0){
	if(class_exists($type, false)){
		throw new $type($msg, $code);
	} else{
		Think::halt($msg);
	} // 异常类型不存在则输出错误信息字串
}
