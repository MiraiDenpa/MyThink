<?php
/**
 * 提供<header:send />标签
 * 将内容送进头部
 */
class TagLibHeader extends TagLib{
	protected $super_head = '';
	protected $prepend = '';
	protected $append = '';

	protected $tags = array(
		'addheader'  => [
			'attr'  => 'browserlib',
			'must'  => '',
			'close' => 1
		],
		'browserlib' => [
			'attr'  => '',
			'must'  => '',
			'close' => 1
		],
	);

	/**
	 * 在标准头添加内容
	 * 解析 script style meta link base
	 * 其余的标签原样返回
	 *
	 * @param $attr    空
	 * @param $content 要添加的内容
	 *
	 * @return string
	 */
	public function _AddHeader($attr, $content){
		$tag = $this->parseXmlAttr($attr, 'AddHeader');
		if(isset($tag['browserlib'])){
			$this->_BrowserLib('', str_replace(',', "\n", $tag['browserlib']));
		}
		preg_match_all('#<(script|style)[^>]*>.*</\1>#is', $content, $mats);
		foreach($mats[1] as $id => $type){
			if($type == 'script'){
				$this->append .= $mats[0][$id];
			} else{
				$this->prepend .= $mats[0][$id];
			}
			$content = str_replace($mats[0][$id], '', $content);
		}
		preg_match_all('#<(meta|base)[^>]*/?>(</\1>)?#is', $content, $mats);
		foreach($mats[0] as $tag){
			$this->super_head .= $tag;
			$content = str_replace($tag, '', $content);
		}
		preg_match_all('#<(link)[^>]*/?>(</\1>)?#is', $content, $mats);
		foreach($mats[0] as $tag){
			$this->prepend .= $tag;
			$content = str_replace($tag, '', $content);
		}

		return trim($content);
	}

	/**
	 * 添加外部库
	 * 每行指定一个文件，这个文件名必须在PUBLIC_PATH中存在，且制定的文件名唯一（用find查询）
	 * 自动区分js和css，非调试模式使用min版本
	 *
	 * @param $attr    空
	 * @param $content 要添加的文件列表
	 *
	 * @return void
	 */
	public function _BrowserLib($attr, $content){
		$arr = explode("\n", $content);
		$css = $js = [];
		foreach($arr as $file){
			$file = trim($file);
			if(empty($file)){
				continue;
			}
			if(\COM\MyThink\Strings::isEndWith($file, '.js')){
				$js[] = $file;
			} else{
				$css[] = $file;
			}
		}

		$content = HTML::importFile(searchPublic($css));
		$content .= HTML::importFile(searchPublic($js));
		if(trim($content)){
			$ret = $this->_AddHeader('', trim($content));
			if($ret){
				Think::halt('无法添加头部：多余内容' . dump_some($ret));
			}
		}
	}

	/**
	 * 用来获得头部的输出函数，只用于CompileHeader函数
	 * FIXME 需要解耦合
	 *
	 * @param $type
	 *  script | style
	 *
	 * @return string
	 */
	public function getHeader($type){
		$content = TagLibHeader::StandardHeader($type);

		if($this->super_head){
			$content = $this->super_head."\n<!-- SuperHead -->\n".$content;
			$this->super_head = '';
		}
		
		if($type == 'script'){
			$ret          = trim($content) . $this->append;
			$this->append = '';
		} else{
			$ret           = trim($content) . $this->prepend;
			$this->prepend = '';
		}
		if(!STATIC_DEBUG){
			return trim($this->concatStatic($content));
		}
		return $ret;
	}

	/**
	 * 返回标准头部
	 *
	 * @param $type
	 * sctipt | style
	 *
	 * @return string
	 */
	protected static function StandardHeader($type){
		static $send = [];
		$head = '';
		if(!isset($send[$type])){
			$send[$type] = 1;
			if($type == 'style'){
				$head .= HTML::importFile(searchPublic('bootstrap.css'));
				$head .= HTML::css(PUBLIC_URL . '/artDialog/skins/<?php echo art_skin();?>.css');
				$head .= HTML::importFile(searchPublic('global.less'));
				$head .= "\n<!-- Standard Header CSS -->";
			} else{
				$head .= HTML::importFile(searchPublic(['global.js', 'phpjs.js', 'basevar.js', 'jquery.js']));
				$head .= HTML::importFile(searchPublic(['bootstrap.js', 'jquery.artDialog.js']));
				$head .= HTML::importFile(searchPublic('artDialog.plugins.js'));
				if(APP_DEBUG){
					$head .= HTML::importFile(searchPublic(['less.js']));
				}
				$head .= "\n<!-- Standard Header JS -->";
			}
		}
		$head .= HTML::getExtraHeader($type);

		return $head;
	}

	/**
	 * 分析头部，把所有包含PUBLIC_URL的静态文件合并，然后在第一个引入的文件处重新插入
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	private function concatStatic($content){
		$PUB_URL = preg_quote(PUBLIC_URL);
		$hash    = md5(time());
		$js      = $css = '';

		// js 部分
		if(preg_match_all('#<script src="(PUBLIC_URL.*?)"[^>]*?></script>#', $content, $mats)){
			$brand1 = [];
			$brand2 = [];
			$insert = false;
			foreach($mats[1] as $i => $url){
				if($insert){
					$content = str_replace($mats[0][$i], '', $content);
				} else{
					$insert  = true;
					$content = str_replace($mats[0][$i], 'JS' . $hash, $content);
				}

				if(preg_match('#jquery|bootstrap|artdialog|createjs|jslib#i', $url) > 0){
					$brand1[] = str_replace('PUBLIC_URL/', '', $url);
				} else{
					$brand2[] = str_replace('PUBLIC_URL/', '', $url);
				}
			}

			$js = HTML::script(PUBLIC_URL . '/??' . (implode(',', $brand1)) . '?v=' . STATIC_VERSION);
			$js .= HTML::script(PUBLIC_URL . '/??' . (implode(',', $brand2)) . '?v=' . STATIC_VERSION);
		}

		return str_replace(['JS' . $hash, 'CSS' . $hash], [$js, $css], $content);
	}
}
