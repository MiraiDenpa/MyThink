<?php
/**
 * 添加TagLibHead的头部
 */
function TaglibReplaceScript(){
	$taglib = ThinkInstance::TagLib('header');
	$ret    = trim($taglib->getHeader('script'));
	if($ret){
		$ret .= "\n{__SCRIPT__}";
	}
	return $ret;
}

function TaglibReplaceStyle(){
	$taglib = ThinkInstance::TagLib('header');
	$ret    = trim($taglib->getHeader('style'));
	if($ret){
		$ret .= "\n{__STYLE__}";
	}
	return $ret;
}

