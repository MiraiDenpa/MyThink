<?php

/**
 *
 * @param $act
 * @param $mtd
 * @param &$params
 *
 * @return string
 * @static
 */
function map_path($act, $mtd, &$params){
	$param_map = hidef_load('actmap');

	if(isset($param_map[$act][$mtd])){
		$path = [];
		foreach($param_map as $index => $info){
			$name = $info[0];
			if(isset($params[$name])){
				$path[$index] = $params[$name];
				unset($params[$name]);
			} else{
				Think::halt('map_path() 缺少参数，需要 [' . $name . ']。');
			}
		}

		return $act . '/' . $mtd . implode_l('/', $path);
	} else{
		return $act . '/' . $mtd;
	}
}
