<?php
/**
 * 快速获取URL
 * @param $key
 * @return mixed
 */
function map_url($key){
	static $urlm;
	if(!$urlm){
		$urlm = hidef_load('urlmap');
	}
	// <DEBUG>
	if(!isset($urlm[$key])){
		Think::halt('URL_MAP[' . $key . '] -- 定义有误(BASE_CONF_PATH/urlmap.php)');
	}
	// </DEBUG>
	return $urlm[$key];
}
