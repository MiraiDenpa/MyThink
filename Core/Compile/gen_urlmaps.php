<?php
if(is_file(BASE_CONF_PATH . '/urlmap.php')){
	echo_line('载入url定义。');
	require(BASE_CONF_PATH . '/urlmap.php');
}
if(!isset($GLOBALS['URL_MAP'][APP_NAME])){
	echo_line('$GLOBALS[URL_MAP][' . APP_NAME . '] -- 定义有误(BASE_CONF_PATH/urlmap.php)');
	die();
}

if(!defined('COOKIE_DOMAIN')){
	define('COOKIE_DOMAIN', '.' . $GLOBALS['URL_MAP'][APP_NAME]);
}
echo_line("Cookie Domain: " . COOKIE_DOMAIN);

echo_line('');

if(is_file(CONF_PATH . '/urlmap.php')){
	echo_line('载入本地url定义。');
	$array = require(CONF_PATH . '/urlmap.php');
	foreach($array as $n => $v){
		define(strtoupper(trim($n, '_')), (string)$v);
	}
}
