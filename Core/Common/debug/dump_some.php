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
	$type = gettype($val);
	$count = null;
	switch($type){
	case 'boolean':
		$val = $val? 'true' : 'false';
		break;
	case 'string':
		$count = strlen($val);
		$val = strip_tags($val);
		break;
	case 'array':
		$count = count($val);
		$val = json_encode($val, JSON_FORCE_OBJECT);
		break;
	case 'object':
		/** @var  Closure  $val */
		$type = get_class($val);
		if($type == 'Closure'){
			$val = '{...}';
		}else{
			$val = (string)$val;
		}
		$type = 'object::' . $type;
		break;
	case 'NULL':
		$val = 'NULL';
		break;
	default:
		if(!is_numeric($val)){
			$val = 'Unknown';
		}
		break;
	}
	if($length){
		$val = mb_strcut($val, 0, $length, 'UTF-8') . '...';
	}
	$ret = ' <b>#<font color="green">' . $type .'</font>'. (is_int($count)? '[<font color="red">' . $count . '</font>]' : '') . '</b> <em>&lt;' . $val . '&gt;</em>';

	$level--;
	if(!$level){
		$ret = addcslashes($ret, "\n\t\r");
	}

	return $ret;
}
