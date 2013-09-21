<?php

function _UC($app, $action, $method, $lpath = '', $params = [], $suffix = ''){
	if(!isset($GLOBALS['URL_MAP'][$app])){
		Think::halt('URL_MAP[' . $app . '] -- 定义有误(BASE_CONF_PATH/urlmap.php)');
	}
	$domain = $GLOBALS['URL_MAP'][$app];
	$perfix = 'http://' . $domain;

	$path = $action . URL_PATHINFO_DEPR . $method;
	if($lpath){
		$path .= URL_PATHINFO_DEPR . $lpath;
	}

	$url = $perfix . URL_PATHINFO_DEPR . $path;

	if($suffix){
		$url .= '.' . $suffix;
	}

	if(!empty($params)){
		if(is_string($params)){
			$url .= '?' . $params;
		} else{
			$url .= '?' . http_build_query($params);
		}
	}

	return $url;
}
