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

/**
 * ThinkPHP 视图类
 * @category    Think
 * @package     Think
 * @subpackage  Core
 * @author      liu21st <liu21st@gmail.com>
 */
class View{
	/**
	 * 模板输出变量
	 * @var array tVar
	 * @access protected
	 */
	protected $tVar = array();

	/**
	 * 模板变量赋值
	 * @access public
	 *
	 * @param mixed $data
	 */
	public function assign(&$data){
		$this->tVar = & $data;
	}

	/**
	 * 取得模板变量的值
	 * @access public
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get($name = ''){
		if('' === $name){
			return $this->tVar;
		}

		return isset($this->tVar[$name])? $this->tVar[$name] : false;
	}

	/**
	 * 加载模板和页面输出
	 * @access public
	 *
	 * @param string $templateFile 模板文件名
	 * @param string $contentType
	 *
	 * @return null
	 */
	public function display($templateFile = '', $contentType = 'text/html'){
		G('viewStartTime');
		// 视图开始标签
		tag('view_begin', $templateFile);
		// 解析并获取模板内容
		$content = $this->fetch($templateFile);
		// 输出模板内容
		$this->render($content, $contentType);
		// 视图结束标签
		tag('view_end');
	}

	/**
	 * 输出内容文本可以包括Html
	 * @access private
	 *
	 * @param string $content     输出内容
	 * @param string $contentType 输出类型
	 *
	 * @return mixed
	 */
	private function render($content, $contentType = ''){
		if(empty($contentType)){
			$contentType = extension_to_mime(EXTENSION_NAME);
		}
		// 网页字符编码
		header('Content-Type:' . $contentType . '; charset=utf-8');
		header('Cache-control: ' . HTTP_CACHE_CONTROL); // 页面缓存控制
		header('X-Powered-By:ThinkPHP-g');
		// 输出模板文件
		echo $content;
	}

	/**
	 * 解析和获取模板内容 用于输出
	 * @access   public
	 *
	 * @param string $templateFile 模板文件名
	 *
	 * @return string
	 */
	public function fetch($templateFile = ''){
		N('template_show', 1);
		trace('解析模板: ' . $templateFile, '', 'INFO');
		$templateFile = locateTemplate($templateFile);
		// 模板文件不存在抛出异常
		if(!is_file($templateFile)){
			Think::halt(LANG_TEMPLATE_NOT_EXIST . '[' . $templateFile . ']');
		}

		// 解析
		$ob = new OutputBuffer();
		$this->parse($templateFile);
		$content = $ob->get();
		unset($ob);

		// 内容过滤标签
		tag('view_filter', $content);

		// 输出模板文件
		return $content;
	}

	/**
	 * 编译模板然后输出
	 *
	 * @param $file
	 *
	 * @return void
	 */
	private function parse($file){
		$prefix        = strpos($file, BASE_TMPL_PATH) === 0? '' : APP_NAME;
		$tmplCacheFile = CACHE_PATH . $prefix . md5($file) . TMPL_CACHFILE_SUFFIX;
		if(TMPL_NO_CACHE){
			$cache = false;
		} elseif(!is_file($tmplCacheFile)){
			$cache = false;
		} elseif(filemtime($file) > filemtime($tmplCacheFile)){
			// 模板文件如果有更新则缓存需要更新
			$cache = false;
		} else{
			if(APP_DEBUG)trace('模板有缓存: ' . xdebug_filepath_anchor($file, 1, $file) . '[' . date('H:i:s', filemtime($file)) .
				  '] &gt; ' . xdebug_filepath_anchor($tmplCacheFile, 1, $tmplCacheFile) . '[' .
				  date('H:i:s', filemtime($tmplCacheFile)), '', 'INFO');
			$cache = true;
		}
		if(!$cache){ // 缓存有效
			$tpl = ThinkInstance::ThinkTemplate();
			// 编译并加载模板文件
			$tpl->build($file, $this->tVar, $tmplCacheFile);
			trace('编译目标: ' . xdebug_filepath_anchor($tmplCacheFile, 1, $tmplCacheFile) , '', 'INFO');
		}

		extract($this->tVar, EXTR_OVERWRITE);
		include $tmplCacheFile;
	}
}
