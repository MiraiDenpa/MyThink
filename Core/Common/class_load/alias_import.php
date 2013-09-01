<?php

/**
 * 快速定义和导入别名
 *
 * @param string $alias     类库别名
 *
 * @return boolean
 */
function alias_import($alias){
	global $_think_import_alias;
	if(isset($_think_import_alias[$alias])){
		return require_once($_think_import_alias[$alias]);
	}else{
		return false;
	}
}
