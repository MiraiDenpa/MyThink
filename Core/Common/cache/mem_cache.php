<?php

/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name,$value='',$options=null) {
	static $cache   =   '';
	if(is_array($options) && empty($cache)){
		// 缓存操作的同时初始化
		$type       =   isset($options['type'])?$options['type']:'';
		$cache      =   Cache::getInstance($type,$options);
	}elseif(is_array($name)) { // 缓存初始化
		$type       =   isset($name['type'])?$name['type']:'';
		$cache      =   Cache::getInstance($type,$name);
		return $cache;
	}elseif(empty($cache)) { // 自动初始化
		$cache      =   Cache::getInstance();
	}
	if(''=== $value){ // 获取缓存
		return $cache->get($name);
	}elseif(is_null($value)) { // 删除缓存
		return $cache->rm($name);
	}else { // 缓存数据
		if(is_array($options)) {
			$expire     =   isset($options['expire'])?$options['expire']:NULL;
		}else{
			$expire     =   is_numeric($options)?$options:NULL;
		}
		return $cache->set($name, $value, $expire);
	}
}
