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
 * CX标签库解析类
 * @category    Think
 * @package     Think
 * @subpackage  Driver.Taglib
 * @author      liu21st <liu21st@gmail.com>
 */
class TagLibCx extends TagLib{

	// 标签定义
	protected $tags = array(
		// 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
		'php'        => array(),
		'volist'     => array(
			'attr'  => 'name,id,key,index,mod',
			'must'  => 'name,id',
			'level' => 3,
			'alias' => ''
		),
		'oncelist'   => array('attr' => 'source,id,key,index,mod', 'must' => 'id,source', 'level' => 3),
		'foreach'    => array('attr' => 'name,item,key', 'level' => 3),
		'if'         => array('attr' => 'condition', 'level' => 2),
		'elseif'     => array('attr' => 'condition', 'close' => 1),
		'else'       => array('attr' => '', 'close' => 1),
		'switch'     => array('attr' => 'name', 'level' => 2),
		'case'       => array('attr' => 'value,break'),
		'default'    => array('attr' => '', 'close' => 0),
		'compare'    => array(
			'attr'  => 'name,value,type',
			'level' => 3,
			'alias' => 'eq,equal,notequal,neq,gt,lt,egt,elt,heq,nheq'
		),
		'range'      => array('attr' => 'name,value,type', 'level' => 3, 'alias' => 'in,notin,between,notbetween'),
		'empty'      => array('attr' => 'name', 'level' => 3),
		'notempty'   => array('attr' => 'name', 'level' => 3),
		'present'    => array('attr' => 'name', 'level' => 3, 'alias' => 'isset'),
		'notpresent' => array('attr' => 'name', 'level' => 3, 'alias' => 'notset'),
		'defined'    => array('attr' => 'name', 'level' => 3),
		'notdefined' => array('attr' => 'name', 'level' => 3),
		'assign'     => array('attr' => 'name,value,source', 'must' => 'name', 'close' => 0),
		'define'     => array('attr' => 'name,value', 'close' => 0),
		'for'        => array('attr' => 'start,end,name,comparison,step', 'level' => 3),
		'url'        => array('attr' => 'app,action,method,params,suffix,protocol', 'close' => 0),
		'script'     => ['attr' => 'type,wrap,globals,declare', 'must' => 'type', 'close' => 1],
		'style'      => ['attr' => 'type', 'must' => 'type', 'close' => 1],
		'link'       => ['attr' => 'rel,href,type', 'must' => 'rel,href,type', 'close' => 0],
		'comment'    => ['attr' => '', 'must' => '', 'close' => 1],
		'iif'        => ['attr' => 'name,then,else,type', 'must' => 'name', 'close' => 0],
		//'formdescript' => ['attr' => 'template', 'must' => 'template', 'close' => 1],
	);

