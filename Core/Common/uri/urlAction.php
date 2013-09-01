<?php

/**
 * URL组装，当前action内
 *
 * @param        $model
 * @param array  $vars   传入的参数
 * @param bool   $merge  如果是true，则把$_GET放进参数里
 *
 * @return string
 */
function UI($model = METHOD_NAME, $vars = [], $merge = true){
	global $helper;
	if(!$helper){
		$helper = ThinkInstance::UrlHelper();
	}
	if($merge){
		$vars = array_merge($_GET, $vars);
	}

	$helper->reset();
	$helper->setMethod($model);
	$helper->setParamAll($vars);

	return $helper->getUrl();
}
