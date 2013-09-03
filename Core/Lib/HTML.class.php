<?php
use COM\MyThink\Strings;

/**
 * 输出html标签
 */
class HTML{
	protected static $plathead = [];
	protected static $readycode = '';

	/**
	 *
	 * 如果有HTML类的函数需要添加到head，由这个函数完成
	 */
	public static function getExtraHeader($type){
		$head = '';
		if(!empty(self::$plathead[$type])){
			$head = self::$plathead[$type];
		}
		self::$plathead[$type] = '';
		if($type == 'script' && self::$readycode){
			$head .= '<script type="text/javascript" wrap="ready">' . self::$readycode . "\n</script>";
			self::$readycode = '';
		}
		return $head;
	}

	public static function ReadyCode($code){
		self::$readycode .= "\n" . $code;
	}

	/**
	 * 导入一个或多个js/css文件
	 *
	 * @param string|array $file
	 *
	 * @return string
	 * @static
	 */
	public static function importFile($file){
		static $cache = [];
		if(is_array($file)){
			$headers = array();
			foreach($file as $item){
				$headers[] = self::importFile($item);
			}

			return implode_r("\n", array_filter($headers));
		}
		if(isset($cache[$file])){
			return '';
		}
		$cache[$file] = true;
		$url          = PathToUrl($file);
		if(Strings::isEndWith($file, '.css')){
			return self::css($url);
		} else if(Strings::isEndWith($file, '.less')){
			if(!LESS_DEBUG){
				$url = Strings::endWith(Strings::blocktrim($url, '.less'), '.css');
			}
			return self::css($url);
		} else if(Strings::isEndWith($file, '.js')){
			return self::script($url);
		} else{
			Think::halt(LANG_UNKNOWN_EXTENSION . ': ' . $file);
			return '';
		}
	}

	/**
	 * 创建一个style标签
	 *
	 * @param $url
	 *
	 * @return string
	 * @static
	 */
	public static function css($url){
		// href 必须在前，因为TagLibHreader依赖这个特性
		if(Strings::isEndWith($url, '.less')){
			return '<link href="' . $url . '" rel="stylesheet" type="text/less"/>';
		} else{
			return '<link href="' . $url . '" rel="stylesheet" type="text/css"/>';
		}
	}

	/**
	 * 创建一个script标签
	 *
	 * @param $url
	 *
	 * @return string
	 * @static
	 */
	public static function script($url){
		// src 必须在前，因为TagLibHreader依赖这个特性
		return '<script src="' . $url . '" type="text/javascript" charset="UTF-8"></script>';
	}

	/**
	 * 创建<a>标签
	 *
	 * @param string       $url
	 * @param string       $name
	 * @param string|array $attr
	 *
	 * @return string
	 * @static
	 */
	public static function anchor($url, $name, $attr = ''){
		if(is_array($attr)){
			$attr = self::attr($attr);
		}
		return '<a href="' . $url . '"' . $attr . '>' . $name . '</a>';
	}

	/**
	 * 创建一个文本标签
	 * NOTE: $type需要根据bootstrap更新
	 *
	 * @param        $content
	 * @param string $type
	 * @param string $attr
	 *
	 * @return string
	 * @static
	 */
	public static function smlabel($content, $type = 'default', $attr = ''){
		if(is_array($attr)){
			$attr = self::attr($attr);
		}
		return '<span class="label label-' . $type . '"' . $attr . '>' . $content . '</span>';
	}

	/**
	 * 创建表单
	 *
	 * @param string $fdml   表单描述
	 * @param string $parser 样式
	 * @param array  $attr   form属性
	 *
	 * @return mixed
	 * @static
	 */
	public static function form($fdml, $parser, $attr){
		if(is_array($attr)){
			if(!$attr['id']){
				Think::halt('HTML::form 没有指定ID。');
			}
			$formId = $attr['id'];
			$attr   = self::attr($attr);
		} else{
			if(!preg_match('#id="(.*?)"#', $attr, $mats)){
				Think::halt('HTML::form 没有指定ID。');
			}
			$formId = $mats[1];
		}
		self::$plathead['script'] .= self::importFile(searchPublic(['jquery.validate.js', 'jquery.validate.zh.js']));
		$ret  = require(THINK_PATH . 'Tpl/html/form.php');
		$html = '<form ' . ($attr) . ">\n" . $ret . "\n</form>";
		return $html;
	}

	/**
	 * 创建输入框
	 *
	 * @param string $type    类型
	 * @param string $default 默认填写值
	 * @param array  $attrs   属性数组
	 *
	 * @return string
	 * @static
	 */
	public static function input($type, $default, array $attrs){
		if(isset($attrs['_text'])){
			$txt = ' '.$attrs['_text'];
			unset($attrs['_text']);
		} else{
			$txt = '';
		}
		switch($type){
		case 'rich':
			$attrs['content'] = $default;
			$attrs['_text'] = $txt;
			return HTML::richtext($attrs);
		case 'textarea':
			return '<textarea' . self::attr($attrs).$txt . '>' . htmlentities($default) . '</textarea>';
		case 'static':
			return '<p' . self::attr($attrs).$txt . '>' . htmlentities($default) . '</p>';
		case 'submit':
		case 'radio':
		case 'checkbox':
		default:
			$attrs['type']  = $type;
			$attrs['value'] = $default;
			return '<input' . self::attr($attrs).$txt . '/>';
		}
	}

	/**
	 * 生成<label for="xxx"></label>
	 *
	 * @param string $for
	 * @param string $content
	 * @param array  $attr
	 *
	 * @return string
	 * @static
	 */
	public static function label($for, $content, $attr = []){
		return '<label for="' . $for . '"' . (is_array($attr)? self::attr($attr) : $attr) . '>' . $content . '</label>';
	}

	/**
	 * 生成类似 id="abc" name="efg" 这样的属性列表
	 * 最前面有空格，后面没有
	 *
	 * @param array $attr
	 *
	 * @return string
	 * @static
	 */
	public static function attr(array $attr){
		$ret = '';
		foreach($attr as $k => $v){
			$ret .= ' ' . $k . '="' . addslashes($v) . '"';
		}
		return $ret;
	}
}


