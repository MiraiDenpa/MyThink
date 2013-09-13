<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

defined('THINK_PATH') or exit();

$ret = [];
foreach(glob(CORE_PATH . '*.class.php') as $file){
	$base       = basename($file, '.class.php');
	$ret[$base] = $file;
}

// 系统别名定义文件
return array(
	'Action'         => CORE_PATH . 'Action.class.php',
	'App'            => CORE_PATH . 'App.class.php',
	'Behavior'       => CORE_PATH . 'Behavior.class.php',
	'Cache'          => CORE_PATH . 'Cache.class.php',
	'Db'             => CORE_PATH . 'Db.class.php',
	'Dispatcher'     => CORE_PATH . 'Dispatcher.class.php',
	'Entity'         => CORE_PATH . 'Entity.class.php',
	'Error'          => CORE_PATH . 'Error.class.php',
	'HTML'           => CORE_PATH . 'HTML.class.php',
	'InputStream'    => CORE_PATH . 'InputStream.class.php',
	'Log'            => CORE_PATH . 'Log.class.php',
	'Model'          => CORE_PATH . 'Model.class.php',
	'Mongoo'          => CORE_PATH . 'Mongoo.class.php',
	'OutputBuffer'   => CORE_PATH . 'OutputBuffer.class.php',
	'Page'           => CORE_PATH . 'Page.class.php',
	'Think'          => CORE_PATH . 'Think.class.php',
	'ThinkException' => CORE_PATH . 'ThinkException.class.php',
	'ThinkInstance'  => CORE_PATH . 'ThinkInstance.class.php',
	'UrlHelper'      => CORE_PATH . 'UrlHelper.class.php',
	'View'           => CORE_PATH . 'View.class.php',
	'Widget'         => CORE_PATH . 'Widget.class.php',
	'ThinkTemplate'  => CORE_PATH . 'ThinkTemplate.class.php',
	'TagLib'         => CORE_PATH . 'TagLib.class.php',
);
