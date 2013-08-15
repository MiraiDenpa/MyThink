<?php
/*
define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);
define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false);
*/
/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
	if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
		return true;
	}elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
		return true;
	}
	return false;
}

/**
 * @return bool
 */
function isAjax(){
	return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[VAR_AJAX_SUBMIT]) || !empty($_GET[VAR_AJAX_SUBMIT])) ? true : false;
}
