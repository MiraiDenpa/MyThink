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
 * ThinkPHP内置模板引擎类
 * 支持XML标签和普通标签的模板解析
 * 编译型模板引擎 支持动态缓存
 * @category    Think
 * @package     Think
 * @subpackage  Template
 * @author      liu21st <liu21st@gmail.com>
 */
class  ThinkTemplate{

	// 模板页面中引入的标签库列表
	protected $tagLib = array();
	// 当前模板文件
	protected $templateFile = '';
	// 模板变量
	public $tVar = array();
	public $config = array();
	private $literal = array();
	private $block = array();

	/**
	 * 架构函数
	 * @access public
	 */
	public function __construct(){
		$this->config['template_suffix'] = '.html';
		$this->config['cache_suffix']    = TMPL_CACHFILE_SUFFIX;
		$this->config['cache_path']      = CACHE_PATH;
		$this->config['taglib_begin']    = '<';
		$this->config['taglib_end']      = '>';
		$this->config['tmpl_begin']      = '\\{';
		$this->config['tmpl_end']        = '\\}';
		$this->config['layout_item']     = TMPL_LAYOUT_ITEM;
	}

	// 模板变量获取和设置
	public function get($name){
		if(isset($this->tVar[$name])){
			return $this->tVar[$name];
		} else{
			return false;
		}
	}

	public function set($name, $value){
		$this->tVar[$name] = $value;
	}

	/**
	 * 加载模板，返回html
	 * @access   public
	 *
	 * @param string $templateFile     模板文件
	 * @param array  $templateVar      模板变量
	 *
	 * @return bool
	 */
	public function fetch($templateFile, $templateVar){
		trace($templateFile, '临时模板显示', 'INFO');
		$tmpf = CACHE_PATH . 'tmp' . time();
		$this->build($templateFile, $templateVar, $tmpf);
		// 模板阵列变量分解成为独立变量
		extract($templateVar, EXTR_OVERWRITE);
		//载入模版缓存文件
		$ret = include $tmpf;

		return $ret;
	}

	/**
	 * 编译模板，保存到 $targetFile 指定的位置，待以后载入
	 *
	 * @param $templateFile
	 * @param $templateVar
	 * @param $targetFile
	 *
	 * @return void
	 */
	public function build($templateFile, $templateVar, $targetFile){
		$this->tVar = $templateVar;
		$content    = $this->loadTemplate($templateFile);
		// 检测模板目录
		$dir = dirname($targetFile);
		if(!is_dir($dir)){
			mkdir($dir, 0755, true);
		}
		// 写Cache文件
		if(false === file_put_contents($targetFile, $content)){
			throw_exception(LANG_CACHE_WRITE_ERROR . ':' . $targetFile);
		}
	}

	/**
	 * 加载主模板并缓存
	 * @access public
	 *
	 * @param string $tmplTemplateFile 模板文件
	 *
	 * @return string
	 */
	public function loadTemplate($tmplTemplateFile){
		trace(xdebug_filepath_anchor($tmplTemplateFile, 1, $tmplTemplateFile), '模板加载', 'INFO');
		$this->templateFile = $tmplTemplateFile;
		// 读取模板文件内容
		$tmplContent = file_get_contents($tmplTemplateFile);

		// 编译模板内容
		return $this->compiler($tmplContent);
	}

	/**
	 * 编译模板文件内容
	 * @access protected
	 *
	 * @param mixed $tmplContent 模板内容
	 *
	 * @return string
	 */
	protected function compiler($tmplContent){
		// 模板解析
		$tmplContent = $this->parse($tmplContent);
		// 还原被替换的Literal标签
		$tmplContent = preg_replace_callback('/<!--###literal(\d+)###-->/is',
			function ($mats){
				return $this->restoreLiteral($mats[1]);
			},
											 $tmplContent
		);

		// 优化生成的php代码
		$tmplContent = str_replace('?><?php', '', $tmplContent);
		return TMPL_READABLE? html_beautify($tmplContent, true) : html_whitespace($tmplContent, true);
	}

