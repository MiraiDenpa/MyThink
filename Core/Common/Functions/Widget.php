<?php
/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传入的参数
 * @param boolean $return 是否返回内容
 * @param string $path Widget所在路径
 * @return string
 */
function W($name, $data=array(), $return=false,$path='') {
	$class      =   $name . 'Widget';
	$path       =   empty($path) ? BASE_LIB_PATH : $path;
	require_cache($path . 'Widget/' . $class . '.class.php');
	if (!class_exists($class))
		throw_exception(L('_CLASS_NOT_EXIST_') . ':' . $class);
	$widget     =   ThinkInstance::get($class);
	$content    =   $widget->render($data);
	if ($return)
		return $content;
	else
		echo $content;
}
