<?php

/**
 * 404处理
 * 调试模式会抛异常
 * 部署模式下面传入url参数可以指定跳转页面，否则发送404信息
 *
 * @param string $msg 提示信息
 * @param string $url 跳转URL地址
 *
 * @return void
 */
function _404($msg = '', $url = ''){
	http_response_code(404);
	// <DEBUG>
	Think::halt($msg);
	// </DEBUG>
	if($msg && LOG_EXCEPTION_RECORD){
		Log::write($msg);
	}
	if(empty($url) && URL_404_REDIRECT){
		$url = URL_404_REDIRECT;
	}
	if($url){
		redirect($url);
	} else{
		exit;
	}
}
