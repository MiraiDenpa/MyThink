<?php
/**
 * UG - 提供跨项目的url生成
 * UI - 提供当前Action内简写地址
 */

/**
 * @param string $project 子域名
 * @param string $u1 原来U函数的第一个参数
 * @param array $data 原来U函数的第二个参数
 * @param bool $merge 与当前GET混合
 * @return string
 */
function UG($project, $u1 = null, $data = array(), $merge = false){
	if(is_array($u1)){
		$data = $u1;
		$u1 = $project;
		$project = '';
	}

	if(func_num_args() == 1){
		$u1 = $project;
		$project = '';
	}

	if(!$project){
		$project = APP_NAME;
	}
	$p_define = PROJECT_URL_DEFINE;
	if(!isset($p_define[$project])){
		trigger_error("U2错误：找不到配置段 $project", E_USER_ERROR);
		exit;
	}

	if($merge){
		$merge = $_GET;
		unset($merge['_URL_PARAMS_'],$merge['p']);
		if( !empty($merge) ){
			$data = array_merge($merge,$data);
			//unset($data['p']);
		}
	}

	$uri = U($u1, $data);
	$uri = explode('entry', $uri, 2);
	$uri = explode('.php', $uri[1], 2);
	if(isset($uri[1])){
		$uri = $uri[1];
	}else{
		$uri = $uri[0];
	}

	$uri = ltrim($uri,'/');
	return $p_define[$project].$uri;
}