	/**
	 * php标签解析
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _php($attr, $content){
		$parseStr = '<?php ' . $content . ' ?>';

		return $parseStr;
	}

	/**
	 * volist标签解析 循环输出数据集
	 * 格式：
	 * <volist name="userList" id="user" empty="" >
	 * {user.username}
	 * {user.email}
	 * </volist>
	 * 允许使用函数设定数据集 <volist name=":fun('arg')" id="vo">{$vo.name}</volist>
	 *
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string|void
	 */
	public function _volist($attr, $content){
		static $_iterateParseCache = array();
		//如果已经解析过，则直接返回变量值
		$cacheIterateId = md5($attr . $content);
		if(isset($_iterateParseCache[$cacheIterateId])){
			return $_iterateParseCache[$cacheIterateId];
		}
		$tag  = $this->parseXmlAttr($attr, 'volist');
		$name = $tag['name'];
		$id   = $tag['id'];
		if(isset($tag['empty'])){
			$empty = $tag['empty'];
			if($empty{0} == ':'){
				$empty = substr($empty, 1);
			} elseif(strpos($empty, 'LANG_') === 0){
				$empty = substr($empty, 1);
			} elseif($empty{0} != '$'){
				$empty = var_export($empty, true);
			}
		}
		$key      = !empty($tag['key'])? $tag['key'] : 'key';
		$index    = !empty($tag['index'])? $tag['index'] : 'i';
		$mod      = isset($tag['mod'])? $tag['mod'] : null;
		$parseStr = "<?php\n";
		if(0 === strpos($name, ':')){ // 遍历返回值
			$parseStr .= '$_result=' . substr($name, 1) . ";\n";
			$name = '$_result';
		} else{ // 遍历普通数组
			$name = $this->autoBuildVar($name);
		}

		$parseStr .= "\t\${$index} = 0;\n";
		if(isset($empty)){
			$parseStr .= "\tif( count({$name})==0 ):\n";
			$parseStr .= "\t\techo {$empty};\n";
			$parseStr .= "\telse:\n";
		}
		$parseStr .= "\t\tforeach({$name} as \${$key}=>\${$id}):\n";
		if($mod){
			$parseStr .= "\t\t\t\$mod = (\${$index}%{$mod});\n";
		}
		$parseStr .= "\t\t\t++\${$index};\n?>";
		$parseStr .= $this->tpl->parse($content);
		$parseStr .= "\n<?php\n\t\tendforeach;?>\n";
		if(isset($empty)){
			$parseStr .= "<?php endif; ?>\n";
		}
		$_iterateParseCache[$cacheIterateId] = $parseStr;

		if(!empty($parseStr)){
			return $parseStr;
		}

		return '';
	}

	/**
	 * source : 源文件名
	 * id : 循环值
	 * key : 循环变量
	 * index : 递增变量
	 * mod : 取余结果变量
	 */
	public function _oncelist($attr, $content){
		$tag       = $this->parseXmlAttr($attr, 'oncelist');
		$source    = $tag['source'];
		$__id__    = $tag['id'];
		$__key__   = isset($tag['key'])? $tag['key'] : 'key';
		$__index__ = !empty($tag['index'])? $tag['index'] : 'i';
		$modBase   = isset($tag['mod'])? intval($tag['mod']) : 2;

		$_d_a_t_a_ = $this->source($source);

		$__ob__ = new OutputBuffer();
		$__i    = 0;
		foreach($_d_a_t_a_ as $__k => $__v){
			extract(array(
						 $__key__   => $__k,
						 $__id__    => $__v,
						 $__index__ => $__i++,
						 'mod'      => $__i%$modBase,
					));
			eval('?>' . $this->tpl->parse($content));
		}
		$parsestr = $__ob__->get();
		$__ob__   = null;

		return $parsestr;
	}

	/**
	 * foreach标签解析 循环输出数据集
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string|void
	 */
	public function _foreach($attr, $content){
		static $_iterateParseCache = array();
		//如果已经解析过，则直接返回变量值
		$cacheIterateId = md5($attr . $content);
		if(isset($_iterateParseCache[$cacheIterateId])){
			return $_iterateParseCache[$cacheIterateId];
		}
		$tag      = $this->parseXmlAttr($attr, 'foreach');
		$name     = $tag['name'];
		$item     = $tag['item'];
		$key      = !empty($tag['key'])? $tag['key'] : 'key';
		$name     = $this->autoBuildVar($name);
		$parseStr = '<?php if(is_array(' . $name . ')): foreach(' . $name . ' as $' . $key . '=>$' . $item . '): ?>';
		$parseStr .= $this->tpl->parse($content);
		$parseStr .= '<?php endforeach; endif; ?>';
		$_iterateParseCache[$cacheIterateId] = $parseStr;
		if(!empty($parseStr)){
			return $parseStr;
		}

		return '';
	}

