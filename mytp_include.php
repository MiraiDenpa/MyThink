<?php

define('MTP_PATH', __DIR__ . '/');
define('THINK_PATH', MTP_PATH . 'Core/');

// APP_PATH: 存放程序文件的地方
defined('APP_PATH')    or define('APP_PATH', './' . APP_NAME . '/');
// ROOT_PATH: 对应URL和路径、应该跟DOC_ROOT一样
defined('ROOT_PATH')    or define('ROOT_PATH', realpath($_SERVER['DOCUMENT_ROOT']) . '/');
defined('ROOT_URL')    or define('ROOT_URL', '/'); // 

defined('RUNTIME_PATH')    or define('RUNTIME_PATH', sys_get_temp_dir().'/'.APP_NAME.'/');

include THINK_PATH . 'MyThinkCompile.php';
