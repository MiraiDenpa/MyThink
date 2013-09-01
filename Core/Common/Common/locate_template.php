<?php
/**
 * 自动定位模板文件
 * @access protected
 *
 * @param string $template 模板文件规则
 *
 * @return string
 */
function locateTemplate($template = ''){
	// 分析模板文件规则
	if(!$template){ // 如果模板文件名为空 按照默认规则定位
		$template = METHOD_NAME;
	}
	if($template{0} == ':'){ // 当前项目最外部
		$template = substr($template, 1);
		return TMPL_PATH . $template . TMPL_TEMPLATE_SUFFIX;
	} elseif($template{0} == '/'){ // 绝对路径
		return $template;
	} elseif($template{0} == '!'){ // 在BaseTpl里找路径
		$template = substr($template, 1);
		if(is_file(BASE_TMPL_PATH . $template . TMPL_TEMPLATE_SUFFIX)){
		return BASE_TMPL_PATH . $template . TMPL_TEMPLATE_SUFFIX;
		}else{
			return THINK_PATH .'Tpl/' . $template . TMPL_TEMPLATE_SUFFIX;
		}
	} else{ // 当前项目，当前action
		return TMPL_PATH . ACTION_NAME . '/' . strtolower(APP_NAME . '.' . ACTION_NAME . '.' . $template) .
			   TMPL_TEMPLATE_SUFFIX;
	}
}
