<?php

echo_line('合并函数库');
$list  = array_merge(glob(THINK_PATH . 'Common/*/*.php'), // 内置函数
					 glob(BASE_LIB_PATH . 'Common/*.php'), // 用户全局函数
					 glob(APP_PATH . 'Common/*.php'), // 用户定义函数
					 glob(EXTEND_PATH . 'Function/*.php')); // Extend里定义的函数

if(CORE_DEBUG){
	echo_line(' -- CORE_DEBUG -- 调试模式，用include方式引入文件。');
	$funcLib = "foreach(" . var_export($list, true) . " as \$file){\n\trequire_once \$file;\n}\n";
} else{
	echo_line("\t - 共".count($list)."个文件");
	$funcLib = compile_core_files($list);
}
file_put_contents(RUNTIME_PATH . 'functions.php', '<?php ' . $funcLib);

/**
 * 编译文件
 *
 * @param array $filearr
 *
 * @return string
 */
function compile_core_files(array $filearr){
	$result      = '';
	foreach($filearr as $filename){
		$content = file_get_contents($filename);
		// 替换预编译指令
		if(!APP_DEBUG){
			$content = preg_replace('#<DEBUG>(.*?)</DEBUG>#s', '', $content);
		}
		$content = substr(trim($content), 5);
		if('?>' == substr($content, -2)){
			$content = substr($content, 0, -2);
		}
		$result .= "/* $filename */\n".$content . "\n";
	}

	return $result;
}
