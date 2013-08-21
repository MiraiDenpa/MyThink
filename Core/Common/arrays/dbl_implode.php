<?php
/**
 * 
 *
 * @param $glueOutter 连接两个元素的分隔符
 * @param $glueInner 连接键值的分隔符
 * @param $arr 被连接的数组
 *
 * @return string
 */
function dbl_implode($glueOutter,$glueInner,$arr){
	$ret = '';
	foreach($arr as $k=>$v){
		$ret .= $glueOutter.$k.$glueInner.$v;
	}
	return substr($ret, strlen($glueOutter));
}
