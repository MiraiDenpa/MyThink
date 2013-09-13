<?php

function classmeta_read($id){
	$file = CACHE_PATH . 'meta/class.' . $id . '.' . APP_NAME . '.php';
	if(is_file($file)){
		$ret = require $file;
		if($ret){
			return $ret;
		}
	}
	$ret = ReflectionArray::parseClass($id, true);

	// <DEBUG> 
	$debug_checker = "if( filemtime(__FILE__)<filemtime('{$ret['file']}') ){
		trace(__FILE__,'缓存过时','DEBUG');
		return false;
	}";
	trace(xdebug_filepath_anchor($file, 0, $file), '更新类型meta缓存', 'CACHE');
	// </DEBUG>

	file_put_contents($file, "<?php
// <DEBUG>
$debug_checker 
// </DEBUG>
return (\$_SERVER['REQUEST_TIME']>" . (time() + DATA_CACHE_TIME) . ")?'':" . var_export($ret, true) . ';');
	return $ret;
}
