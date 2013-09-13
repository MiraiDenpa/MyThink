<?php


/**
 * Cookie 设置、获取、删除
 *
 * @param string $name    名称
 * @param mixed  $value   值
 * @param int    $timeout 过期超时
 * @param string $path
 * @param string $domain
 *
 * @return mixed
 */
function cookie($name, $value = '', $timeout = COOKIE_EXPIRE, $path = COOKIE_PATH, $domain = COOKIE_DOMAIN){
	if(!$value && func_num_args() == 1){
		return $_COOKIE[$name];
	} elseif(!$value){
		return setcookie($name, null, $_SERVER['REQUEST_TIME'] - 1800, $path, $domain, false, false);
	} else{
		return setcookie($name, $value, $_SERVER['REQUEST_TIME'] + $timeout, $path, $domain, false, false);
	}
}