	/**
	 * 模板解析入口
	 * 支持普通标签和TagLib解析 支持自定义标签库
	 * @access public
	 *
	 * @param string $content 要解析的模板内容
	 *
	 * @return string
	 */
	public function parse($content){
		trace('内容解析' . dump_some($content), '模板编译', 'INFO');
		N('template_parse', 1);
		// 内容为空不解析
		if(empty($content)){
			return '';
		}

		$begin = $this->config['taglib_begin'];
		$end   = $this->config['taglib_end'];
		// 检查include语法
		$content = $this->parseInclude($content);
		// 解析[%xxxxx%]标签
		$content = $this->parseInline($content);

		// 检查PHP语法
		$content = $this->parsePhp($content);
		// 首先替换literal标签内容
		$content = preg_replace_callback('/' . $begin . 'literal' . $end . '(.*?)' . $begin . '\/literal' . $end .
										 '/is',
			function ($mats){
				return $this->parseLiteral($mats[1]);
			},
										 $content
		);

		// 内置标签库 无需使用taglib标签导入就可以使用
		$tagLibs = explode(',', TAGLIB_BUILD_IN);
		// 获取需要引入的标签库列表
		// 标签库只需要定义一次，允许引入多个一次
		// 一般放在文件的最前面
		// 格式：<taglib name="html,mytag..." />
		$this->getIncludeTagLib($content);
		if(!empty($this->tagLib)){
			$tagLibs = array_merge($tagLibs, $this->tagLib);
		}

		$this->parseTagLibAll($tagLibs, $content);

		$this->parseReplace($content);

		$this->parseTagLibAll($tagLibs, $content);

		//解析普通模板标签 {tagName}
		preg_match_all('/(' . $this->config['tmpl_begin'] . ')([^\d\s' . $this->config['tmpl_begin'] .
					   $this->config['tmpl_end'] . '].+?)(' . $this->config['tmpl_end'] . ')/is',
					   $content,
					   $mats
		);
		foreach($mats[2] as $i => $repl){
			$repl    = $this->parseTag($repl);
			$content = str_replace($mats[0][$i], $repl, $content);
		}

		return $content;
	}

	/**
	 * @param $content
	 *
	 * @return void
	 */
	protected function parseReplace(&$content){
		// 替换特殊标志符号 如 {__STYLES__} 
		while(preg_match_all('/\{__([A-Z0-9_]+)__\}/', $content, $mats)){
			foreach($mats[1] as $i => $fnName){
				$method  = 'TaglibReplace' . ucfirst(strtolower($fnName));
				$content = str_replace($mats[0][$i], $method(), $content);
			}
		}
	}

	// 检查PHP语法
	protected function parsePhp($content){
		return str_replace('<?=', '<?php echo ', $content);
	}

	// 解析模板中的布局标签
	protected function parseLayout($content){
		// 读取模板中的布局标签
		$find = preg_match('/' . $this->config['taglib_begin'] . 'layout\s(.+?)\s*?' . $this->config['taglib_end'] .
						   '(.*)' . $this->config['taglib_begin'] . '\/layout' . $this->config['taglib_end'] . '/is',
						   $content,
						   $matches
		);
		if($find){
			//替换Layout标签
			$content = str_replace($matches[0], '', $content);
			//解析Layout标签
			$array = $this->parseXmlAttrs($matches[1]);
			// 读取布局模板
			if(isset($array['name']) && $array['name']){
				$layoutFile = locateTemplate($array['name']);
			} elseif(isset($array['file']) && $array['file']){
				$layoutFile = $array['file'];
			} else{
				trigger_error('模板中layout标签，使用file或name属性定义模板位置。', E_USER_ERROR);
				exit;
			}

			if(!is_file($layoutFile)){
				trigger_error(LANG_TEMPLATE_NOT_EXIST . '(layout)[' . $layoutFile . ']', E_USER_ERROR);
			}
			$replace = isset($array['replace'])? $array['replace'] : $this->config['layout_item'];
			// 替换布局的主体内容
			$prepend = empty($matches[2])? '' : '<?php ' . $matches[2] . ' ?>';
			$content = str_replace($replace, $content, $prepend . file_get_contents($layoutFile));
		}

		return $content;
	}

	//解析模板中的[% xxx %]标签
	protected function parseInline($content){
		return preg_replace_callback([
									 '#\[%([a-z0-9A-Z]+)%\].*?\[%/\1\%]#s',
									 '#\[%([a-z0-9A-Z]+) .*?/%\]#s',
									 ],
			function ($mats){
				$str = str_replace(['[%', '%]'], ['<', '>'], $mats[0]);
				return $this->parse($str);
			},
									 $content
		);
	}

