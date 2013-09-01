<?php
$language = 'zh-cn';
echo_line("语言文件： {$language}");
$lang = merge_config(array(
						  '核心语言包' => THINK_PATH . 'Lang/' . $language . '.php',
						  '全局语言包' => BASE_LANG_PATH . $language . '.php',
						  '项目语言包' => LANG_PATH . $language . '.php',
					 ));
foreach($lang as $n => $v){
	define('LANG_' . trim($n, '_'), $v);
}
echo_line('');