	/**
	 * if标签解析
	 * 格式：
	 * <if condition=" $a eq 1" >
	 * <elseif condition="$a eq 2" />
	 * <else />
	 * </if>
	 * 表达式支持 eq neq gt egt lt elt == > >= < <= or and || &&
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _if($attr, $content){
		$tag       = $this->parseXmlAttr($attr, 'if');
		$condition = $this->parseCondition($tag['condition']);
		$parseStr  = '<?php if(' . $condition . '): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**
	 * else标签解析
	 * 格式：见if标签
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _elseif($attr, $content){
		$tag       = $this->parseXmlAttr($attr, 'elseif');
		$condition = $this->parseCondition($tag['condition']);
		$parseStr  = '<?php elseif(' . $condition . '): ?>';
		if($content){
			$parseStr .= "\n" . $content;
		}

		return $parseStr;
	}

	/**
	 * else标签解析
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _else($attr, $content){
		$parseStr = '<?php else: ?>';
		if($content){
			$parseStr .= "\n" . $content;
		}

		return $parseStr;
	}

	/**
	 * switch标签解析
	 * 格式：
	 * <switch name="a.name" >
	 * <case value="1" break="false">1</case>
	 * <case value="2" >2</case>
	 * <default />other
	 * </switch>
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _switch($attr, $content){
		$tag      = $this->parseXmlAttr($attr, 'switch');
		$name     = $tag['name'];
		$varArray = explode('|', $name);
		$name     = array_shift($varArray);
		$name     = $this->autoBuildVar($name);
		if(count($varArray) > 0){
			$name = $this->tpl->parseVarFunction($name, $varArray);
		}
		$parseStr = '<?php switch(' . $name . '): ?>' . $content . '<?php endswitch;?>';

		return $parseStr;
	}

	/**
	 * case标签解析 需要配合switch才有效
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _case($attr, $content){
		$tag   = $this->parseXmlAttr($attr, 'case');
		$value = $tag['value'];
		if('$' == substr($value, 0, 1)){
			$varArray = explode('|', $value);
			$value    = array_shift($varArray);
			$value    = $this->autoBuildVar(substr($value, 1));
			if(count($varArray) > 0){
				$value = $this->tpl->parseVarFunction($value, $varArray);
			}
			$value = 'case ' . $value . ': ';
		} elseif(strpos($value, '|')){
			$values = explode('|', $value);
			$value  = '';
			foreach($values as $val){
				$value .= 'case ' . var_export($val, true) . ': ';
			}
		} else{
			$value = 'case ' . var_export($value, true) . ': ';
		}
		$parseStr = '<?php ' . $value . ' ?>' . $content;
		$isBreak  = isset($tag['break'])? $tag['break'] : '';
		if('' == $isBreak || $isBreak){
			$parseStr .= '<?php break;?>';
		}

		return $parseStr;
	}

	/**
	 * default标签解析 需要配合switch才有效
	 * 使用： <default />ddfdf
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _default($attr, $content){
		$parseStr = '<?php default: ?>';

		return $parseStr;
	}

	/**
	 * compare标签解析
	 * 用于值的比较 支持 eq neq gt lt egt elt heq nheq 默认是eq
	 * 格式： <compare name="" type="eq" value="" >content</compare>
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 * @param string $type    标签内容
	 *
	 * @return string
	 */
	public function _compare($attr, $content, $type = 'eq'){
		$tag      = $this->parseXmlAttr($attr, 'compare');
		$name     = $tag['name'];
		$value    = $tag['value'];
		$type     = isset($tag['type'])? $tag['type'] : $type;
		$type     = $this->parseCondition(' ' . $type . ' ');
		$varArray = explode('|', $name);
		$name     = array_shift($varArray);
		$name     = $this->autoBuildVar($name);
		if(count($varArray) > 0){
			$name = $this->tpl->parseVarFunction($name, $varArray);
		}
		if('$' == substr($value, 0, 1)){
			$value = $this->autoBuildVar(substr($value, 1));
		} else{
			$value = var_export($value, true);
		}
		$parseStr = '<?php if((' . $name . ') ' . $type . ' ' . $value . '): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**  */
	public function _eq($attr, $content){
		return $this->_compare($attr, $content, 'eq');
	}

	/**  */
	public function _equal($attr, $content){
		return $this->_compare($attr, $content, 'eq');
	}

	/**  */
	public function _neq($attr, $content){
		return $this->_compare($attr, $content, 'neq');
	}

	/**  */
	public function _notequal($attr, $content){
		return $this->_compare($attr, $content, 'neq');
	}

	/**  */
	public function _gt($attr, $content){
		return $this->_compare($attr, $content, 'gt');
	}

	/**  */
	public function _lt($attr, $content){
		return $this->_compare($attr, $content, 'lt');
	}

	/**  */
	public function _egt($attr, $content){
		return $this->_compare($attr, $content, 'egt');
	}

	/**  */
	public function _elt($attr, $content){
		return $this->_compare($attr, $content, 'elt');
	}

	/**  */
	public function _heq($attr, $content){
		return $this->_compare($attr, $content, 'heq');
	}

	/**  */
	public function _nheq($attr, $content){
		return $this->_compare($attr, $content, 'nheq');
	}

	/**
	 * range标签解析
	 * 如果某个变量存在于某个范围 则输出内容 type= in 表示在范围内 否则表示在范围外
	 * 格式： <range name="var|function"  value="val" type='in|notin' >content</range>
	 * example: <range name="a"  value="1,2,3" type='in' >content</range>
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 * @param string $type    比较类型
	 *
	 * @return string
	 */
	public function _range($attr, $content, $type = 'in'){
		$tag      = $this->parseXmlAttr($attr, 'range');
		$name     = $tag['name'];
		$value    = $tag['value'];
		$varArray = explode('|', $name);
		$name     = array_shift($varArray);
		$name     = $this->autoBuildVar($name);
		if(count($varArray) > 0){
			$name = $this->tpl->parseVarFunction($name, $varArray);
		}

		$type = isset($tag['type'])? $tag['type'] : $type;

		if('$' == substr($value, 0, 1)){
			$value = $this->autoBuildVar(substr($value, 1));
			$str   = 'is_array(' . $value . ')?' . $value . ':explode(\',\',' . $value . ')';
		} else{
			$value = '"' . $value . '"';
			$str   = 'explode(\',\',' . $value . ')';
		}
		if($type == 'between'){
			$parseStr = '<?php $_RANGE_VAR_=' . $str . ';if(' . $name . '>= $_RANGE_VAR_[0] && ' . $name .
						'<= $_RANGE_VAR_[1]):?>' . $content . '<?php endif; ?>';
		} elseif($type == 'notbetween'){
			$parseStr = '<?php $_RANGE_VAR_=' . $str . ';if(' . $name . '<$_RANGE_VAR_[0] || ' . $name .
						'>$_RANGE_VAR_[1]):?>' . $content . '<?php endif; ?>';
		} else{
			$fun      = ($type == 'in')? 'in_array' : '!in_array';
			$parseStr = '<?php if(' . $fun . '((' . $name . '), ' . $str . ')): ?>' . $content . '<?php endif; ?>';
		}

		return $parseStr;
	}

	/** range标签的别名 用于in判断*/
	public function _in($attr, $content){
		return $this->_range($attr, $content, 'in');
	}

	/** range标签的别名 用于notin判断*/
	public function _notin($attr, $content){
		return $this->_range($attr, $content, 'notin');
	}

	/**  */
	public function _between($attr, $content){
		return $this->_range($attr, $content, 'between');
	}

	/**  */
	public function _notbetween($attr, $content){
		return $this->_range($attr, $content, 'notbetween');
	}

	/**
	 * present标签解析
	 * 如果某个变量已经设置 则输出内容
	 * 格式： <present name="" >content</present>
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _present($attr, $content){
		$tag      = $this->parseXmlAttr($attr, 'present');
		$name     = $tag['name'];
		$name     = $this->autoBuildVar($name);
		$parseStr = '<?php if(isset(' . $name . ')): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**
	 * notpresent标签解析
	 * 如果某个变量没有设置，则输出内容
	 * 格式： <notpresent name="" >content</notpresent>
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _notpresent($attr, $content){
		$tag      = $this->parseXmlAttr($attr, 'notpresent');
		$name     = $tag['name'];
		$name     = $this->autoBuildVar($name);
		$parseStr = '<?php if(!isset(' . $name . ')): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**
	 * empty标签解析
	 * 如果某个变量为empty 则输出内容
	 * 格式： <empty name="" >content</empty>
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _empty($attr, $content){
		$tag      = $this->parseXmlAttr($attr, 'empty');
		$name     = $tag['name'];
		$name     = $this->autoBuildVar($name);
		$parseStr = '<?php if(empty(' . $name . ')): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**  */
	public function _notempty($attr, $content){
		$tag      = $this->parseXmlAttr($attr, 'notempty');
		$name     = $tag['name'];
		$name     = $this->autoBuildVar($name);
		$parseStr = '<?php if(!empty(' . $name . ')): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**
	 * 判断是否已经定义了该常量
	 * <defined name='TXT'>已定义</defined>
	 *
	 * @param <type> $attr
	 * @param <type> $content
	 *
	 * @return string
	 */
	public function _defined($attr, $content){
		$tag      = $this->parseXmlAttr($attr, 'defined');
		$name     = $tag['name'];
		$parseStr = '<?php if(defined(' . var_export($name, true) . ')): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**  */
	public function _notdefined($attr, $content){
		$tag      = $this->parseXmlAttr($attr, '_notdefined');
		$name     = $tag['name'];
		$parseStr = '<?php if(!defined("' . $name . '")): ?>' . $content . '<?php endif; ?>';

		return $parseStr;
	}

	/**
	 * assign标签解析
	 * 在模板中给某个变量赋值 支持变量赋值
	 * 格式： <assign name="" value="" />
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _assign($attr, $content){
		$tag  = $this->parseXmlAttr($attr, 'assign');
		$name = $this->autoBuildVar($tag['name']);
		if(isset($tag['value'])){
			if('$' == $tag['value']{0}){
				$value = $this->autoBuildVar(substr($tag['value'], 1));
			} elseif(':' == $tag['value']{0}){
				$value = substr($tag['value'], 1);
			} else{
				$value = var_export($tag['value'], true);
			}
		} elseif(isset($tag['source'])){
			$value = $this->source_php($tag['source']);
		} else{
			Think::halt('assign标签中source和value至少有一个。');
		}

		$parseStr = '<?php ' . $name . ' = ' . $value . '; ?>';

		return $parseStr;
	}

	/**
	 * define标签解析
	 * 在模板中定义常量 支持变量赋值
	 * 格式： <define name="" value="" />
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _define($attr, $content){
		$tag  = $this->parseXmlAttr($attr, 'define');
		$name = var_export($tag['name'], true);
		if('$' == substr($tag['value'], 0, 1)){
			$value = $this->autoBuildVar(substr($tag['value'], 1));
		} else{
			$value = var_export($tag['value'], true);
		}
		$parseStr = '<?php define(' . $name . ', ' . $value . '); ?>';

		return $parseStr;
	}

	/**
	 * for标签解析
	 * 格式： <for start="" end="" comparison="" step="" name="" />
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _for($attr, $content){
		//设置默认值
		$start      = 0;
		$end        = 0;
		$step       = 1;
		$comparison = 'lt';
		$name       = 'i';
		$rand       = rand(); //添加随机数，防止嵌套变量冲突
		//获取属性
		foreach($this->parseXmlAttr($attr, 'for') as $key => $value){
			$value = trim($value);
			if(':' == substr($value, 0, 1)){
				$value = substr($value, 1);
			} elseif('$' == substr($value, 0, 1)){
				$value = $this->autoBuildVar(substr($value, 1));
			}
			switch($key){
			case 'start':
				$start = $value;
				break;
			case 'end' :
				$end = $value;
				break;
			case 'step':
				$step = $value;
				break;
			case 'comparison':
				$comparison = $value;
				break;
			case 'name':
				$name = $value;
				break;
			}
		}

		$parseStr = '<?php $__FOR_START_' . $rand . '__=' . $start . ';$__FOR_END_' . $rand . '__=' . $end . ';';
		$parseStr .= 'for($' . $name . '=$__FOR_START_' . $rand . '__;' .
					 $this->parseCondition('$' . $name . ' ' . $comparison . ' $__FOR_END_' . $rand . '__') . ';$' .
					 $name . '+=' . $step . '){ ?>';
		$parseStr .= $content;
		$parseStr .= '<?php } ?>';

		return $parseStr;
	}

	/**
	 * 920223
	 * 标签解析
	 * 格式： <url app="{$var}" action="Login" method="index" path="" protocol="http" suffix="html" params="varname" param-A="{$a}"/>
	 * @access public
	 *
	 * @param string $attr    标签属性
	 * @param string $content 标签内容
	 *
	 * @return string
	 */
	public function _url($attr, $content){
		$tag = $this->parseXmlAttr($attr, 'url');
		if(isset($tag['map'])){
			return map_url($tag['map']);
		}

		$url = ThinkInstance::UrlHelper();
		$url->reset();

		// params,app,action,method,suffix,protocol
		if(isset($tag['params'])){
			$url->setParamStr($tag['params']);
			unset($tag['params']);
		}
		if(isset($tag['suffix'])){
			$url->setSuffix($tag['suffix']);
			unset($tag['suffix']);
		}
		if(isset($tag['action'])){
			$url->setAction($tag['action']);
			unset($tag['action']);
		}
		if(isset($tag['app'])){
			$url->setApp($tag['app']);
			unset($tag['app']);
		}
		if(isset($tag['method'])){
			$url->setMethod($tag['method']);
			unset($tag['method']);
		}
		if(isset($tag['path'])){
			$url->setPath($tag['path']);
			unset($tag['path']);
		}
		if(isset($tag['protocol'])){
			$url->setProtocol($tag['protocol']);
			unset($tag['protocol']);
		}

		foreach($tag as $name => $val){
			$url->setParam(\COM\MyThink\Strings::blocktrim($name, 'param-', STR_TRIM_LEFT), $val);
		}

		$ret = $url->getUrl();
		if(!empty($tag)){
			$ret = preg_replace('#%7B%24(.*?)%7D#', '{\\$$1}', $ret);
		}

		return $ret;
	}

	/**
	 * 预处理script标签
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string
	 */
	public function _script($attr, $content){
		$tag = $this->parseXmlAttr($attr, 'script');

		if(isset($tag['src']) || $tag['type'] != 'text/javascript' || !isset($tag['wrap']) || !$tag['wrap']){
			return false;
		}

		$wrap    = $tag['wrap'];
		$globals = isset($tag['globals'])? $tag['globals'] : '';
		$declare = isset($tag['declare'])? 'var ' . $tag['declare'] . ";\n" : '';

		$content = "\n" . $content . "\n"; // 防止最后一行是注释

		unset($tag['type'], $tag['globals'], $tag['declare'], $tag['wrap']);
		$attr = HTML::attr($tag);

		switch($wrap){
		case 'ready':
			return '<script type="text/javascript"' . $attr . '>"use strict";' . $declare . '$(function($){' .
				   $content . '});</script>';
		case 'closure':
			return '<script type="text/javascript"' . $attr . '>"use strict";' . $declare . '(function(' . $globals .
				   '){' . $content . '})(' . $globals . ');</script>';
		default:
			Think::halt('&lt;script&gt; 中使用了未知的wrap属性。');
			return '';
		}
	}

	/**
	 * 预处理link标签
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string
	 */
	public function _link($attr, $content){
		return false;
	}

	/**
	 * 预处理style标签
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string
	 */
	public function _style($attr, $content){
		$tag = $this->parseXmlAttr($attr, 'style');
		if(isset($tag['parse'])){
			return false;
		}
		if(!STATIC_DEBUG && $tag['type'] == 'text/less'){
			$content     = less_compile($content);
			$tag['type'] = 'text/css';
		} else{
			$content = "\n" . $content . "\n";
		}
		$content = broswer_css_perfix($content);
		return '<style type="' . $tag['type'] . '" parse="true">' . $content . '</style>';
	}

	/**  */
	public function _iif($attr, $unused){
		$tag  = $this->parseXmlAttr($attr, 'iif');
		$name = $this->autoBuildVar($tag['name']);
		if(!isset($tag['then'])){
			$then = $name;
		} elseif($tag['then']{0} == '$'){
			$then = $this->autoBuildVar(substr($attr['then'], 1));
		} else{
			$then = var_export($tag['then'], true);
		}
		if(!isset($tag['else'])){
			$else = "''";
		} elseif($tag['else']{0} == '$'){
			$else = $this->autoBuildVar(substr($tag['else'], 1));
		} else{
			$else = var_export($tag['else'], true);
		}
		if(!isset($tag['type'])){
			$type = "empty";
		} else{
			$type = $tag['type'];
		}

		return '<?php echo (' . $type . '(' . $name . ')?' . $then . ':' . $else . ');?>';
	}

	/**  */
	public function _comment($attr, $content){
		return "\n";
	}

	/**
	 * 处理表单
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return string
	 */
	public function _formdescript($attr, $content){
		$tag = $this->parseXmlAttr($attr, 'formdescript');
		$tpl = $tag['template'];
		unset($tag['template']);
		return HTML::form($content, $tpl, $tag);
	}

	private function source_php($source){
		$d = S('TagLibCxSourcePhp.' . $source);
		if($d){
			return $d;
		}
		$hit = false;
		include_one([
					LIB_PATH . 'DataSource/' . $source . '.php',
					BASE_LIB_PATH . 'DataSource/' . $source . '.php'
					],
					$hit);
		$ret = false;
		if($hit){
			$ret = 'require(\'' . $hit . '\')';
		} else{
			$file = LIB_PATH . 'DataSource/' . $source . '.json';
			if(is_file($file)){
				$ret = 'json_decode(file_get_contents(\'' . $file . '\'),false)';
			}
			$file = BASE_LIB_PATH . 'DataSource/' . $source . '.json';
			if(is_file($file)){
				$ret = 'json_decode(file_get_contents(\'' . $file . '\'),false)';
			}
		}
		if($ret){
			S('TagLibCxSourcePhp.' . $source, $ret);
			return $ret;
		} else{
			return 'Think::halt("TaglibCx找不到资源[' . $source . ']");';
		}
	}

	private function source($source){
		$hit  = false;
		$data = include_one([
							LIB_PATH . 'DataSource/' . $source . '.php',
							BASE_LIB_PATH . 'DataSource/' . $source . '.php'
							],
							$hit);
		if(!$hit){
			$file = LIB_PATH . 'DataSource/' . $source . '.json';
			if(!is_file($file)){
				$file = BASE_LIB_PATH . 'DataSource/' . $source . '.json';
				if(!is_file($file)){
					Think::halt('模板不存在： ' . $file);
				}
			}
			$data = json_decode(file_get_contents($file), true);
			if($data === null){
				Think::halt(json_last_message());
			}
		}
		return $data;
	}
}
