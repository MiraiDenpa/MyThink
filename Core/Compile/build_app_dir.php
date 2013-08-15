<?php
// 没有创建项目目录的话自动创建
if(!is_dir(APP_PATH)){
	mkdir(APP_PATH, 0755, true);
}
if(is_writeable(APP_PATH)){
	$dirs = array(
		LIB_PATH,
		CONF_PATH,
		APP_PATH . '/Common',
		LANG_PATH,
		TMPL_PATH,
		TMPL_PATH . '/',
		LIB_PATH . 'Model/',
		LIB_PATH . 'Action/',
		LIB_PATH . 'Behavior/',
		LIB_PATH . 'Widget/',
		BASE_LIB_PATH,
		BASE_LIB_PATH . 'Model/',
		BASE_LIB_PATH . 'Action/',
		BASE_LIB_PATH . 'Behavior/',
		BASE_LIB_PATH . 'Widget/',
		BASE_LIB_PATH . 'Conf/',
	);
	foreach($dirs as $dir){
		if(!is_dir($dir)){
			echo_line("\t create - $dir");
			mkdir($dir, 0755, true);
		}
	}
	// 写入测试Action
	if(!is_file(LIB_PATH . 'Action/IndexAction.class.php')){
		echo_line("\t copy - ".LIB_PATH . 'Action/IndexAction.class.php');
		copy(THINK_PATH . 'Tpl/default_index.tpl', LIB_PATH . 'Action/IndexAction.class.php');
	}
} else{
	echo_line('项目目录不可写，目录无法自动生成！');
	exit;
}
