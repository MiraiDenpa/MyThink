<?php
/**
 * 合并数组，然后在前后再添加一个分隔符
 *
 * @param       $glue
 * @param array $pieces
 *
 * @return string
 */
function implode_lr($glue, array $pieces){
	$str = implode($glue, $pieces);
	if(strlen($str)){
		$str = $glue . $str;
	}

	return $str . $glue;
}

/**
 * 合并数组，然后在前面再添加一个分隔符
 *
 * @param       $glue
 * @param array $pieces
 *
 * @return string
 */
function implode_l($glue, array $pieces){
	$str = implode($glue, $pieces);
	if(strlen($str)){
		return $glue . $str;
	} else{
		return $str;
	}
}

/**
 * 合并数组，然后在后面再添加一个分隔符
 *
 * @param       $glue
 * @param array $pieces
 *
 * @return string
 */
function implode_r($glue, array $pieces){
	$str = implode($glue, $pieces);
	if(strlen($str)){
		return $str . $glue;
	} else{
		return $str;
	}
}
