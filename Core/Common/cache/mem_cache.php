<?php

/**
 * 缓存管理
 * @param mixed $name    缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value   缓存值
 * @param mixed $options 缓存参数
 *
 * @return mixed
 */
function S($name, $value = '', $expire = DATA_CACHE_TIME){
	global $_g_cache;
	if(is_null($_g_cache)){
		$_g_cache = new ThinkMemcached();
	}

	return SAS($_g_cache->id, $name, $value, $expire);
}

/**
 *
 *
 * @param        $name
 * @param string $value
 * @param        $expire
 *
 * @return bool|mixed|string
 */
function SAS($cas, $name, $value = '', $expire = DATA_CACHE_TIME){
	global $_g_cache;
	if(is_null($_g_cache)){
		$_g_cache = new ThinkMemcached();
	}
	if($value === ''){
		N('cache_read_mem', 1);
		$ret = $_g_cache->getByKey($cas, $name);
		//<DEBUG>
		trace($name . '  -  ' . $_g_cache->getResultMessage() . dump_some($ret), '内存读取(' . $cas . ')', 'CACHE');
		//</DEBUG>
	} elseif($value === null){
		N('cache_write_mem', 1);
		$ret = $_g_cache->deleteByKey($cas, $name);
		//<DEBUG>
		trace($name . '  -  ' . $_g_cache->getResultMessage(), '内存删除(' . $cas . ')', 'CACHE');
		//</DEBUG>
	} else{
		N('cache_write_mem', 1);
		$_g_cache->setByKey($cas, $name, $value, $expire);
		//<DEBUG>
		trace($name . '  -  ' . $_g_cache->getResultMessage() . dump_some($value), '内存写入(' . $cas . ')', 'CACHE');
		//</DEBUG>
		$ret = $value;
	}

	return $ret;
}

class ThinkMemcached extends Memcached{
	public $id = '';

	function __construct($id = 'cache'){
		$this->id = $id;
		parent::__construct('memcachedb' . $id);

		if(!count($this->getServerList())){
			$config = hidef_load('ThinkDb' . $id);
			$this->setOptions((array)$config['params']);
			/*<DEBUG>*/
			$ret = /*</DEBUG>*/
					$this->addServers((array)$config['hosts']);
			//<DEBUG>
			trace(dump_some($ret) . $this->getResultMessage(), '连接服务器', 'CACHE');
			//</DEBUG>
		}
	}
}
