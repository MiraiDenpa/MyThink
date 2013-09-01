<?php
/**
 * @param string $script
 *
 * @return string
 */
function script_whitespace($script){
	$hash = md5($script);
	$ret  = apc_fetch($hash, $succ);
	if($succ){
		return $ret;
	}
	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	$process = proc_open('uglifyjs2', $descriptorspec, $pipes, $cwd, $env);
	trace('uglifyjs2','执行程序','INFO');

	if(is_resource($process)){
		fwrite($pipes[0], $script);
		fclose($pipes[0]);

		$minize = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$error = stream_get_contents($pipes[1]);
		fclose($pipes[2]);
		if($error){
			trigger_error($error, E_USER_ERROR);
		}

		proc_close($process);

		apc_store($hash,$minize);
		return $minize;
	} else{
		trigger_error('无法执行uglifyjs2。', E_USER_ERROR);
		apc_store($hash,$script);
		return $script;
	}
}
