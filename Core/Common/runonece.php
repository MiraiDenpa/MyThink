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
 * ThinkPHP 运行时文件 编译后不再加载
 * @category   Think
 * @package    Common
 * @author     liu21st <liu21st@gmail.com>
 */

// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);

// 检查缓存目录(Runtime) 如果不存在则自动创建
function check_runtime(){
	if(!is_dir(RUNTIME_PATH)){
		mkdir(RUNTIME_PATH);
	} elseif(!is_writeable(RUNTIME_PATH)){
		header('Content-Type:text/html; charset=utf-8');
		exit('目录 [ ' . RUNTIME_PATH . ' ] 不可写！');
	}
	mkdir(CACHE_PATH); // 模板缓存目录
	if(!is_dir(LOG_PATH)){
		mkdir(LOG_PATH);
	} // 日志目录
	if(!is_dir(TEMP_PATH)){
		mkdir(TEMP_PATH);
	} // 数据缓存目录
	if(!is_dir(DATA_PATH)){
		mkdir(DATA_PATH);
	} // 数据文件目录
	return true;
}

// 检查项目目录结构 如果不存在则自动创建
if(!is_dir(CACHE_PATH)){
	// 检查缓存目录
	check_runtime();
} elseif(APP_DEBUG){
	// 调试模式切换删除编译缓存
	if(is_file(RUNTIME_FILE)){
		unlink(RUNTIME_FILE);
	}
}

// 记录加载文件时间
G('loadTime');
// 执行入口
Think::Start();
