<?php

/**
 * 吧当前定义的所有用户常量保存到ini文件里
 *
 * @return string ini文件路径
 */
function write_all_define_to_ini($ini_path){
	$consts = get_defined_constants(true);
	$consts = $consts['user'];

	ksort($consts);
	$def = '';
	foreach($consts as $k => $v){
		if(is_int($v) || is_long($v)){
			$def .= "int {$k} = {$v}\n";
		} elseif(is_bool($v)){
			$def .= "bool {$k} = " . ($v? 'true' : 'false') . "\n";
		} elseif(is_float($v) || is_double($v)){
			$def .= "float {$k} = {$v}\n";
		} else{
			$def .= "str {$k} = " . var_export('' . $v, true) . "\n";
		}
	}
	if(!is_dir(RUNTIME_PATH . APP_NAME)){
		mkdir(RUNTIME_PATH . APP_NAME, 0777, true);
	}
	file_put_contents($ini_path . '/const.ini', $def);
	if(APP_DEBUG){
		echo_line("写入常量调试符号。");
		$defines = '';
		foreach($consts as $k => $v){
			$defines .= "define('{$k}', " . var_export($v, true) . ");\n";
		}
		file_put_contents(RUNTIME_PATH . APP_NAME . '/const.php', "<?php /* 调试文件 */\n" . $defines);
	}
}
