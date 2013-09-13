<?php
// 写入nginx配置文件
echo_line("写入nginx配置文件");
ob_start();
require THINK_PATH . 'Tpl/nginx.tpl';
$cnt = ob_get_clean();
file_put_contents('/etc/nginx/vhost.d/' . APP_NAME . '-ngx.conf', $cnt);

// 写入phpfpm配置文件
echo_line("写入phpfpm配置文件");
ob_start();
require THINK_PATH . 'Tpl/php-fpm.tpl';
$cnt = ob_get_clean();
file_put_contents('/etc/php-fpm.d/' . APP_NAME . '-fpm.conf', $cnt);
