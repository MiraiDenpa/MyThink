<?php

/**
 * 快速定义和导入别名 支持批量定义
 *
 * alias_import(a) -> 导入别名文件
 * alias_import(a,a.php) -> 添加别名
 * alias_import([a=>a.php,b=>b/php]) -> 批量添加别名
 *
 * @param string|array $alias 类库别名
 * @param string $classfile 对应类库文件名
 * @return boolean
 */
function alias_import($alias, $classfile='') {
	static $_alias = array();
	if (is_string($alias)) {
		if(isset($_alias[$alias])) {
			return require_once($_alias[$alias]);
		}elseif ('' !== $classfile) {
			// 定义别名导入
			$_alias[$alias] = $classfile;
			return true;
		}
	}elseif (is_array($alias)) {
		$_alias   =  array_merge($_alias,$alias);
		return true;
	}
	return false;
}
