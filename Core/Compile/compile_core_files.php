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
		echo_line("\t - " . $filename);
		$content = file_get_contents($filename);
		// 替换预编译指令
		$content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
		$content = substr(trim($content), 5);
		if('?>' == substr($content, -2)){
			$content = substr($content, 0, -2);
		}
		if(APP_DEBUG){
			$lines = explode("\n", $content);
			$linen = 0;
			$fn    = basename($filename);
			foreach($lines as &$line){
				$linen++;
				if(strpos($line, " *")===0){
					$line = str_pad("   {$fn}:{$linen}   ", $lonest_name, ' ', STR_PAD_RIGHT) . $line;
				}else{
					$line = str_pad("/* {$fn}:{$linen} */", $lonest_name, ' ', STR_PAD_RIGHT) . $line;
				}
			}
			$content = implode("\n", $lines);
		}
		$result .= $content . "\n";
	}

	return $result;
}
