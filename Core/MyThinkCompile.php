<?php
header('Content-Type:text/html; charset=utf-8');
umask(0);

global $br;
if(PHP_SAPI == 'cli'){
	$br = "\n";
	if(0 !== posix_getuid()){
		$cmd = 'sudo php ';
		foreach($argv as $arg){
			$cmd .= ' ' . escapeshellarg($arg);
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

require THINK_PATH.'Compile/compile_core_files.php';
require THINK_PATH.'Compile/merge_config.php';
require THINK_PATH.'Compile/hidef_save_constant.php';

/* 定义所有常量 */
require THINK_PATH.'Compile/gen_define_all.php';

/* 合并整个函数库 */
echo_line('合并函数库');
$list  = array_merge(glob(THINK_PATH . 'Common/*/*.php'), // 内置函数
					 glob(BASE_LIB_PATH . 'Common/*.php'), // 用户全局函数
					 glob(APP_PATH . 'Common/*.php'), // 用户定义函数
					 glob(EXTEND_PATH . 'Function/*.php')); // Extend里定义的函数
$flist = [];
foreach($list as $file){
	$flist[] = $file;
	if(APP_DEBUG){
		echo_line("\t - $file");
	}
}
if(CORE_DEBUG){
	echo_line(' -- CORE_DEBUG -- 调试模式，用include方式引入文件。');
	$funcLib = "foreach(" . var_export($flist, true) . " as \$file){\n\trequire_once \$file;\n}\n";
	file_put_contents(RUNTIME_PATH . 'functions.php', '<?php ' . $funcLib);
} else{
	$funcLib = compile_core_files($flist);
	file_put_contents(RUNTIME_PATH . 'functions.php', '<?php ' . $funcLib);
}
require RUNTIME_PATH . 'functions.php';
echo_line('');

/* 配置项目 */
require THINK_PATH.'Compile/gen_config.php';

if(!is_dir(LIB_PATH) || !is_dir(CONF_PATH)){
	echo_line('创建项目目录结构');
	// 创建项目目录结构
	require THINK_PATH.'Compile/build_app_dir.php';
}

echo_line('创建临时文件目录结构');
// 创建临时文件目录结构
require THINK_PATH.'Compile/build_runtime_dir.php';

/* 开始生成编译文件 */
require THINK_PATH.'Compile/gen_index.php';

/* 语言包 */
require THINK_PATH.'Compile/gen_language.php';

/* 域名、路由 定义 */
require THINK_PATH.'Compile/gen_urlmaps.php';

/* 标签回调代码 */
require THINK_PATH.'Compile/gen_error_code.php';

/* 错误码定义 */
require THINK_PATH.'Compile/gen_tags.php';

/* 静态文件生成 */
require THINK_PATH.'Compile/gen_compile_public_path.php';

echo_line("写入常量ini文件。");
$ini_path  = RUNTIME_PATH . APP_NAME;
$data_path = RUNTIME_PATH . 'hidef';
write_all_define_to_ini($ini_path);
echo_line("");

/* TODO 处理路由 */
$restart = !in_array('-n', $argv);
if(PHP_SAPI == 'cli'){
	$debug = require(THINK_PATH . 'Compile/gen_service_config.php');

	if($restart){
		$debug = require(THINK_PATH . 'Compile/gen_test_service.php');
	}
}

echo_line(" --- 编译结束 --- ");
