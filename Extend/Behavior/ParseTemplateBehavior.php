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
 * 系统行为扩展：模板解析
 * @category    Think
 * @package     Think
 * @subpackage  Behavior
 * @author      liu21st <liu21st@gmail.com>
 */
class ParseTemplateBehavior extends Behavior{

	// 行为扩展的执行入口必须是run
	public function run(&$_data){
		$_data['prefix'] = !empty($_data['prefix'])? $_data['prefix'] : APP_NAME;
		if($this->checkCache($_data['file'], $_data['prefix'])){ // 缓存有效
			// 分解变量并载入模板缓存
			extract($_data['var'], EXTR_OVERWRITE);
			//载入模版缓存文件
			include CACHE_PATH . $_data['prefix'] . md5($_data['file']) . TMPL_CACHFILE_SUFFIX;
		} else{
			$tpl = ThinkInstance::ThinkTemplate();
			// 编译并加载模板文件
			$tpl->fetch($_data['file'], $_data['var'], $_data['prefix']);
		}
	}

	/**
	 * 检查缓存文件是否有效
	 * 如果无效则需要重新编译
	 * @access public
	 *
	 * @param string $tmplTemplateFile  模板文件名
	 *
	 * @return boolean
	 */
	protected function checkCache($tmplTemplateFile, $prefix = ''){
		$tmplCacheFile = CACHE_PATH . $prefix . md5($tmplTemplateFile) . TMPL_CACHFILE_SUFFIX;
		if(TMPL_DEBUG){
			return false;
		} elseif(!is_file($tmplCacheFile)){
			return false;
		} elseif(filemtime($tmplTemplateFile) > filemtime($tmplCacheFile)){
			// 模板文件如果有更新则缓存需要更新
			return false;
		} else{
			// 缓存有效
			trace('模板有缓存: ' . filemtime($tmplTemplateFile) . ' > ' . filemtime($tmplCacheFile), '', 'INFO');

			return true;
		}
	}
}
