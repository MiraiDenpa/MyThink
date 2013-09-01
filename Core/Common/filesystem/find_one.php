<?php
/**
 * 按顺序尝试查找文件
 * 
 * 如果都没有找到，产生一个严重错误
 *
 * @param array $list 文件列表
 *
 * @return string 第一个找到的文件名
 */
function find_one(array $list){
	foreach($list as $file){
		if(is_file($file)){
			return $file;
		}
	}
	Think::halt('find_one: 找不到任何一个文件：' . implode_l("\n", $list));
	exit();
}
