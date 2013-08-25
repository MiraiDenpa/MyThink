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
 * ThinkPHP 应用程序类 执行应用过程管理
 * 可以在模式扩展中重新定义 但是必须具有Run方法接口
 * @category    Think
 * @package     Think
 * @subpackage  Core
 * @author      liu21st <liu21st@gmail.com>
 */
class App{
	/**
	 * 运行应用实例 入口文件使用的快捷方法
	 * @access public
	 * @return void
	 */
	static public function run(){
		$dispatcher = new Dispatcher();
		// 项目初始化标签
		tag('app_init', $dispatcher);
		// 定义当前请求的系统常量
		define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
		// URL调度
		$_GET = $dispatcher->parse_path($_SERVER['PATH_INFO']);
		if($_GET === false){ // 調度出錯
			_404('无法从请求的URL定位资源（缺少参数）。');
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
		return;
	}
}
