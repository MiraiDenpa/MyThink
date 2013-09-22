<?php
/**
 * @param string $script
 *
 * @return string
 */
function script_whitespace($script){
	$hash = md5($script);
	$ret  = SAS('global', $hash);
	if($ret){
		return $ret;
	}

	// 处理php问题
	$replace = $search = '';
	preg_match_all('#<\?php.*?\?>#', $script, $mats);
	foreach($mats[0] as $index => $block){
		$id        = '_StripSpacePhpBlock' . $index;
		$search[]  = $id;
		$replace[] = $block;
	}
	$script = str_replace($replace, $search, $script);

	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	$process = proc_open('uglifyjs2', $descriptorspec, $pipes);
	trace('uglifyjs2', '执行程序', 'INFO');

	$script .= "\n\n";
	if(is_resource($process)){
		fwrite($pipes[0], $script);
		fclose($pipes[0]);

		$minize = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$error = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		if(trim($error)){
			echo('<pre>' . $error . '</pre><pre>' . htmlentities($script));
			ob_end_flush();
			ob_end_flush();
			ob_end_flush();
			exit;
		}

		proc_close($process);

		$minize = str_replace($search, $replace, $minize);
		SAS('global', $hash, $minize);
		return $minize;
	} else{
		trigger_error('无法执行uglifyjs2。', E_USER_ERROR);
		$script = str_replace($search, $replace, $script);
		SAS('global', $hash, $script);
		return $script;
	}
}
