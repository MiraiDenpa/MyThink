<?php

require $alias['UrlHelper'];

echo_line("定义错误码。");
$error    = require BASE_CONF_PATH . 'error.php';
$revert   = [];
$info_arr = [];
foreach($error as $code => &$info){
	$code += 10000;

	$name = 'ERR_' . strtoupper($info[0]);

	$info_arr[$code] = [
		'name'    => $name,
		'message' => $info[1],
		'info'    => $info[2],
		'url'     => isset($info[3])? $info[3] : [],
	];
	define($name, $code);
}
$info_arr[0] = [
	'name'    => 'ERR_NO_ERROR',
	'message' => 'success',
	'info'    => '',
	'url'     => [],
];
define('ERR_NO_ERROR', 0);
$info_arr[9] = [
	'name'    => 'ERR_NEVER_ERROR',
	'message' => '最强！',
	'info'    => '',
	'url'     => [],
];
define('ERR_NEVER_ERROR', 9);
hidef_save('error-info', $info_arr, true);
echo_line("");
