<?php
/**
 * 提供<header:send />标签
 * 将内容送进头部
 */
class TagLibHeader extends TagLib{
	protected $prepend = '';
	protected $append = '';

	protected $tags = array(
		'AppendHeader'  => array(
			'attr'  => '',
			'must'  => '',
			'close' => 1
		),
		'PrependHeader' => array(
			'attr'  => '',
			'must'  => '',
			'close' => 1
		),
		'BrowserLib'    => array(
			'attr'  => '',
			'must'  => '',
			'close' => 1
		),
	);

	/**
	 * 在标准头部前方添加
	 * 用来添加css
	 *
	 * @param $attr    空
	 * @param $content 要添加的内容
	 *
	 * @return void
	 */
	public function _PrependHeader($attr, $content){
		$this->prepend .= $content . "\n";
	}

	/**
	 * 在标准头部后方添加
	 * 用于js
	 *
	 * @param $attr    空
	 * @param $content 要添加的内容
	 *
	 * @return void
	 */
	public function _AppendHeader($attr, $content){
		$this->append .= $content . "\n";
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
		if($content){
			$this->_PrependHeader('', trim($content));
		}

		$content = HTML::importFile(searchPublic($js));
		if($content){
			$this->_AppendHeader('', trim($content));
		}
	}

	/**
	 * 用来获得头部的输出函数，只用于CompileHeader函数
	 * FIXME 需要解耦合
	 *
	 * @return string
	 */
	public function getHeader(){
		$content = \COM\MyThink\Strings::tabMultiline(str_replace('<!--/-->', $this->prepend, TagLibHeader::StandardHeader()) .
													  $this->append);
		if(STATIC_DEBUG){
			return trim($content);
		} else{
			return trim($this->concatStatic($content));
		}
	}

	/**
	 * 返回标准头部
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public static function StandardHeader(){
		$head = '';
		$head .= HTML::importFile(searchPublic('bootstrap.css'));
		$head .= HTML::css(PUBLIC_URL . '/artDialog/skins/<?php echo art_skin();?>.css');
		$head .= '<!--/-->';
		$head .= HTML::importFile(searchPublic('jquery.js'));
		$head .= HTML::importFile(searchPublic('bootstrap.js'));
		$head .= HTML::importFile(searchPublic('jquery.artDialog.js'));
		$head .= HTML::importFile(searchPublic('artDialog.plugins.js'));
		if(APP_DEBUG){
			$head .= HTML::importFile(searchPublic(['debugless.js', 'less.js']));
		}

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
