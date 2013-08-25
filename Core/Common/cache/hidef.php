<?php
/**
 * 持久存储，重启php才能刷新
 *
 * @param $name
 * @param $array
 * @param $global
 *
 * @return void
 */
function hidef_save($name, $array, $global = false){
	if(!is_dir(RUNTIME_PATH . 'hidef')){
		mkdir(RUNTIME_PATH . 'hidef', 0777, true);
	}
	file_put_contents(RUNTIME_PATH . 'hidef/' . ($global? 'g' : APP_NAME) . '-' . $name . ".data", serialize($array));
}

function hidef_load($name){
	if(is_file(RUNTIME_PATH . 'hidef/' . APP_NAME . '-' . $name . ".data")){
		return hidef_fetch(APP_NAME . '-' . $name);
	} else{
		return hidef_fetch('g-' . $name);
	}
}
