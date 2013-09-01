<?php

/**
 * URL组装
 *
 * @param        $action
 * @param        $model
 * @param array  $vars   传入的参数
 * @param bool   $merge  如果是true，则把$_GET放进参数里
 *
 * @return string
 */
function U($action = ACTION_NAME, $model = METHOD_NAME, $vars = [], $merge = false){
	global $helper;
	if(!$helper){
		$helper = ThinkInstance::UrlHelper();
	}
	if($merge){
		$vars = array_merge($_GET, $vars);
	}

	$helper->reset();
	$helper->setAction($action);
	$helper->setMethod($model);
	$helper->setParamAll($vars);
	
	return $helper->getUrl();
}
