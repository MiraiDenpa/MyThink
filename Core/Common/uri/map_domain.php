<?php
function map_domain($app){
	static $urlm;
	if(!$urlm){
		$urlm = hidef_load('urlmap');
	}
	// <DEBUG>
	if(!isset($urlm[$app])){
		Think::halt('URL_MAP[' . $this->app . '] -- 定义有误(BASE_CONF_PATH/urlmap.php)');
	}
	// </DEBUG>
	return $urlm[$app];
}
