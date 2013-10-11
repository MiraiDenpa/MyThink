<?php
/**
 * 提供<header:send />标签
 * 将内容送进头部
 */
class TagLibHeader extends TagLib{
	protected $define = '';

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

	/** */
	public function __construct(){
		parent::__construct();
		$this->define            = require PUBLIC_PATH . 'BrowserLib.php';
		$files                   = $this->parseDependence($this->define['globals']);
		$this->define['globals'] = HTML::importFile($files);
	}

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
		$is_prepend = isset($tag['prepend']);
		$super_head = $append = $prepend = '';
		preg_match_all('#<(script|style)[^>]*>.*?</\1>#is', $content, $mats);
		foreach($mats[1] as $id => $type){
			if($type == 'script'){
				$append .= $mats[0][$id];
			} else{
				$prepend .= $mats[0][$id];
			}
			$content = str_replace($mats[0][$id], '', $content);
		}
		preg_match_all('#<(meta|base)[^>]*/?>(</\1>)?#is', $content, $mats);
		foreach($mats[0] as $tag){
			$super_head .= $tag;
			$content = str_replace($tag, '', $content);
		}
		preg_match_all('#<(link)[^>]*/?>(</\1>)?#is', $content, $mats);
		foreach($mats[0] as $tag){
			$prepend .= $tag;
			$content = str_replace($tag, '', $content);
		}

		if($is_prepend){
			$this->prepend    = $prepend . $this->prepend;
			$this->append     = $append . $this->append;
			$this->super_head = $super_head . $this->super_head;
		} else{
			$this->prepend .= $prepend;
			$this->append .= $append;
			$this->super_head .= $super_head;
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
	 * @return string
	 */
	public function _BrowserLib($attr, $content){
		if(!$content){
			return '';
		}
		$arr     = explode("\n", $content);
		$arr     = array_map('trim', $arr);
		$files   = $this->parseDependence($arr);
		$content = HTML::importFile($files);

		if(trim($content)){
			$ret = $this->_AddHeader('prepenad="true"', trim($content));
			if($ret){
				Think::halt('无法添加头部：多余内容' . dump_some($ret));
			}
		}
		return '';
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
		$content = HTML::getExtraHeader($type);

		static $hsend = false;
		if(!$hsend){
			$this->_AddHeader('prepend="true"', $this->define['globals']);
			$hsend = true;
		}

		if($this->super_head){
			$content          = $this->super_head . "\n<!-- SuperHead -->\n" . $content;
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
			return trim($this->concatStatic($ret));
		} else{
			return str_replace(['><', "\n\t</"], [">\n\t<", '</'], $ret);
		}
	}

	/**
	 * 分析头部，把所有包含PUBLIC_URL的静态文件合并，然后在第一个引入的文件处重新插入
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	private function concatStatic($content){
		$content = str_replace(PUBLIC_URL, 'PUBLIC_URL', $content);
		// js 部分
		if(preg_match_all('#<script src="PUBLIC_URL(.*?)"[^>]*?></script>#', $content, $mats)){
			foreach($mats[1] as $index => $file){
				$script  = HTML::script(PUBLIC_URL . '/getjs/' . pubfile_guid(trim($file, '/')) . '.js?_=' .
										STATIC_VERSION
				);
				$content = str_replace($mats[0][$index], $script, $content);
			}
		}
		// css 部分
		if(preg_match_all('#<link href="PUBLIC_URL(.*?)"[^>]*?>()#', $content, $mats)){
			foreach($mats[1] as $index => $file){
				$script  = HTML::css(PUBLIC_URL . '/getcss/' . pubfile_guid(trim($file, '/')) . '.css?_=' .
									 STATIC_VERSION
				);
				$content = str_replace($mats[0][$index], $script, $content);
			}
		}

		return $content;
		//FIXME 需要更聪明的连接方式
		$script  = '';
		$content = str_replace(PUBLIC_URL, 'PUBLIC_URL', $content);

		// js 部分
		if(preg_match_all('#<script src="PUBLIC_URL(.*?)"[^>]*?></script>()#', $content, $mats)){
			$concat = [];
			foreach($mats[1] as $file){
				$file     = trim($file, '/');
				$guid     = pubfile_guid($file);
				$concat[] = $guid . '.js';
			}
			$content = str_replace($mats[0], $mats[2], $content);
			$script  = HTML::script(PUBLIC_URL . '/getjs/??' . (implode(',', $concat)) . '?v=' . STATIC_VERSION);
		}
		// css 部分
		if(preg_match_all('#<link href="PUBLIC_URL(.*?)"[^>]*?>()#', $content, $mats)){
			$concat = [];
			foreach($mats[1] as $file){
				$file     = trim($file, '/');
				$guid     = pubfile_guid($file);
				$concat[] = $guid . '.css';
			}
			$content = str_replace($mats[0], $mats[2], $content);
			$script  = HTML::css(PUBLIC_URL . '/getcss/??' . (implode(',', $concat)) . '?v=' . STATIC_VERSION);
		}

		return $script . $content;
	}

	/** */
	protected function parseDependence(array $files){
		$files  = array_values($files);
		$define = & $this->define;
		static $cache = [];
		$key   = 0;
		$count = count($files);
		for(; $key < $count; $key++){
			$basefile = $files[$key];
			if($basefile{0} === '/'){
				continue;
			}
			if(isset($cache[$basefile])){
				array_splice($files, $key, 1);
				$count = count($files);
				$key--;
				continue;
			}
			$cache[$basefile] = true;
			$found            = false;
			$changed          = false;
			$inject           = [];
			if(isset($define['libraries'][$basefile])){ // 库文件
				$inject  = (array)$define['libraries'][$basefile];
				$changed = $found = true;
			}
			if(isset($define['requirements'][$basefile])){ // 依赖文件
				$inject  = array_merge($inject, (array)$define['requirements'][$basefile]);
				$changed = $found = true;
			}
			if(isset($define['fileset'][$basefile])){ // 名称引入文件
				$inject[] = (string)$define['fileset'][$basefile];
				$found    = true;
			}
			if(isset($define['component'][$basefile])){ // 名称引入文件
				$inject = array_merge($inject, (array)$define['component'][$basefile]);
				$found  = true;
			}
			array_splice($files, $key, 1, $inject);
			$count = count($files);
			if($changed){
				$key--;
			}
			if(!$found){
				Think::halt('无法解析静态依赖：' . $basefile);
			}
		}

		return array_filter(array_map(function ($val){
									if($val{0} == '/'){
										return PUBLIC_URL . $val;
									} else{
										return null;
									}
								},
								$files
							)
		);
	}

	/** */
	protected function parseLibraries($name){
		if(!isset($this->define['libraries'][$name])){
			Think::halt('未定义类库：' . $name);
		}
		$deps = $this->define['libraries'][$name];
		foreach($deps as $depfile){
			$deps += $this->parseDependence($depfile);
		}
		return array_unique($deps);
	}
}
