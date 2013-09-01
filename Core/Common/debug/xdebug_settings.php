<?php
function var_display_max_children($l = 128){
	ini_set("xdebug.var_display_max_children", $l);
}

function var_display_max_depth($l = 3){
	ini_set("xdebug.var_display_max_depth", $l);
}

function var_display_max_data($l = 512){
	ini_set("xdebug.var_display_max_data", $l);
}

function var_display_html($l = false){
	$last = ini_get('html_errors');
	ini_set('html_errors', $l);
	return $last;
}
