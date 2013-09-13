<?php
// 检查配置文件
echo_line("测试fpm配置文件");
exec('php-fpm -t 2>&1', $print, $ret);
if($ret){
	echo_line(" *** php-fpm 配置文件生成失败 ***");
	echo_line(implode("\n", $print));
	exit;
}
echo_line("测试nginx配置文件");
exec('nginx -t 2>&1', $print, $ret);
if($ret){
	echo_line(" *** nginx 配置文件生成失败 ***");
	echo_line(implode("\n", $print));
	exit;
}

// 重启服务
echo_line("重启服务");
if(is_file('/usr/bin/systemctl')){
	system('systemctl restart php-fpm nginx');
	passthru('systemctl status php-fpm nginx | grep "Active:"');
} else{
	passthru('service php-fpm restart');
	passthru('service nginx restart');
}
