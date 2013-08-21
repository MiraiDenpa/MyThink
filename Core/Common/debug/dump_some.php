<?php
/**
 *
 * 可以在ob回调函数中使用
 *
 * @param mixed $val
 * @param int   $length 正数结果结果
 *
 * @return string
 */
function dump_some($val, $length = 160){
	static $level = 0;
	$level++;
	if($length && $level > 3){
		return '#[LOOP]#';
	}
	switch(gettype($val)){
	case 'boolean':
		$ret = 'boolean(' . ($val? 'true' : 'false') . ')';
		break;
	case 'string':
		if($length){
			$ret = '"' . mb_strcut($val, 0, $length, 'UTF-8') . '..."';
		} else{
			$ret = '"' . $val . '"';
		}
		break;
	case 'array':
		$new_arr = [];
		foreach($val as $k => $v){
			$new_arr[$k] = dump_some($v, $length);
		}
		$ret = '['.dbl_implode(',',':',$new_arr);
		if($length){
			$ret = mb_strcut($ret, 0, $length, 'UTF-8') . '...('.count($val).')';
		}
		$ret .= ']';
		break;
	case 'object':
		$ret = 'object(#' . get_class($val) . ')';
		break;
	case 'NULL':
		$ret = 'NULL';
		break;
	default:
		$ret = gettype($val) . '(...)';
		break;
	}

	$level--;
	return $ret;
}
