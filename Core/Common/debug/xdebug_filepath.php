<?php
function xdebug_filepath($file, $line = 0){
	$formart = ini_get('xdebug.file_link_format');
	$url     = str_replace('%f', $file, $formart);
	$url     = str_replace('%l', $line, $url);

	return $url;
}

function xdebug_filepath_anchor($file, $line = 0, $name = ''){
	$formart = ini_get('xdebug.file_link_format');
	$url     = str_replace('%f', $file, $formart);
	$url     = str_replace('%l', $line, $url);
	if(!$name){
		$name = $file.':'.$line;
	}

	return "<a href='{$url}'>{$name}</a>";
}

function xdebug_filepath_print_backtrace($arg = 0, $limit = 0){
	ob_start();
	debug_print_backtrace($arg, $limit);
	$content = ob_get_clean();
	
	$formart = ini_get('xdebug.file_link_format');
	$url     = str_replace('%f', '\1', $formart);
	$url     = str_replace('%l', '\2', $url);
	
	echo preg_replace('#\[(.*):(\d*)\]$#m', "<a href='{$url}'>\\0</a>", $content);
}

