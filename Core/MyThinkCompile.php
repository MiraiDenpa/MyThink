<?php
header('Content-Type:text/html; charset=utf-8');
umask(0);

global $br;
if(PHP_SAPI == 'cli'){
	$br = "\n";
	if(0 !== posix_getuid()){
		$cmd = 'sudo php ';
		foreach($argv as $arg){
			$cmd .= escapeshellarg($arg);
		}
		echo_line("You must have root privilige...\n");
		passthru($cmd, $ret);
		exit($ret);
	}
} else{
	$br = '<br/>';
}
/**
 */
function echo_line($msg){
	global $br;
	echo $msg . $br;
}

$GLOBALS['COMPILE'] = true;
echo_line(" --- 开始编译 --- ");

require __DIR__ . '/Compile/compile_core_files.php';
require __DIR__ . '/Compile/merge_config.php';
require __DIR__ . '/Compile/hidef_save_constant.php';

/* 定义所有常量 */
require __DIR__ . '/Compile/gen_define_all.php';

if(!is_dir(LIB_PATH) || !is_dir(CONF_PATH)){
	echo_line('创建项目目录结构');
	// 创建项目目录结构
	require __DIR__ . '/Compile/build_app_dir.php';
}

echo_line('创建临时文件目录结构');
// 创建临时文件目录结构
require __DIR__ . '/Compile/build_runtime_dir.php';

/* 合并整个函数库 */
echo_line('合并函数库');
$list  = array_merge(glob(THINK_PATH . 'Common/*/*.php'), // 内置函数
					 glob(APP_PATH . 'Common/*.php'), // 用户定义函数
					 glob(EXTEND_PATH . 'Function/*.php')); // Extend里定义的函数
$flist = [];
foreach($list as $file){
	$flist[] = $file;
	echo_line("\t - $file");
}
if(APP_DEBUG){
	echo_line(' -- 调试模式，用include方式引入文件。');
	$funcLib = "foreach(" . var_export($flist, true) . " as \$file){\n\trequire_once \$file;\n}\n";
	file_put_contents(RUNTIME_PATH . 'functions.php', '<?php ' . $funcLib);
} else{
	$funcLib = compile_core_files($flist);
	file_put_contents(RUNTIME_PATH . 'functions.php', '<?php ' . $funcLib);
}
require RUNTIME_PATH . 'functions.php';
echo_line('');

/* 开始生成编译文件 */
require __DIR__ . '/Compile/gen_index.php';

/* 语言包 */
require __DIR__ . '/Compile/gen_language.php';

/* 配置项目 */
require __DIR__ . '/Compile/gen_config.php';

/* 域名、路由 定义 */
require __DIR__ . '/Compile/gen_urlmaps.php';

/* 标签回调代码 */
require __DIR__ . '/Compile/gen_error_code.php';

/* 错误码定义 */
require __DIR__ . '/Compile/gen_tags.php';

/* TODO 额外定义 */

/* 静态文件生成 */
require __DIR__.'/Compile/gen_compile_public_path.php';

/* 调试 */
$debug = require(THINK_PATH . 'Conf/default_debug.php');
hidef_save('ThinkDebug', $debug);


echo_line("写入常量ini文件。");
$ini_path  = RUNTIME_PATH . APP_NAME;
$data_path = RUNTIME_PATH . 'hidef';
write_all_define_to_ini($ini_path);
echo_line("");


/* TODO 处理路由 */

if(PHP_SAPI == 'cli'){
	// 写入nginx配置文件
	echo_line("写入nginx配置文件");
	ob_start();
	require THINK_PATH . 'Tpl/nginx.tpl';
	$cnt = ob_get_clean();
	file_put_contents('/etc/nginx/vhost.d/' . APP_NAME . '-ngx.conf', $cnt);

	// 写入phpfpm配置文件
	echo_line("写入phpfpm配置文件");
	ob_start();
	require THINK_PATH . 'Tpl/php-fpm.tpl';
	$cnt = ob_get_clean();
	file_put_contents('/etc/php-fpm.d/' . APP_NAME . '-fpm.conf', $cnt);

	// 检查配置文件
	echo_line("测试fpm配置文件");
	exec('php-fpm -t 2>&1', $print, $ret);
	if($ret){
		echo_line(" *** php-fpm 配置文件生成失败 ***");
		echo_line(implode("\n", $print));
		exit;
	}
	echo_line("测试nginx配置文件");
	exec('nginx -t 2>&1', $print, $ret);
	if($ret){
		echo_line(" *** nginx 配置文件生成失败 ***");
		echo_line(implode("\n", $print));
		exit;
	}

	// 重启服务
	echo_line("重启服务");
	if(is_file('/usr/bin/systemctl')){
		system('systemctl restart php-fpm nginx');
		passthru('systemctl status php-fpm nginx | grep "Active:"');
	} else{
		passthru('service php-fpm restart');
		passthru('service nginx restart');
	}
}

echo_line(" --- 编译结束 --- ");
