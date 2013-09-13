<?php
echo_line("配置项目：");
$config = merge_config(array(
							'默认配置'                           => THINK_PATH . 'Conf/convention.php',
							'默认调试配置'                         => APP_DEBUG?
									THINK_PATH . 'Conf/default_debug.php' : '/not/exist',
							'全局配置'                           => BASE_CONF_PATH . 'config.php',
							'全局状态配置（' . APP_STATUS . '.php）' => BASE_CONF_PATH . APP_STATUS . '.php',
							'项目配置'                           => CONF_PATH . 'config.php',
							'项目状态配置（' . APP_STATUS . '.php）' => CONF_PATH . APP_STATUS . '.php',
					   ));
foreach($config as $n => $v){
	if(is_array($v)){
		echo_line("\t ** " . $n . ' - 配置项是数组！');
		hidef_save($n, $v);
	} else{
		define(trim($n, '_'), (string)$v);
	}
}
echo_line('');

echo_line("数据库定义：");
foreach(array_merge(glob(BASE_CONF_PATH . 'db/*.php'), glob(CONF_PATH . 'db/*.php')) as $file){
	$define = require $file;
	hidef_save('ThinkDb' . pathinfo($file, PATHINFO_FILENAME), $define, true);
	echo_line("\t - " . basename($file));
}

echo_line("数据库调试定义：");
foreach(array_merge(glob(BASE_CONF_PATH . APP_STATUS . '/db/*.php'), glob(CONF_PATH . APP_STATUS .
																		  '/db/*.php')) as $file){
	$define = require $file;
	hidef_save('ThinkDb' . pathinfo($file, PATHINFO_FILENAME), $define, true);
	echo_line("\t - " . basename($file));
}
echo_line('');
