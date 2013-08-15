<?php

/**
 * 过滤器方法 引用传值
 * @param string $name 过滤器名称
 * @param string $content 要过滤的内容
 * @return void
 */
function filter($name, &$content) {
	$class      =   $name . 'Filter';
	$filter     =   ThinkInstance::Filter($name);
	$content    =   $filter->run($content);
}


// 过滤表单中的表达式
function filter_exp(&$value){
	if (in_array(strtolower($value),array('exp','or'))){
		$value .= ' ';
	}
}