	// 解析模板中的include标签
	protected function parseInclude($content){
		// 解析布局
		$content = $this->parseLayout($content);
		// 读取模板中的include标签
		$find = preg_match_all('/' . $this->config['taglib_begin'] . 'include\s(.+?)\s*?\/' .
							   $this->config['taglib_end'] . '/is',
							   $content,
							   $matches
		);
		if($find){
			for($i = 0; $i < $find; $i++){
				$include = $matches[1][$i];
				$array   = $this->parseXmlAttrs($include);
				$file    = $array['file'];
				unset($array['file']);
				$content = str_replace($matches[0][$i], $this->parseIncludeItem($file, $array), $content);
			}
		}

		return $content;
	}

	/**
	 * 分析XML属性
	 * @access private
	 *
	 * @param string $attrs  XML属性字符串
	 *
	 * @return array
	 */
	private function parseXmlAttrs($attrs){
		$xml = '<tpl><tag ' . $attrs . ' /></tpl>';
		$xml = simplexml_load_string($xml);
		if(!$xml){
			throw_exception(LANG_XML_TAG_ERROR);
		}
		$xml   = (array)($xml->tag->attributes());
		$array = array_change_key_case($xml['@attributes']);

		return $array;
	}

	/**
	 * 替换页面中的literal标签
	 * @access private
	 *
	 * @param string $content  模板内容
	 *
	 * @return string|false
	 */
	private function parseLiteral($content){
		if(trim($content) == ''){
			return '';
		}
		$content           = stripslashes($content);
		$i                 = count($this->literal);
		$parseStr          = "<!--###literal{$i}###-->";
		$this->literal[$i] = $content;

		return $parseStr;
	}

	/**
	 * 还原被替换的literal标签
	 * @access private
	 *
	 * @param string $tag  literal标签序号
	 *
	 * @return string|false
	 */
	private function restoreLiteral($tag){
		// 还原literal标签
		$parseStr = $this->literal[$tag];
		// 销毁literal记录
		unset($this->literal[$tag]);

		return $parseStr;
	}

	/**
	 * 记录当前页面中的block标签
	 * @access private
	 *
	 * @param string $name     block名称
	 * @param string $content  模板内容
	 *
	 * @return string
	 */
	private function parseBlock($name, $content){
		$this->block[$name] = $content;

		return '';
	}

	/**
	 * 替换继承模板中的block标签
	 * @access private
	 *
	 * @param string $name     block名称
	 * @param string $content  模板内容
	 *
	 * @return string
	 */
	private function replaceBlock($name, $content){
		// 替换block标签 没有重新定义则使用原来的
		$replace = isset($this->block[$name])? $this->block[$name] : $content;

		return stripslashes($replace);
	}

	/**
	 * 搜索模板页面中包含的TagLib库
	 * 并返回列表
	 * @access public
	 *
	 * @param string $content  模板内容
	 *
	 * @return string|false
	 */
	public function getIncludeTagLib(& $content){
		//搜索是否有TagLib标签
		$find = preg_match('/' . $this->config['taglib_begin'] . 'taglib\s(.+?)(\s*?)\/' . $this->config['taglib_end'] .
						   '\W/is',
						   $content,
						   $matches
		);
		if($find){
			//替换TagLib标签
			$content = str_replace($matches[0], '', $content);
			//解析TagLib标签
			$array        = $this->parseXmlAttrs($matches[1]);
			$this->tagLib = explode(',', $array['name']);
		}

		return;
	}

	/**
	 * 解析指定的所有taglib库
	 *
	 * @param $tagLibs
	 * @param $content
	 *
	 * @return void
	 */
	public function parseTagLibAll($tagLibs, &$content){
		do{
			$count = 0;
			$cnt   = 0;
			foreach($tagLibs as $tag){
				$this->parseTagLib($tag, $content, true, $cnt);
				$count += $cnt;
			}
		} while($count);
	}

