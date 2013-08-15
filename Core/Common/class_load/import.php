<?php
/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolean
 */
function import($class, $baseUrl = '', $ext='.class.php') {
	static $_file = array();
	$class = str_replace(array('.', '#'), array('/', '.'), $class);
	if ('' === $baseUrl && false === strpos($class, '/')) {
		// 检查别名导入
		return alias_import($class);
	}
	if (isset($_file[$class . $baseUrl]))
		return true;
	else
		$_file[$class . $baseUrl] = true;
	$class_strut     = explode('/', $class);
	if (empty($baseUrl)) {
		$libPath    =   defined('BASE_LIB_PATH')?BASE_LIB_PATH:LIB_PATH;
		if ('@' == $class_strut[0] || APP_NAME == $class_strut[0]) {
			//加载当前项目应用类库
			$baseUrl = dirname($libPath);
			$class   = substr_replace($class, basename($libPath).'/', 0, strlen($class_strut[0]) + 1);
		}elseif ('think' == strtolower($class_strut[0])){ // think 官方基类库
			$baseUrl = CORE_PATH;
			$class   = substr($class,6);
		}elseif (in_array(strtolower($class_strut[0]), array('org', 'com'))) {
			// org 第三方公共类库 com 企业公共类库
			$baseUrl = LIBRARY_PATH;
		}else { // 加载其他项目应用类库
			$class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
			$baseUrl = APP_PATH . '../' . $class_strut[0] . '/'.basename($libPath).'/';
		}
	}
	if (substr($baseUrl, -1) != '/')
		$baseUrl    .= '/';
	$classfile       = $baseUrl . $class . $ext;
	if (!class_exists(basename($class),false)) {
		// 如果类不存在 则导入类库文件
		return require_cache($classfile);
	}
}
