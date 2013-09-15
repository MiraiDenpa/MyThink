<?php

//  版本信息
define('THINK_VERSION', '3.1.3g');

// 遗留问题
define('TMPL_L_DELIM', '{');
define('TMPL_R_DELIM', '}');

// 待移动定义
define('STR_TRIM_BOTH', 3);
define('STR_TRIM_LEFT', 1);
define('STR_TRIM_RIGHT', 2);

// 路径设置 可在编译入口文件中重新定义 所有路径常量都必须以/ 结尾
defined('CORE_PATH')    or define('CORE_PATH', THINK_PATH . 'Lib/'); // 系统核心类库目录
defined('EXTEND_PATH')  or define('EXTEND_PATH', MTP_PATH . 'Extend/'); // 系统扩展目录
defined('VENDOR_PATH')  or define('VENDOR_PATH', MTP_PATH . 'Vendor/'); // 第三方类库目录
defined('LIBRARY_PATH') or define('LIBRARY_PATH', EXTEND_PATH . 'Library/'); // 扩展类库目录
defined('LOG_PATH')     or define('LOG_PATH', RUNTIME_PATH . 'Logs/'); // 项目日志目录
defined('TEMP_PATH')    or define('TEMP_PATH', RUNTIME_PATH . 'Temp/'); // 项目缓存目录
defined('DATA_PATH')    or define('DATA_PATH', RUNTIME_PATH . 'Data/'); // 项目数据目录
defined('CACHE_PATH')   or define('CACHE_PATH', RUNTIME_PATH . 'Cache/'); // 项目模板缓存目录

defined('LIB_PATH')     or define('LIB_PATH', APP_PATH . 'Lib/'); // 项目类库目录
defined('CONF_PATH')    or define('CONF_PATH', APP_PATH . 'Conf/'); // 项目配置目录
defined('LANG_PATH')    or define('LANG_PATH', APP_PATH . 'Lang/'); // 项目语言包目录
defined('TMPL_PATH')    or define('TMPL_PATH', APP_PATH . 'Tpl/'); // 项目模板目录

defined('BASE_LIB_PATH')     or define('BASE_LIB_PATH', LIB_PATH); // 项目共用文件目录
defined('BASE_CONF_PATH')    or define('BASE_CONF_PATH', BASE_LIB_PATH . 'Conf/'); // 项目配置目录
defined('BASE_LANG_PATH')    or define('BASE_LANG_PATH', BASE_LIB_PATH . 'Lang/'); // 项目语言包目录
defined('BASE_TMPL_PATH')    or define('BASE_TMPL_PATH', BASE_LIB_PATH . 'Tpl/'); // 项目模板目录

echo_line("程序目录（APP_PATH） = " . APP_PATH);
echo_line("库目录（BASE_LIB_PATH） = " . BASE_LIB_PATH);

defined('STATIC_PATH')  or define('STATIC_PATH', APP_PATH . 'Static/'); // 
defined('PUBLIC_PATH')  or define('PUBLIC_PATH', APP_PATH . 'Public/'); // 

defined('STATIC_URL')   or define('STATIC_URL', '/Static'); // 
defined('PUBLIC_URL')   or define('PUBLIC_URL', '/Public'); // 

defined('PHP_SELF')     or define('PHP_SELF', 'index.php');
defined('ENTRY_FILE')   or define('ENTRY_FILE', APP_PATH . PHP_SELF);
