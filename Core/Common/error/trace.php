<?php


/**
 * 添加和获取页面Trace记录
 * @param string $value 变量
 * @param string $label 标签
 * @param string $level 日志级别
 * @param boolean $record 是否记录日志
 * @return void
 */
function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
	$info   =   ($label?$label.':':'').print_r($value,true);
	$level  =   strtoupper($level);
	if(!isset($_trace[$level])) {
		$_trace[$level] =   array();
	}
	$_trace[$level][]   = $info;
	if((defined('IS_AJAX') && IS_AJAX) || !SHOW_TRACE  || $record) {
		Log::record($info,$level,$record);
	}
}
