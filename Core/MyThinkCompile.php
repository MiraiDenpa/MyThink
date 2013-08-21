<?php
header('Content-Type:text/html; charset=utf-8');
umask(0);

global $br;
$br = PHP_SAPI == 'cli'? "\n" : '<br/>';
function echo_line($msg){
	global $br;
	echo $msg . $br;
}

echo_line(" --- 开始编译 --- ");

require __DIR__ . '/Compile/compile_core_files.php';
require __DIR__ . '/Compile/merge_config.php';
require __DIR__ . '/Compile/hidef.php';

//  版本信息
define('THINK_VERSION', '3.1.3g');

// 遗留问题
define('TMPL_L_DELIM', '{');
define('TMPL_R_DELIM', '}');

// 待移动定义
define('STR_TRIM_BOTH', 3);
define('STR_TRIM_LEFT', 1);
define('STR_TRIM_RIGHT', 2);

// 路径设置 可在编译入口文件中重新定义 所有路径常量都必须以/ 结尾
defined('CORE_PATH')    or define('CORE_PATH', THINK_PATH . 'Lib/'); // 系统核心类库目录
defined('EXTEND_PATH')  or define('EXTEND_PATH', MTP_PATH . 'Extend/'); // 系统扩展目录
defined('VENDOR_PATH')  or define('VENDOR_PATH', MTP_PATH . 'Vendor/'); // 第三方类库目录
defined('LIBRARY_PATH') or define('LIBRARY_PATH', EXTEND_PATH . 'Library/'); // 扩展类库目录
defined('LOG_PATH')     or define('LOG_PATH', RUNTIME_PATH . 'Logs/'); // 项目日志目录
defined('TEMP_PATH')    or define('TEMP_PATH', RUNTIME_PATH . 'Temp/'); // 项目缓存目录
defined('DATA_PATH')    or define('DATA_PATH', RUNTIME_PATH . 'Data/'); // 项目数据目录
defined('CACHE_PATH')   or define('CACHE_PATH', RUNTIME_PATH . 'Cache/'); // 项目模板缓存目录

defined('LIB_PATH')     or define('LIB_PATH', APP_PATH . 'Lib/'); // 项目类库目录
defined('CONF_PATH')    or define('CONF_PATH', APP_PATH . 'Conf/'); // 项目配置目录
defined('LANG_PATH')    or define('LANG_PATH', APP_PATH . 'Lang/'); // 项目语言包目录
defined('TMPL_PATH')    or define('TMPL_PATH', APP_PATH . 'Tpl/'); // 项目模板目录

defined('BASE_LIB_PATH')     or define('BASE_LIB_PATH', LIB_PATH); // 项目共用文件目录
defined('BASE_CONF_PATH')    or define('BASE_CONF_PATH', BASE_LIB_PATH . 'Conf/'); // 项目配置目录
defined('BASE_LANG_PATH')    or define('BASE_LANG_PATH', BASE_LIB_PATH . 'Lang/'); // 项目语言包目录
defined('BASE_TMPL_PATH')    or define('BASE_TMPL_PATH', BASE_LIB_PATH . 'Tpl/'); // 项目模板目录

echo_line("程序目录（APP_PATH） = " . APP_PATH);
echo_line("库目录（BASE_LIB_PATH） = " . BASE_LIB_PATH);

defined('STATIC_PATH')  or define('STATIC_PATH', APP_PATH . 'Static/'); // 
defined('PUBLIC_PATH')  or define('PUBLIC_PATH', APP_PATH . 'Public/'); // 

defined('STATIC_URL')   or define('STATIC_URL', '/Static'); // 
defined('PUBLIC_URL')   or define('PUBLIC_URL', '/Public'); // 

defined('PHP_SELF')     or define('PHP_SELF', 'index.php');
defined('ENTRY_FILE')   or define('ENTRY_FILE', ROOT_PATH . PHP_SELF);

if(!is_dir(LIB_PATH) || !is_dir(CONF_PATH)){
	echo_line('创建项目目录结构');
	// 创建项目目录结构
	require __DIR__ . '/Compile/build_app_dir.php';
}
if(!is_dir(RUNTIME_PATH) || !is_dir(CACHE_PATH)){
	echo_line('创建临时文件目录结构');
	// 创建临时文件目录结构
	require __DIR__ . '/Compile/build_runtime_dir.php';
}

/* 开始生成编译文件 */
$compile = "<?php /* [SIG_GENERATE] */\n";
$compile .= "\$GLOBALS['_beginTime'] = microtime(TRUE);\n";
if(MEMORY_DEBUG) $compile .= "\$GLOBALS['_startUseMems'] = memory_get_usage();\n";
$compile .= "require(RUNTIME_PATH.'functions.php');\n";
$compile .= "set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);\n";

