<?php
echo_line('标签回调定义。');
$tags = include THINK_PATH . 'Conf/tags.php';
if(is_file(BASE_CONF_PATH . 'tags.php')){
	$tag = include BASE_CONF_PATH . 'tags.php';
	foreach($tag as $type => $arr){
		$tags[$type] = array_merge((array)$type, $arr);
	}
}
if(is_file(CONF_PATH . 'tags.php')){
	$tag = include CONF_PATH . 'tags.php';
	foreach($tag as $type => $arr){
		$tags[$type] = array_merge((array)$type, $arr);
	}
}
hidef_save('ThinkTags', $tags);
echo_line('');
