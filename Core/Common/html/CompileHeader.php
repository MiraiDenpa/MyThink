<?php
/**
 * 添加TagLibHead的头部
 */
function CompileHeader(){
	$taglib = ThinkInstance::TagLib('header');
	return $taglib->getHeader();
}
