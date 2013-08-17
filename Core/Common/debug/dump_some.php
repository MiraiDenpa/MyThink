<?php
/**
 * 
 *
 * @param     $val
 * @param int $length
 *
 * @return string
 */
function dump_some($val, $length = 180){
	switch(gettype($val)){
	case 'boolean':
		return 'boolean('.($val?'true':'false').')';
	case 'string':
		return '"'.mb_strcut($val, 0, $length, 'UTF-8').'..."';
	case 'array':
		$e = @var_export($val,true);
		$e = str_replace("\n", '', $e);
		return '['.count($val).'#'.mb_strcut($e, 0, $length, 'UTF-8').'...]';
	case 'object':
		return 'object(#'.get_class($val).')';
	default:
		return gettype($val).'(...)';
	}
}
