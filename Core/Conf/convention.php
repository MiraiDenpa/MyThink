<?php
// 默认配置文件
return array(
	/* 项目设定 */
	'APP_SUB_DOMAIN_DEPLOY' => false,
	// 是否开启子域名部署
	'APP_SUB_DOMAIN_RULES'  => array(),
	// 子域名部署规则
	'APP_SUB_DOMAIN_DENY'   => array(),
	//  子域名禁用列表

	/* Cookie设置 */
	'COOKIE_EXPIRE'         => 1800,
	// Coodie有效期
	'COOKIE_DOMAIN'         => '',
	// Cookie有效域名
	'COOKIE_PATH'           => '/',
	// Cookie路径
	'COOKIE_PREFIX'         => '',
	// Cookie前缀 避免冲突

	'DEFAULT_APP'           => '@',
	// 默认项目名称，@表示当前项目
	'DEFAULT_LANG'          => 'zh-cn',
	// 默认语言
	'DEFAULT_AJAX_RETURN'   => 'JSON',
	// 默认AJAX 数据返回格式,可选JSON XML ...
	'DEFAULT_JSONP_HANDLER' => 'jsonpReturn',
	// 默认JSONP格式返回的处理方法

	/* 数据库设置 */
	'DB_TYPE'               => 'mysqli',
	// 数据库类型
	'DB_HOST'               => 'localhost',
	// 服务器地址
	'DB_NAME'               => '',
	// 数据库名
	'DB_USER'               => 'root',
	// 用户名
	'DB_PWD'                => '',
	// 密码
	'DB_PORT'               => '',
	// 端口	
	'DB_PREFIX'             => '',
	// 数据库表前缀
	'DB_FIELDTYPE_CHECK'    => false,
	// 是否进行字段类型检查
	'DB_FIELDS_CACHE'       => true,
	// 启用字段缓存
	'DB_CHARSET'            => 'utf8',
	// 数据库编码默认采用utf8
	'DB_DEPLOY_TYPE'        => 0,
	// 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
	'DB_RW_SEPARATE'        => false,
	// 数据库读写是否分离 主从式有效
	'DB_MASTER_NUM'         => 1,
	// 读写分离后 主服务器数量
	'DB_SLAVE_NO'           => '',
	// 指定从服务器序号
	'DB_SQL_BUILD_CACHE'    => false,
	// 数据库查询的SQL创建缓存
	'DB_SQL_BUILD_QUEUE'    => 'apc',
	// SQL缓存队列的缓存方式 支持 file xcache和apc
	'DB_SQL_BUILD_LENGTH'   => 20,
	// SQL缓存的队列长度
	'DB_SQL_LOG'            => true,
	// SQL执行日志记录

	/* 数据缓存设置 */
	'DATA_CACHE_TIME'       => 0,
	// 数据缓存有效期 0表示永久缓存
	'DATA_CACHE_COMPRESS'   => false,
	// 数据缓存是否压缩缓存
	'DATA_CACHE_CHECK'      => false,
	// 数据缓存是否校验缓存
	'DATA_CACHE_PREFIX'     => '',
	// 缓存前缀
	'DATA_CACHE_TYPE'       => 'Memcached',
	// 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator

	/* 日志设置 */
	'LOG_RECORD'            => false,
	// 默认不记录日志
	'LOG_TYPE'              => 3,
	// 日志记录类型 0 系统 1 邮件 3 文件 4 SAPI 默认为文件方式
	'LOG_DEST'              => '',
	// 日志记录目标
	'LOG_EXTRA'             => '',
	// 日志记录额外信息
	'LOG_LEVEL'             => 'EMERG,ALERT,CRIT,ERR,NOTICE' . (APP_DEBUG? ',DEBUG,LOG,INFO' : ''),
	// 允许记录的日志级别
	'LOG_FILE_SIZE'         => 1024*1024*30,
	// 日志文件大小限制
	'LOG_EXCEPTION_RECORD'  => true,
	// 是否记录异常信息日志

	/* 模板引擎设置 */
	'TMPL_CONTENT_TYPE'     => 'text/html',
	// 默认模板输出类型
	'TMPL_ACTION_ERROR'     => THINK_PATH . 'Tpl/dispatch_jump.tpl',
	// 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS'   => THINK_PATH . 'Tpl/dispatch_jump.tpl',
	// 默认成功跳转对应的模板文件
	'TMPL_EXCEPTION_FILE'   => THINK_PATH . 'Tpl/think_exception.tpl',
	// 异常页面的模板文件
	'TMPL_TEMPLATE_SUFFIX'  => '.html',
	// 默认模板文件后缀
	'TMPL_FILE_DEPR'        => '_',
	//模板文件MODULE_NAME与ACTION_NAME之间的分割符

	/* URL设置 */
	'DEFAULT_EXTENSION'             => 'html',
	// 默认返回格式
	'URL_PATHINFO_DEPR'     => '/',
	// PATHINFO模式下，各参数之间的分割符号
	'URL_404_REDIRECT'      => '',
	// 404 跳转页面 部署模式有效

	/* 系统变量名称设置 */
	'VAR_GROUP'             => 'g',
	// 默认分组获取变量
	'VAR_MODULE'            => 'm',
	// 默认模块获取变量
	'VAR_ACTION'            => 'a',
	// 默认操作获取变量
	'VAR_AJAX_SUBMIT'       => 'ajax',
	// 默认的AJAX提交变量
	'VAR_JSONP_HANDLER'     => 'callback',
	'VAR_URL_PARAMS'        => '_URL_',
	// PATHINFO URL参数变量

	'HTTP_CACHE_CONTROL'    => 'private',
	// 网页缓存控制

	'SHOW_PAGE_TRACE'       => TRACE_DEBUG,
	// 显示页面Trace信息
	'TRACE_PAGE_TABS'       => array('BASE'      => '基本',
									 'FILE'      => '文件',
									 'INFO'      => '流程',
									 'ERR|NOTIC' => '错误',
									 'SQL'       => 'SQL',
									 'DEBUG'     => '调试',
	), // 页面Trace可定制的选项卡
	'PAGE_TRACE_SAVE'       => !APP_DEBUG,
);