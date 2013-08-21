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

	public function __construct(){
		parent::__construct();
	}

	public function _PrependHeader($attr, $content){
		$this->prepend .= $content."\n";
	}

	public function _AppendHeader($attr, $content){
		$this->append .= $content."\n";
	}

	public function _BrowserLib($attr, $content){
		$arr = explode("\n", $content);
		$css = $js = [];
		foreach($arr as $file){
			if(\COM\MyThink\Strings::isEndWith($file, '.js')){
				$js[] = $file;
			} else{
				$css[] = $file;
			}
		}

		$content = HTML::importFile(searchPublic($css));
		if($content)$this->_PrependHeader('',$content);

		$content = HTML::importFile(searchPublic($js));
		if($content)$this->_AppendHeader('',$content);
	}

	public function getHeader(){
		return \COM\MyThink\Strings::tabMultiline($this->prepend . StandardHeader() . $this->append);
	}
}
