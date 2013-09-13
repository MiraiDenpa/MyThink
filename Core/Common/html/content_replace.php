<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 *
 *
 * @param $content
 *
 * @return mixed
 */
function ContentReplace($content){
	if(!defined('RUNTIME_PATH')){
		return false;
	}
	/*if(defined('IS_AJAX') && IS_AJAX){
		return false;
	}*/
	// 系统默认的特殊变量替换
	$replace = array(
		'PUBLIC_URL' => PUBLIC_URL, // 站点公共目录
		'STATIC_URL' => STATIC_URL, // 站点公共目录
		'APP_NAME'   => APP_NAME, // 站点公共目录
		'JS_DEBUG'   => JS_DEBUG, // 站点公共目录
	);

	if(!SHOW_TRACE){
		$replace2 = array(
			ROOT_PATH     => '/', // 站点公共目录
			MTP_PATH      => '/Think/', // 站点公共目录
			RUNTIME_PATH  => '/Runtime/', // 站点公共目录
			BASE_LIB_PATH => '/Base/', // 站点公共目录
			LIB_PATH      => '/Lib/', // 站点公共目录
		);
		$replace  = array_merge($replace, $replace2);
	}

	$content = str_replace(array_keys($replace), array_values($replace), $content);

	return $content;
}
