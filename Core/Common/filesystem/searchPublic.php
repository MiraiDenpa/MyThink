<?php
function searchPublic($name){
	$path = escapeshellarg(PUBLIC_PATH);
	if(is_array($name)){
		$find = [];
		foreach($name as $item){
			if(strpos($item, '/') !== false){
				$find[] = '-name ' . escapeshellarg(basename($item)) . ' -a -path */' .
						  escapeshellarg(dirname($item)) . '/*';
			} else{
				$find[] = '-name ' . escapeshellarg($item);
			}
		}
		$find = implode(' -o ', $find);
	} else{
		if(strpos($name, '/') !== false){
			$find = '-name ' . escapeshellarg(basename($name)) . ' -a -path */' .
					escapeshellarg(dirname($name)) . '/*';
		} else{
			$find = '-name ' . escapeshellarg($name);
		}
	}
	if(strlen(trim($find)) === 0){
		return array();
	}

	$cmd = "/bin/find $path $find";

	$out = array();
	exec($cmd, $out, $ret);
	// <DEBUG>
	if(is_array($name)){
		if(count($name) > count($out)){
			trigger_error(LANG_FILE_NOT_FOUND . ': 试图查找 ' . $cmd, E_USER_NOTICE);
		}
	}
	// </DEBUG>
	if(empty($out)){
		// <DEBUG>
		if(!is_array($name)){
			trigger_error(LANG_FILE_NOT_FOUND . ': 试图查找 ' . $cmd, E_USER_NOTICE);
		}

		// </DEBUG>

		return array();
	}

	return $out;
}
