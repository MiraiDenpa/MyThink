<?php
echo_line('URL二级域名路由定义。');
// 用GLOBAL是因为可以调用其他脚本处理，虽然暂时没有
if(is_file(BASE_CONF_PATH . '/urlmap.php')){
	echo_line('载入全局定义。');
	require(BASE_CONF_PATH . '/urlmap.php');
}
if(is_file(BASE_CONF_PATH . APP_STATUS . '/urlmap.php')){
	echo_line('载入状态定义。');
	require(BASE_CONF_PATH . APP_STATUS . '/urlmap.php');
}
foreach($GLOBALS['URL_MAP'] as $app => $url){
	echo_line("\t$app \t -> ".(is_array($url)?'[multi-value]':$url));
}
if(!isset($GLOBALS['URL_MAP'][APP_NAME])){
	echo_line('$GLOBALS[URL_MAP][' . APP_NAME . '] -- 定义有误(BASE_CONF_PATH/urlmap.php)');
	die();
}
hidef_save('urlmap', $GLOBALS['URL_MAP'], true);

if(!defined('COOKIE_DOMAIN')){
	define('COOKIE_DOMAIN', '.' . $GLOBALS['URL_MAP'][APP_NAME]);
}
echo_line("Cookie Domain: " . COOKIE_DOMAIN);

echo_line('');
