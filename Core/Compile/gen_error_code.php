<?php

require $alias['UrlHelper'];

function _UC($app,$action,$model,$path='',$vars=[],$suffix='html'){
	static $hp;
	if(!$hp){
		$hp = @new UrlHelper($GLOBALS['URL_MAP']);
	}

	$hp->setApp($app);
	$hp->setAction($action);
	$hp->setMethod($model);
	$hp->setProtocol('http');
	$hp->setParamAll($vars);
	$hp->setSuffix($suffix);
	$hp->setPath($path);

	return $hp->getUrl();
}

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
		'url'     => isset($info[3])?$info[3]:[],
	];
	define($name, $code);
}
hidef_save('error-info', $info_arr, true);
echo_line("");
