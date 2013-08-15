<?php
// 调试模式加载系统默认的配置文件
// 读取调试模式的应用状态
if(is_file(CONF_PATH . APP_STATUS . '.php')){// 允许项目增加开发模式配置定义
	C(include CONF_PATH . APP_STATUS . '.php');
}
