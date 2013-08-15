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
/**
 * 系统行为扩展：页面Trace显示输出
 * @category   Think
 * @package  Think
 * @subpackage  Behavior
 * @author   liu21st <liu21st@gmail.com>
 */
class ShowPageTraceBehavior extends Behavior {
	// 行为扩展的执行入口必须是run
	public function run(&$params){
		if(!IS_AJAX && CONF_SHOW_PAGE_TRACE ) {
			echo $this->showTrace();
		}elseif(defined('FORCE_NOT_AJAX')){
			echo $this->showTrace();
		}
	}

	/**
	 * 显示页面Trace信息
	 * @access private
	 */
	private function showTrace() {
		// 系统默认显示信息
		$trace  =   array();
		$base   =   array(
			'请求信息'  =>  date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']).' '.$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'].' : '.__SELF__,
			'运行时间'  =>  $this->showTime(),
			'吞吐率'	=>	number_format(1/G('beginTime','viewEndTime'),2).'req/s',
			'内存开销'  =>  MEMORY_LIMIT_ON?number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024,2).' kb':'不支持',
			'查询信息'  =>  N('db_query').' queries '.N('db_write').' writes ',
			'文件加载'  =>  count(get_included_files()),
			'缓存信息'  =>  N('cache_read').' gets '.N('cache_write').' writes ',
			'会话信息'  =>  'SESSION_ID='.session_id(),
		);
		// 读取项目定义的Trace文件
		$traceFile  =   CONF_PATH.'trace.php';
		if(is_file($traceFile)) {
			$base   =   array_merge($base, (array)include($traceFile));
		}
		$debug  =   trace();
		$debug['INFO'][] = '* 日志被打印，后方信息无法显示 *';
		$tabs   =   hidef_fetch('TRACE_PAGE_TABS');
		foreach ($tabs as $name=>$tab_def){
			if(!is_array($tab_def)){
				$tab_def = array($tab_def,$name,'','');
			}else{
				$name = $tab_def[1];
			}
			switch(strtoupper($name)) {
			case 'BASE':// 基本信息
				$tab_def[] = $base;
				$trace[] = $tab_def;
				break;
			case 'FILE': // 文件信息
				$files  =  get_included_files();
				$info   =   array();
				foreach ($files as $key=>$file){
					$info[] = '#'.$key.': '.$file;
				}
				$tab_def[] = $info;
				$trace[] = $tab_def;
				break;
			default:// 调试信息
				$name       =   strtoupper($name);
				if($name == '***'){ // 标签
					$trace[]  = $tab_def;
				}elseif(strpos($name,'|')) { // 多组信息
					$array  =   explode('|',$name);
					$result =   array();
					foreach($array as $name){
						$result   +=   isset($debug[$name])?$debug[$name]:array();
					}
					if(!empty($result)){
						$tab_def[] = $result;
						$trace[] = $tab_def;
					}
				}else{ // 单组信息
					if(isset($debug[$name])){
						$tab_def[] = $debug[$name];
						$trace[] = $tab_def;
					}
				}
			}
		}
		if($save = CONF_PAGE_TRACE_SAVE) { // 保存页面Trace日志
			if(is_array($save)) {// 选择选项卡保存
				$tabs   =   hidef_fetch('TRACE_PAGE_TABS');
				$array  =   array();
				foreach ($save as $tab){
					$array[] =   $tabs[$tab];
				}
			}
			$content    =   date('[ c ]').' '.get_client_ip().' '.$_SERVER['REQUEST_URI']."\r\n";
			foreach ($trace as $key=>$val){
				if(!isset($array) || in_array($key,$array)) {
					$content    .=  '[ '.$key." ]\r\n";
					if(is_array($val)) {
						foreach ($val as $k=>$v){
							$content .= (!is_numeric($k)?$k.':':'').print_r($v,true)."\r\n";
						}
					}else{
						$content .= print_r($val,true)."\r\n";
					}
					$content .= "\r\n";
				}
			}
			error_log(str_replace('<br/>',"\r\n",$content), Log::FILE,LOG_PATH.date('y_m_d').'_trace.log');
		}
		unset($files,$info,$base);
		// 调用Trace页面模板
		ob_start();
		include CONF_TMPL_TRACE_FILE;
		return ob_get_clean();
	}

	/**
	 * 获取运行时间
	 */
	private function showTime() {
		// 显示运行时间
		G('beginTime',$GLOBALS['_beginTime']);
		G('viewEndTime');
		// 显示详细运行时间
		return G('beginTime','viewEndTime').'s ( Load:'.G('beginTime','loadTime').'s Init:'.G('loadTime','initTime').'s Exec:'.G('initTime','viewStartTime').'s Template:'.G('viewStartTime','viewEndTime').'s )';
	}
}
