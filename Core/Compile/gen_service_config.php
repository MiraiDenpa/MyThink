<?php
// 写入nginx配置文件
echo_line("写入nginx配置文件");
if(is_file(CONF_PATH . 'nginx.conf')){
	$NGINX_CONF = file_get_contents(CONF_PATH . 'nginx.conf');
} else{
	$NGINX_CONF = '';
}
ob_start();
require THINK_PATH . 'Tpl/nginx.tpl';
$cnt = ob_get_clean();
if(!is_dir('/etc/nginx/' . PROJECT_NAME . '.d/')){
	mkdir('/etc/nginx/' . PROJECT_NAME . '.d/');
}
file_put_contents('/etc/nginx/' . PROJECT_NAME . '.d/' . APP_NAME . '-ngx.conf', $cnt);

// 写入phpfpm配置文件
echo_line("写入phpfpm配置文件");
if(is_file(CONF_PATH . 'php-fpm.conf')){
	$FPM_CONF = file_get_contents(CONF_PATH . 'php-fpm.conf');
} else{
	$FPM_CONF = '';
}ob_start();
require THINK_PATH . 'Tpl/php-fpm.tpl';
$cnt = ob_get_clean();
file_put_contents('/etc/php-fpm.d/' . APP_NAME . '-fpm.conf', $cnt);
