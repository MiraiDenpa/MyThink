<?php
/**
 * 
 *
 * @param array $fn_array
 *
 * @return array
 */
function merge_config(array $fn_array){
	$data = array();
	foreach($fn_array as $name => $file){
		if(is_file($file)){
			echo_line("\t载入{$name}。");
			$data = array_merge($data, include($file));
		}
	}
	return $data;
}
