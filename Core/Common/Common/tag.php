<?php


/**
 * 处理标签扩展
 * @param string $tag 标签名称
 * @param mixed $params 传入参数
 * @return mixed
 */
function tag($tag, &$params=NULL) {
	$tags       = hidef_fetch('ThinkTags');
	
	if(empty($tags[$tag])){
		return;
	}
	if(APP_DEBUG) {
		G($tag.'Start');
		trace('[ '.$tag.' ] --START--','','INFO');
	}
	// 执行扩展
	foreach ($tags[$tag] as $callback) {
		if(is_string($callback)){
			$callback($params);
		}else{
			$obj = new $callback[0];
			$cb = $callback[1];
			$obj->$cb($params);
		}
	}
	if(APP_DEBUG) { // 记录行为的执行日志
		trace('[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]','','INFO');
	}
}
