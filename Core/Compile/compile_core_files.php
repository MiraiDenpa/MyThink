<?php

/**
 * 编译文件
 *
 * @param array $filearr
 *
 * @return string
 */
function compile_core_files(array $filearr){
	$result      = '';
	$lonest_name = 0;
	if(APP_DEBUG){
		foreach($filearr as $filename){
			$lonest_name = max($lonest_name, strlen(basename($filename)));
		}
	}
	foreach($filearr as $filename){
		if(APP_DEBUG){
			echo_line("\t - " . $filename);
		}
		$content = file_get_contents($filename);
		// 替换预编译指令
		if(!APP_DEBUG){
			$content = preg_replace('#<DEBUG>(.*?)</DEBUG>#s', '', $content);
		}
		$content = substr(trim($content), 5);
		if('?>' == substr($content, -2)){
			$content = substr($content, 0, -2);
		}
		$result .= $content . "\n";
	}

	return $result;
}
