<?php
$ret = [];
	
// 全部显示
$ret['LOG_LEVEL'] = 'INFO,EMERG,CRIT,ALERT|ERR|WARN|NOTIC,DEBUG,SQL,CURL,CACHE';
// 调试的时候不要写入磁盘
$ret['LOG_RECORD'] = false;

// 这些标签被上色
$ret['DEBUG_TAB_TYPE'] = array(
	'EMERG' => 'label-important',
	'ALERT' => 'label-important',
	'CRIT'  => 'label-inverse',
	'ERR'   => 'label-important',
	'WARN'  => 'label-warning',
	'NOTIC' => 'label-warning'
);

// 这些输出被认为是严重错误，自动打开调试面板，要与 TRACE_PAGE_TABS 定义匹配
$alert = explode(',', 'EMERG,CRIT,ALERT|ERR|WARN|NOTIC');

/* 以下自动处理 TRACE_PAGE_TABS 变量 */
$alert                  = array_combine($alert, array_fill(0, count($alert), 'red'));
$ret['TRACE_PAGE_TABS'] = array('BASE' => '基本', 'FILE' => '文件', 'INFO' => '流程');

$tabs = explode(',', $ret['LOG_LEVEL']);

foreach($ret['TRACE_PAGE_TABS'] as $k => $tab){
	$ret['TRACE_PAGE_TABS'][$k] = array($tab, $k, 'static '.@$alert[$k]);
}
foreach($tabs as $tab){
	$ret['TRACE_PAGE_TABS'][$tab] = array($tab, $tab, 'normal '.@$alert[$tab]);
}
$ret['TRACE_PAGE_TABS']['***'] = array('LAST_LOG', '***', 'next');
foreach($tabs as $k => $tab){
	$tabtype                    = str_replace('|', '|LAST_', $tab);
	$ret['TRACE_PAGE_TABS'][$k] = array($tab, 'LAST_'.$tabtype, 'last '.@$alert[$tab]);
}

return $ret;

