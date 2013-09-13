<?php
/* 开始生成编译文件 */
$compile = "<?php /* [SIG_GENERATE] */\n";
if(SHOW_TRACE){
	$compile .= "\$GLOBALS['_beginTime'] = microtime(TRUE);\n";
}
if(MEMORY_DEBUG){
	$compile .= "\$GLOBALS['_startUseMems'] = memory_get_usage();\n";
}
$compile .= "require(RUNTIME_PATH.'functions.php');\n";

/* alias “查表导入” 的文件定义。 */
$compile .= require __DIR__ . '/gen_alias.php';

echo_line('');/* 结束载入 */
$compile .= "\n\n";
$compile .= "require \$_think_import_alias['Think'];\n";
$compile .= "G('loadTime');// 载入时间\n";
$compile .= "Think::Start();// 初始化\n";
if(SHOW_TRACE){
	$compile .= "ini_set('display_errors', 0);";
}
if(MEMORY_DEBUG){
	$compile .= "\$GLOBALS['_initUseMems'] = memory_get_usage();\n";
}
$compile .= "/* 启动应用 */\n";
$compile .= substr(file_get_contents(__DIR__ . '/App.php'), 5);

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
