<?php
function xdebug_max_children($l = 128){
	ini_set("xdebug.var_display_max_children", $l);
}

function xdebug_max_depth($l = 3){
	ini_set("xdebug.var_display_max_depth", $l);
}

function xdebug_max_data($l = 512){
	ini_set("xdebug.var_display_max_data", $l);
}

function xdebug_html($l = false){
	$last = ini_get('html_errors');
	ini_set('html_errors', $l);
	return $last;
}
