<?php
/** @var Dispatcher $dispatcher */
global $dispatcher;
$dispatcher = new Dispatcher();
// 项目初始化标签
tag('app_init', $dispatcher);
// 定义当前请求的系统常量
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
//define('NOW',$_SERVER['REQUEST_TIME']);
// URL调度
$error = $dispatcher->parse_path($_SERVER['PATH_INFO']);
$dispatcher->setData($_GET);
if($error){ // 調度出錯
	Think::fail_error(ERR_NF_ACTION, $error);
}
define('ACTION_NAME', $dispatcher->action_name);
define('METHOD_NAME', $dispatcher->method_name);
define('EXTENSION_NAME', $dispatcher->extension_name);
// 项目开始标签
tag('app_begin');
// 记录应用初始化时间
G('initTime');
$ret = $dispatcher->run();
// 项目结束标签
tag('app_end');
