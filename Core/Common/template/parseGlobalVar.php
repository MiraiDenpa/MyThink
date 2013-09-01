<?php
/**
 * 用于标签属性里面的特殊模板变量解析
 * 格式 以 Think. 打头的变量属于特殊模板变量
 * @access public
 *
 * @param string $varStr  变量字符串
 *
 * @return string
 */
function parseGlobalVar($varStr){
	$vars     = explode('.', substr($varStr, 2));
	$vars[0]  = strtoupper(trim($vars[0]));
	$parseStr = '';
	if(count($vars) > 1){
		$vars[1] = trim($vars[1]);
		switch($vars[0]){
		case 'C':
			$parseStr = strtoupper($vars[1]);
			break;
		case 'L':
			$parseStr = 'LANG_' . strtoupper($vars[1]);
			break;
		default:
			trigger_error('未知全局变量名：'.$vars[0],E_USER_NOTICE);
		}
	} else if(count($vars) == 1){
		switch($vars[0]){
		case 'NOW':
			$parseStr = "date('Y-m-d g:i a')";
			break;
		case 'DLEFT':
			$parseStr = 'TMPL_L_DELIM';
			break;
		case 'DRIGHT':
			$parseStr = 'TMPL_R_DELIM';
			break;
		default:
			if(\COM\MyThink\Strings::isEndWith($vars[0],'__')){
				return strtoupper(trim($vars[0],'_'));
			}
			trigger_error('未知全局变量名：'.$vars[0],E_USER_NOTICE);
		}
	}

	return $parseStr;
}
