<?php
use COM\MyThink\Strings;

/**
 * 输出html标签
 */
class HTML{
	public static function importFile($file){
		if(is_array($file)){
			$headers = array();
			foreach($file as $item){
				$headers[] = self::importFile($item);
			}

			return implode_r("\n", $headers);
		}
		$url = PathToUrl($file);
		if(Strings::isEndWith($file, '.min.css')){
			return self::css($url);
		} else if(Strings::isEndWith($file, '.css')){
			if(!APP_DEBUG){
				$url = Strings::endWith(Strings::blocktrim($url, '.css'), '.min.css');
			}

			return self::css($url);
		} else if(Strings::isEndWith($file, '.less')){
			if(!APP_DEBUG){
				$url = Strings::endWith(Strings::blocktrim($url, '.less'), '.css');
			}

			return self::css($url);
		} else if(Strings::isEndWith($file, '.min.js')){
			return self::script($url);
		} else if(Strings::isEndWith($file, '.js')){
			if(!APP_DEBUG){
				$url = Strings::endWith(Strings::blocktrim($url, '.js'), '.min.js');
			}

			return self::script($url);
		} else{
			Think::halt(LANG_UNKNOWN_EXTENSION . ': ' . $file);
		}
	}

	public static function css($url){
		return '<link href="' . $url . '" rel="stylesheet" type="text/css">';
	}

	public static function script($url){
		return '<script src="' . $url . '" type="text/javascript" charset="UTF-8"></script>';
	}

	/**
	 * NOTE: $type需要根据bootstrap更新
	 *
	 * @param        $content
	 * @param string $type
	 * @param string $attr
	 *
	 * @return string
	 * @static
	 */
	public static function label($content, $type = 'default', $attr = ''){
		if(is_array($attr)){
			$attr = dbl_implode(' ', '=', array_map(function ($item){
				return '"'.htmlentities($item).'"';
			}, $attr));
		}
		if($attr){
			$attr = ' ' . $attr;
		}

		return '<span class="label label-' . $type . '"' . $attr . '>' . $content . '</span>';
	}
}