	/**
	 * TagLib库解析
	 * @access public
	 *
	 * @param string $tagLib   要解析的标签库
	 * @param string $content  要解析的模板内容
	 * @param bool   $hide     是否隐藏标签库前缀
	 * @param int    $count    替换了多少个标签
	 *
	 * @return string
	 */
	public function parseTagLib($tagLib, &$content, $hide, &$count){
		$begin = $this->config['taglib_begin'];
		$end   = $this->config['taglib_end'];
		$tLib  = ThinkInstance::TagLib($tagLib);
		$count = 0;
		foreach($tLib->getTags() as $name => $val){
			$tags = array($name);
			if(isset($val['alias'])){ // 别名设置
				$tags   = explode(',', $val['alias']);
				$tags[] = $name;
			}
			$level    = isset($val['level'])? $val['level'] : 1;
			$closeTag = isset($val['close'])? $val['close'] : true;
			foreach($tags as $tag){
				$parseTag = !$hide? $tagLib . ':' . $tag : $tag; // 实际要解析的标签名称
				if(!method_exists($tLib, '_' . $tag)){
					// 别名可以无需定义解析方法
					$tag = $name;
				}
				$n1 = '\s?([^' . $end . ']*)';
				if(!$closeTag){
					$patterns = '/' . $begin . $parseTag . $n1 . '\/' . $end . '/is';
				} else{
					$patterns =
							'/' . $begin . $parseTag . $n1 . $end . '(.*?)' . $begin . '\/' . $parseTag . $end . '/is';
				}
				for($i = 0; $i < $level; $i++){
					if(!preg_match_all($patterns, $content, $mats)){
						continue;
					}
					for($i = 0, $c = count($mats[1]); $i < $c; $i++){
						if(!isset($mats[2][$i])){
							$mats[2][$i] = '';
						}
						$ret = $this->parseXmlTag($tagLib, $tag, $mats[1][$i], $mats[2][$i]);
						if($ret === false){
							continue;
						}
						$content = str_replace($mats[0][$i], $ret, $content, $cnt);
						$count += $cnt;
					}
					// 模板html标签匹配循环
				}
				// 递归深度循环
			}
			//别名循环
		}
		// taglib->tag循环
	}

	/**
	 * 解析标签库的标签
	 * 需要调用对应的标签库文件解析类
	 * @access public
	 *
	 * @param string $tagLib   标签库名称
	 * @param string $tag      标签名
	 * @param string $attr     标签属性
	 * @param string $content  标签内容
	 *
	 * @return string|false
	 */
	public function parseXmlTag($tagLib, $tag, $attr, $content){
		/*$attr    = stripslashes($attr);
		$content = stripslashes($content);*/

		$tLib    = ThinkInstance::TagLib($tagLib);
		$parse   = '_' . $tag;
		$content = ltrim($content, "\n");
		$content = rtrim($content, "\t");
		if(\COM\MyThink\Strings::isEndWith($content, "\n")){
			$content = substr($content, 0, -1);
		}

		return $tLib->$parse($attr, $content);
	}

	/**
	 * 模板标签解析
	 * 格式： {TagName:args [|content] }
	 * @access public
	 *
	 * @param string $tagStr 标签内容
	 *
	 * @return string
	 */
	public function parseTag($tagStr){
		$parseStr = stripslashes($tagStr);
		//还原非模板标签
		if(preg_match('/^[\s|\d]/is', $parseStr)) //过滤空格和数字打头的标签
		{
			return TMPL_L_DELIM . $parseStr . TMPL_R_DELIM;
		}
		$flag  = substr($parseStr, 0, 1);
		$flag2 = substr($parseStr, 1, 1);
		$name  = substr($parseStr, 1);
		if('$' == $flag && '.' != $flag2 && '(' != $flag2){ //解析模板变量 格式 {$varName}
			return $this->parseVar($name);
		} elseif(':' == $flag){ // 输出某个函数的结果
			return '<?php echo ' . $name . ';?>';
		} elseif('~' == $flag){ // 执行某个函数
			return '<?php ' . $name . ';?>';
		} elseif(substr($parseStr, 0, 2) == '//' || (substr($parseStr, 0, 2) == '/*' && substr($parseStr, -2) == '*/')){
			//注释标签
			return '';
		} else{
			// 未识别的标签直接返回
			return TMPL_L_DELIM . $tagStr . TMPL_R_DELIM;
		}
	}

