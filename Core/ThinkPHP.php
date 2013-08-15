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

// ThinkPHP 入口文件
ob_start();
// 记录开始运行时间
$GLOBALS['_beginTime'] = microtime(true);
// 记录内存初始使用
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON){
	$GLOBALS['_startUseMems'] = memory_get_usage();
}
// 系统目录定义
define('APP_PATH', './' . APP_NAME . '/');
defined('ROOT_PATH')    or define('ROOT_PATH', realpath($_SERVER['DOCUMENT_ROOT']) . '/');

/*
	// 加载系统基础函数库
	require THINK_PATH . 'Common/common.php';
	require THINK_PATH . 'Common/functions.php';
*/

define('RUNTIME_FILE', RUNTIME_PATH . APP_NAME . '.php');

if(!APP_DEBUG && is_file(RUNTIME_FILE)){
	// 部署模式直接载入运行缓存
	require RUNTIME_FILE;
} else{
	foreach(['APP_STATUS', 'APP_DEBUG', 'RUNTIME_PATH'] as $const){
		if(!defined($const)){
			if(!is_file(ROOT_PATH . 'app_status.php')){
				copy(THINK_PATH . 'Tpl/app_status.tpl', ROOT_PATH . 'app_status.php');
			}
			Think::halt('需要有常量定义：' . $const);
		}
	}

	// 加载程序状态
	require ROOT_PATH . 'app_status.php';

	// 加载运行时文件
	require THINK_PATH . 'Common/runonece.php';
}

ob_end_flush();

/** xxx */

// 加载模式系统行为定义
C('extends', include THINK_PATH . 'Conf/tags.php');
// 默认加载项目配置目录的tags文件定义
C('tags', include CONF_PATH . 'tags.php');
