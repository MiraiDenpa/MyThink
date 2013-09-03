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
 * ThinkPHP标签库TagLib解析基类
 * @category    Think
 * @package     Think
 * @subpackage  Template
 * @author      liu21st <liu21st@gmail.com>
 */
class TagLib{

	/**
	 * 标签库定义XML文件
	 * @var string
	 * @access protected
	 */
	protected $xml = '';
	protected $tags = array(); // 标签定义
	/**
	 * 标签库名称
	 * @var string
	 * @access protected
	 */
	protected $tagLib = '';

	/**
	 * 标签库标签列表
	 * @var string
	 * @access protected
	 */
	protected $tagList = array();

	/**
	 * 标签库分析数组
	 * @var string
	 * @access protected
	 */
	protected $parse = array();

	/**
	 * 标签库是否有效
	 * @var string
	 * @access protected
	 */
	protected $valid = false;

	/**
	 * 当前模板对象
	 * @var object
	 * @access protected
	 */
	protected $tpl;

	protected $comparison = array(
		' nheq ' => ' !== ',
		' heq '  => ' === ',
		' neq '  => ' != ',
		' eq '   => ' == ',
		' egt '  => ' >= ',
		' gt '   => ' > ',
		' elt '  => ' <= ',
		' lt '   => ' < '
	);

	/**
	 * 架构函数
	 * @access public
	 */
	public function __construct(){
		$this->tagLib = strtolower(substr(get_class($this), 6));
		$this->tpl    = ThinkInstance::ThinkTemplate();
	}

	/**
	 * TagLib标签属性分析 返回标签属性数组
	 * @access public
	 *
	 * @param string $attr 属性字符串
	 * @param string $tag  标签名称
	 *
	 * @return array
	 */
	public function parseXmlAttr($attr, $tag){
		//XML解析安全过滤
		$attr = str_replace('&', '___', $attr);
		$xml  = '<tpl><tag ' . $attr . ' /></tpl>';
		$xml  = simplexml_load_string($xml);
		if(!$xml){
			Think::halt(LANG_XML_TAG_ERROR . '<' . $tag . '/> : ' . $attr);
		}
		$ltag = strtolower($tag);
		$xml  = (array)($xml->tag->attributes());
		if(empty($xml['@attributes'])){
			return [];
		}
		$array = array_change_key_case($xml['@attributes']);
		if($this->tags[$ltag]['attr']){
			$attrs = explode(',', $this->tags[$ltag]['attr']);
		} else{
			$attrs = [];
		}
		if(isset($this->tags[$ltag]['must'])){
			$must = explode(',', $this->tags[$ltag]['must']);
		} else{
			$must = array();
		}
		foreach($attrs as $name){
			if(isset($array[$name])){
				$array[$name] = str_replace('___', '&', $array[$name]);
			} elseif(false !== array_search($name, $must)){
				Think::halt(LANG_PARAM_ERROR . '<' . $tag . '/> :' . $name);
			}
		}

		return $array;
	}

	/**
	 * 解析条件表达式
	 * @access public
	 *
	 * @param string $condition 表达式标签内容
	 *
	 * @return array
	 */
	public function parseCondition($condition){
		$condition = str_ireplace(array_keys($this->comparison), array_values($this->comparison), $condition);
		$condition = preg_replace('/\$(\w+):(\w+)\s/is', '$\\1->\\2 ', $condition);
		switch(strtolower(TMPL_VAR_IDENTIFY)){
		case 'array': // 识别为数组
			$condition = preg_replace('/\$(\w+)\.(\w+)\s/is', '$\\1["\\2"] ', $condition);
			break;
		case 'obj': // 识别为对象
			$condition = preg_replace('/\$(\w+)\.(\w+)\s/is', '$\\1->\\2 ', $condition);
			break;
		default: // 自动判断数组或对象 只支持二维
			$condition = preg_replace('/\$(\w+)\.(\w+)\s/is', '(is_array($\\1)?$\\1[\'\\2\']:$\\1->\\2) ', $condition);
		}
		if(false !== strpos($condition, '$__')){
			$condition = preg_replace_callback('/(\$__*?)\s/is', 'parseGlobalVar', $condition);
		}

		return $condition;
	}

	/**
	 * 自动识别构建变量
	 * @access public
	 *
	 * @param string $name 变量描述
	 *
	 * @return string
	 */
	public function autoBuildVar($name){
		if('__' == substr($name, 0, 2)){
			// 特殊变量
			return parseGlobalVar($name);
		} elseif(strpos($name, '.')){
			$vars = explode('.', $name);
			$var  = array_shift($vars);
			switch(strtolower(TMPL_VAR_IDENTIFY)){
			case 'array': // 识别为数组
				$name = '$' . $var;
				foreach($vars as $key => $val){
					if(0 === strpos($val, '$')){
						$name .= '[' . $val . ']';
					} else{
						$name .= '[' . var_export($val, true) . ']';
					}
				}
				break;
			case 'obj': // 识别为对象
				$name = '$' . $var;
				foreach($vars as $key => $val){
					$name .= '->' . $val;
				}
				break;
			default: // 自动判断数组或对象 只支持二维
				if(0 === strpos($vars[0], '$')){
					$name .= '[' . $vars[0] . ']';
				} else{
					$name .= '[' . var_export($vars[0], true) . ']';
				}
				$name = 'is_array($' . $var . ')?$' . $var . '[' . $name . ']:$' . $var . '->' . $vars[0];
			}
		} elseif(!defined($name)){
			$name = '$' . $name;
		}

		return $name;
	}

	/**
	 * 用于标签属性里面的特殊模板变量解析
	 * 格式 以 Think. 打头的变量属于特殊模板变量
	 * @access public
	 *
	 * @param string $varStr  变量字符串
	 *
	 * @return string
	 */
	public function parseGlobalVar($varStr){
		$vars     = explode('.', substr($varStr, 2));
		$vars[0]  = strtoupper(trim($vars[0]));
		$parseStr = '';
		if(count($vars) >= 1){
			$vars[1] = trim($vars[1]);
			switch($vars[0]){
			case 'C':
				$parseStr = strtoupper($vars[1]);
				break;
			case 'L':
				$parseStr = 'LANG_' . strtoupper($vars[1]);
				break;
			default:
				trigger_error('未知全局变量名：' . $vars[0], E_USER_NOTICE);
			}
		} else if(count($vars) == 1){
			switch($vars[0]){
			case 'NOW':
				$parseStr = "date('Y-m-d g:i a')";
				break;
			case 'DLEFT':
				$parseStr = 'TMPL_L_DELIM';
				break;
			case 'DRIGHT':
				$parseStr = 'TMPL_R_DELIM';
				break;
			default:
				if(\COM\MyThink\Strings::isEndWith($vars[0], '__')){
					return strtoupper(trim($vars[0], '_'));
				}
				trigger_error('未知全局变量名：' . $vars[0], E_USER_NOTICE);
			}
		}

		return $parseStr;
	}

	// 获取标签定义
	public function getTags(){
		return $this->tags;
	}
}
