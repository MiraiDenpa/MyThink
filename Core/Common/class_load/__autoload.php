<?php


/**
 * 自动加载
 *
 * @param $class
 *
 * @return bool
 */
function __autoload($class){
	// 检查是否存在别名定义
	if($ret = alias_import($class)){
		return $ret;
	}
	$file = $class . '.php';
	if(substr($class, -8) == 'Behavior'){ // 加载行为
		require_one([
					CORE_PATH . 'Behavior/' . $file,
					EXTEND_PATH . 'Behavior/' . $file,
					BASE_LIB_PATH . 'Behavior/' . $file,
					LIB_PATH . 'Behavior/' . $file
					]);
	} elseif(substr($class, -5) == 'Model'){ // 加载模型
		require_one(array(
						 LIB_PATH . 'Model/' . $file,
						 BASE_LIB_PATH . 'Model/' . $file,
						 EXTEND_PATH . 'Model/' . $file
					));
	} elseif(substr($class, -6) == 'Action'){ // 加载控制器
		require_one(array(
						 LIB_PATH . 'Action/' . $file,
						 BASE_LIB_PATH . 'Action/' . $file,
						 EXTEND_PATH . 'Action/' . $file
					));
	} elseif(substr($class, 0, 5) == 'Cache'){ // 加载缓存驱动
		require_one(array(
						 EXTEND_PATH . 'Driver/Cache/' . $file,
						 CORE_PATH . 'Driver/Cache/' . $file
					));
	} elseif(substr($class, 0, 2) == 'Db'){ // 加载数据库驱动
		require_one(array(
						 EXTEND_PATH . 'Driver/Db/' . $file,
						 CORE_PATH . 'Driver/Db/' . $file
					));
	} elseif(substr($class, 0, 8) == 'Template'){ // 加载模板引擎驱动
		require_one(array(
						 EXTEND_PATH . 'Driver/Template/' . $file,
						 CORE_PATH . 'Driver/Template/' . $file
					));
	} elseif(substr($class, -6) == 'Entity'){ // 加载模板引擎驱动
		require_one(array(
						 BASE_LIB_PATH . 'Entity/' . $file,
						 LIB_PATH . 'Entity/' . $file,
					));
	} elseif(substr($class, 0, 6) == 'TagLib'){ // 加载标签库驱动
		require_one(array(
						 BASE_LIB_PATH . 'TagLib/' . $file,
						 EXTEND_PATH . 'Driver/TagLib/' . $file,
					));
	} elseif(substr($class, -6) == 'Stream'){ // 加载标签库驱动
		require_one(array(
						 EXTEND_PATH . 'Driver/Stream/' . $file,
					));
	} elseif(strpos($class, '\\') !== false){
		require_once BASE_LIB_PATH . 'Lib/' . str_replace('\\', '/', $file);
	} else{
		return false;
	}
	return true;
}
