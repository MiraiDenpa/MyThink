<?php


/**
 * 添加和获取页面Trace记录
 * @param string $value 变量
 * @param string $label 标签
 * @param string $level 日志级别
 * @param boolean $record 是否记录日志
 * @return array|null
 */
function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
	static $_trace =  array();
	static $tab = '';
	static $tabc = 0;
	if('[think]' === $value){ // 获取trace信息
		return $_trace;
	}elseif('[think_set]' === $value){
		return $_trace = array_merge($_trace,$label);
	}elseif('[think_sub]' === $value){
		$tabc=$label*5;
		if($tabc<0) return 0;
		return $tab = str_repeat('&nbsp;',$tabc);
	}else{
		if(!$label && $level == 'ERR') $label = $level;
		if($label){
			if( !($lv = hidef_fetch('DEBUG_TAB_TYPE.'.$level)) ){
				$lv = 'label-info';
			}
			if(TRACE_LOG){
				ob_start();
				debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				$trace = ob_get_clean();
				$trace = addslashes(htmlentities(nl2br($trace)));
				$label = '<span class="label '.$lv.'" title="'.$trace.'" onclick="$.dialog.alert($(this).attr(\'title\'))">'.$label.'</span>&nbsp;&nbsp;';
			}else{
				$label = '<span class="label '.$lv.'">'.$label.'</span>&nbsp;&nbsp;';
			}
		}else{
			$label = '';
		}
		if($level == 'INFO'){
			if($tabc<0) return null;
			$label = $tab.$label;
		}

		$info   =   $label.print_r($value,true);
		$level  =   strtoupper($level);
		if(!isset($_trace[$level])) {
			$_trace[$level] =   array();
		}
		$_trace[$level][]   = $info;
		if((defined('IS_AJAX') && IS_AJAX) || !SHOW_PAGE_TRACE  || $record) {
			Log::record($info,$level,$record);
		}
		return null;
	}
}
