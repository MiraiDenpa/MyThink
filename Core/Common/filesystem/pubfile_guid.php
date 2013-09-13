<?php
use \COM\MyThink\Strings;

/**  */
function pubfile_guid($file){
	$file = preg_replace('#[-_][0-9]+\.[0-9]+\.[0-9]+#', '', $file); // delete version
	$file = str_replace([PUBLIC_URL, PUBLIC_PATH], ['', ''], $file);

	$file = Strings::blocktrim($file, '.css');
	$file = Strings::blocktrim($file, '.js');
	$file = Strings::blocktrim($file, '.less');
	$file = Strings::blocktrim($file, '.min');

	$ns     = explode('/', $file);
	$fn     = array_pop($ns);
	$perfix = array_shift($ns);
	$perfix = $perfix? $perfix . '.' : '';

	return $perfix . $fn;
}
