<?php


/**
 * 添加和获取页面Trace记录
 *
 * @param string  $value  变量
 * @param string  $label  标签
 * @param string  $level  日志级别
 * @param boolean $record 是否记录日志
 *
 * @return array|null
 */
function trace($value = null, $label = null, $level = null, $record = false){
	static $_trace = array();
	
	
	if(null === $value){ // 获取trace信息
		return $_trace;
	} else{
		switch($level){
		case '':
			$lv = 'info';
			break;
		default:
			$lv = 'default';
		}
		if($label){
			if(TRACE_DEBUG){
				$safetrace = xdebug_get_function_stack();
				$trace = str_replace("\n", '', $trace);
				$label = HTML::label($label, $lv, [
												  'title'   => $trace,
												  'onclick' => '$.dialog.alert($(this).attr(\'title\').replace(/\n/g, \'<br/>\'))'
												  ]);
			} else{
				$label = HTML::label($label, $lv);
			}
		} else{
			$label = trim($label);
		}
		$info  = dump_some($value);
		$level = strtoupper($level);
		if(!isset($_trace[$level])){
			$_trace[$level] = array();
		}
		$_trace[$level][] = [$label, $info];
		if((defined('IS_AJAX') && IS_AJAX) || PAGE_TRACE_SAVE || $record){
			Log::record($info, $level, $record);
		}

		return null;
	}
}