/* alias “查表导入” 的文件定义。 */
echo_line('ALIAS: ');
$alias = merge_config(array(
						   '核心导入定义' => THINK_PATH . 'Conf/alias.php',
						   '第三方类定义' => __DIR__ . '/Compile/library_map.php',
						   '用户定义'   => CONF_PATH . 'alias.php',
					  ));
foreach($alias as $k => $v){
	echo_line("\t - $k \t-> $v");
}
$compile .= 'alias_import(' . var_export($alias, true) . ');';
echo_line('');

/* 语言包 */
$language = 'zh-cn';
echo_line("语言文件： {$language}");
$lang = merge_config(array(
						  '核心语言包' => THINK_PATH . 'Lang/' . $language . '.php',
						  '全局语言包' => BASE_LANG_PATH . $language . '.php',
						  '项目语言包' => LANG_PATH . $language . '.php',
					 ));
foreach($lang as $n => $v){
	define('LANG_' . trim($n, '_'), $v);
}
echo_line('');

/* 配置项目 */
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
	@echo_line("\t - {$define['dbms']}: {$define['hostname']}{$define['dsn']}");
	hidef_save('ThinkDb' . pathinfo($file, PATHINFO_FILENAME), $define);
}
echo_line('');

/* 标签回调代码 */
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

echo_line('URL二级域名路由定义。');
if(!is_file(BASE_CONF_PATH . 'urlmap.php')){
	file_put_contents(BASE_CONF_PATH . 'urlmap.php', "<?php\nreturn array(\n\t\n);");
	echo_line('$GLOBALS[URL_MAP] -- 文件不存在(BASE_CONF_PATH/urlmap.php)');
}
echo_line('');

/* TODO 错误码定义 */

/* TODO 额外定义 */

/* 调试 */
$debug = require(THINK_PATH . 'Conf/default_debug.php');
hidef_save('ThinkDebug', $debug);

/* 结束载入 */
$compile .= "\n\n";
$compile .= "require CORE_PATH.'Think.class.php';\n";
$compile .= "G('loadTime');// 载入时间\n";
$compile .= "Think::Start();// 初始化\n";
if(SHOW_TRACE){
	$compile .= "ini_set('display_errors', 0);";
}
if(MEMORY_DEBUG) $compile .= "\$GLOBALS['_initUseMems'] = memory_get_usage();\n";
$compile .= "App::run();// 启动应用\n";
if(SHOW_TRACE){
	$compile .= "SPT(false); // 页面Trace显示\n";
}

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
	echo_line('调试模式！');
	$funcLib = "foreach(" . var_export($flist, true) . " as \$file){\n\trequire_once \$file;\n}\n";
	file_put_contents(RUNTIME_PATH . 'functions.php', '<?php ' . $funcLib);
} else{
	$funcLib = compile_core_files($flist);
	file_put_contents(RUNTIME_PATH . 'functions.php', '<?php ' . $funcLib);
	//apc_compile_file(RUNTIME_PATH . 'functions.php');
}
echo_line('');

echo_line('写入入口文件。');
if(is_file(ENTRY_FILE)){
	$origCnt = file_get_contents(ENTRY_FILE);
	if(strpos($origCnt, '[SIG_GENERATE]') === false){
		echo_line(ENTRY_FILE . ' -> 文件不包含签名，生成终止，先删除这个文件再执行。');
		exit;
	}
}
file_put_contents(ENTRY_FILE, $compile);
//apc_compile_file(ENTRY_FILE);
echo_line('');

echo_line('载入全局定义。');
$GLOBALS['URL_MAP'] = require BASE_CONF_PATH . 'urlmap.php';
if(is_file(BASE_CONF_PATH . 'urlmap-' . APP_STATUS . '.php')){
	echo_line('载入状态定义。');
	$GLOBALS['URL_MAP'] = array_merge($GLOBALS['URL_MAP'], require(BASE_CONF_PATH . 'urlmap-' . APP_STATUS .
																   '.php'));
}
foreach($GLOBALS['URL_MAP'] as $app => $url){
	echo_line("\t$app \t -> $url");
}
if(!isset($GLOBALS['URL_MAP'][APP_NAME])){
	echo_line('$GLOBALS[URL_MAP][' . APP_NAME . '] -- 定义有误(BASE_CONF_PATH/urlmap.php)');
	die();
}
echo_line("");

echo_line("写入常量ini文件。");
$ini_path = write_all_define_to_ini();
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
