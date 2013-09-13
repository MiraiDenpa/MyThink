<?php
/**
 * 按顺序尝试导入文件
 * 第一个成功导入的文件返回
 *
 * @param array $array 文件列表
 * @param       string [out] $hit 被引入的文件
 *
 * @return mixed
 */
function require_one(array $array, &$hit = null){
	foreach($array as $file){
		if(is_file($file)){
			$hit = $file;
			return require_once $file;
		}
	}
	Think::fail_error(ERR_NF_FILE, 'require_one: 找不到任何一个文件：' . implode_l("\n", $array));
}

/**
 * 批量导入文件
 * 导入全部文件，不返回
 */
function require_all(array $array){
	foreach($array as $file){
		if(is_file($file)){
			require_once $file;
		}
	}
}
