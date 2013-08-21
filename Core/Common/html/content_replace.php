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
	/*if(!defined('ACTION_NAME')){
		return;
	}*/
	if(defined('IS_AJAX') && IS_AJAX){
		return;
	}
	// 系统默认的特殊变量替换
	$replace = array(
		'__ACTION__' => ACTION_NAME, // 当前操作地址
		'__METHOD__'   => METHOD_NAME, // 当前页面地址
		'PUBLIC_URL' => PUBLIC_URL, // 站点公共目录
		'STATIC_URL' => STATIC_URL, // 站点公共目录
		'APP_NAME' =>APP_NAME, // 站点公共目录
	);
	$content = str_replace(array_keys($replace), array_values($replace), $content);

	return $content;
}