	/**
	 * 模板变量解析,支持使用函数
	 * 格式： {$varname|function1|function2=arg1,arg2}
	 * @access public
	 *
	 * @param string $varStr 变量数据
	 * @param bool   $echo   生成代标签的php代码
	 *
	 * @return string
	 */
	public function parseVar($varStr, $echo = true){
		$varStr = trim($varStr);
		static $_varParseList = array();
		//如果已经解析过该变量字串，则直接返回变量值
		if(isset($_varParseList[$varStr . $echo])){
			return $_varParseList[$varStr . $echo];
		}
		$parseStr = 'null';

		$varExists = true;
		if(!empty($varStr)){
			$castArr  = explode(':', $varStr);
			$varArray = explode('|', $castArr[0]);
			//取得变量名称
			$var = array_shift($varArray);
			if('__' == substr($var, 0, 2)){
				// 所有以Think.打头的以特殊变量对待 无需模板赋值就可以输出
				$name = parseGlobalVar($var);
			} elseif(false !== strpos($var, '.')){
				//支持 {$var.property}
				$vars = explode('.', $var);
				$var  = array_shift($vars);
				$name = '$' . $var;
				foreach($vars as $key => $val){
					$name .= '[\'' . $val . '\']';
				}
			} elseif(false !== strpos($var, '[')){
				//支持 {$var['key']} 方式输出数组
				$name = "$" . $var;
				preg_match('/(.+?)\[(.+?)\]/is', $var, $match);
				$var = $match[1];
			} else{
				$name = "\$$var";
			}
			//对变量使用函数
			if(count($varArray) > 0){
				$name = $this->parseVarFunction($name, $varArray);
			}
			// 对变量进行转换
			$parse1 = '';
			$parse2 = '';
			if(count($castArr) > 1){
				switch(strtolower(trim($castArr[1]))){
				case 'json':
					$parse1 = 'json_encode(';
					$parse2 = TMPL_READABLE? ', JSON_PRETTY_PRINT)' : ')';
					break;
				default:
					Think::halt('未知的转换选项： ' . $castArr[1]);
				}
			}
			$parseStr = '(' . $parse1 . $name . $parse2 . ')';
		}
		$_varParseList[$varStr] = $parseStr;
		if($echo){
			return '<?php echo ' . $parseStr . '; ?>';
		} else{
			return $parseStr;
		}
	}

	/**
	 * 对模板变量使用函数
	 * 格式 {$varname|function1|function2=arg1,arg2}
	 * @access public
	 *
	 * @param string $name      变量名
	 * @param array  $varArray  函数列表
	 *
	 * @return string
	 */
	public function parseVarFunction($name, $varArray){
		//对变量使用函数
		$length = count($varArray);
		//取得模板禁止使用函数列表
		$template_deny_funs = explode(',', TMPL_DENY_FUNC_LIST);
		for($i = 0; $i < $length; $i++){
			$args = explode('=', $varArray[$i], 2);
			//模板函数过滤
			$fun = strtolower(trim($args[0]));
			switch($fun){
			case 'default': // 特殊模板函数
				$name = '(' . $name . ')?(' . $name . '):' . $args[1];
				break;
			default: // 通用模板函数
				if(!in_array($fun, $template_deny_funs)){
					if(isset($args[1])){
						if(strstr($args[1], '###')){
							$args[1] = str_replace('###', $name, $args[1]);
							$name    = "$fun($args[1])";
						} else{
							$name = "$fun($name,$args[1])";
						}
					} else if(!empty($args[0])){
						$name = "$fun($name)";
					}
				}
			}
		}

		return $name;
	}

	/**
	 * 加载公共模板并缓存 和当前模板在同一路径，否则使用相对路径
	 * @access private
	 *
	 * @param string $tmplPublicName  公共模板文件名
	 * @param array  $vars            要传递的变量列表
	 *
	 * @return string
	 */
	private function parseIncludeItem($tmplPublicName, $vars = array()){
		// 分析模板文件名并读取内容
		$parseStr = $this->parseTemplateName($tmplPublicName);
		// 替换变量
		foreach($vars as $key => $val){
			$parseStr = str_replace('[' . $key . ']', $val, $parseStr);
		}

		// 再次对包含文件进行模板分析
		return $this->parseInclude($parseStr);
	}

	/**
	 * 分析加载的模板文件并读取内容 支持多个模板文件读取
	 * @access private
	 *
	 * @param string $tmplPublicName  模板文件名
	 *
	 * @return string
	 */
	private function parseTemplateName($templateName){
		$array    = explode(',', $templateName);
		$parseStr = '';
		foreach($array as $templateName){
			$parseStr .= "\n" . file_get_contents(locateTemplate($templateName));
		}

		return $parseStr;
	}
}
