<?php
/**
 * @param string $style
 *
 * @return string
 */
function less_compile($style){
	$hash = md5($style);
	$ret  = SAS('global', $hash);
	if($ret){
		return $ret;
	}

	// 处理php问题
	$replace = $search = '';
	preg_match_all('#<\?php.*?\?>#', $style, $mats);
	foreach($mats[0] as $index => $block){
		$id        = '_StripSpacePhpBlock' . $index;
		$search[]  = $id;
		$replace[] = $block;
	}
	$style = str_replace($replace, $search, $style);

	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	$cmd  = 'lessc --no-ie-compat --yui-compress -sm=on --rp=' . escapeshellarg(PUBLIC_URL . '/') . ' --include-path=' .
			escapeshellarg(PUBLIC_PATH) . ' - ';
	$process = proc_open($cmd, $descriptorspec, $pipes);
	trace('lessc', '执行程序', 'INFO');

	$style .= "\n\n";
	if(is_resource($process)){
		fwrite($pipes[0], $style);
		fclose($pipes[0]);

		$minize = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$error = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		if(trim($error)){
			echo('<pre>' . $error . '</pre><pre>' . htmlentities($style));
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
		$style = str_replace($search, $replace, $style);
		SAS('global', $hash, $style);
		return $style;
	}
}
