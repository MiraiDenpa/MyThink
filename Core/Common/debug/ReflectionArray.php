<?php
/**
 * 密码加密、验证
 * Class password
 * @package GFW
 */
class ReflectionArray{
	/**
	 * 分析一个函数或一个方法
	 *
	 * @param ReflectionFunction|ReflectionMethod|callable $function
	 *
	 * @return array
	 */
	public static function parseFunction($function){
		if(is_object($function)){
			$ref = & $function;
		} else{
			$ref = new ReflectionFunction($function);
		}

		$meta               = self::parseComment($ref->getDocComment());
		$meta['namespace']  = $ref->getNamespaceName();
		$meta['all_param']  = $ref->getNumberOfParameters();
		$meta['must_param'] = $ref->getNumberOfRequiredParameters();
		$meta['name']       = $ref->getName();

		if(method_exists($ref, 'getModifiers')){ // 是一个method
			$meta['modifiers'] = $ref->getModifiers();
		}

		$params = $ref->getParameters();
		foreach($params as $param){
			$param                         = self::parseParam($param);
			$meta['param'][$param['name']] = $param;
		}

		return $meta;
	}

	/**
	 * 分析一个参数
	 * @param ReflectionParameter $p
	 *
	 * @return array
	 */
	private static function parseParam(ReflectionParameter &$p){
		$meta                  = array();
		$meta['id']            = $p->getPosition();
		$meta['name']          = $p->getName();
		$meta['default_const'] = $meta['default'] = null;
		if($p->isDefaultValueAvailable()){
			$meta['default']       = $p->getDefaultValue();
			$meta['default_const'] = $p->isDefaultValueConstant()? $p->getDefaultValueConstantName() : '';
		}
		$meta['can_null'] = $p->allowsNull();
		$meta['optional'] = $p->isOptional();
		$meta['refer']    = $p->isPassedByReference();

		return $meta;
	}

	/* 类处理 */
	/**
	 * 分析一个类
	 *
	 * @param string|ReflectionClass|object $cls
	 * @param bool                          $all_final 是否只显示final的方法
	 *
	 * @return array
	 */
	public static function parseClass($cls, $all_final = false){
		if(is_string($cls) || is_object($cls)){
			$ref = new ReflectionClass($cls);
		} elseif(is_a($cls, 'ReflectionClass')){
			$ref = & $cls;
		} else{
			Think::halt(LANG_PARAM_ERROR . ': ReflectionArray::parseClass');
			exit;
		}
		/** @var ReflectionClass $ref */

		$meta              = self::parseComment($ref->getDocComment());
		$meta['name']      = $ref->getName();
		$meta['namespace'] = $ref->getNamespaceName();
		$meta['parent']    = $ref->getParentClass();
		$meta['file']      = $ref->getFileName();
		$meta['traits']    = $ref->getTraitNames();

		$meta['interfase'] = $ref->getInterfaceNames();

		$mets           = $ref->getMethods();
		$meta['method'] = '';
		foreach($mets as $method){
			/** @var ReflectionMethod $method */
			if($method->isPublic() && $method->isUserDefined() && (!$all_final || $method->isFinal())){
				$meta['method'][$method->name] = self::parseFunction($method);
			}
		}
		if($ref->getParentClass()){
			$meta['parent'] = $ref
							  ->getParentClass()
							  ->getName();
		}

		$meta['const'] = $ref->getConstants();
		$ppts          = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach($ppts as $ppt){
			$meta['property'] = $ppt->getName();
		}
		$meta['abstract'] = $ref->isAbstract();

		return $meta;
	}

	/**
	 * 分析注释快内容
	 * 返回tag => content
	 * （没有tag的部分为 “phd” ）
	 *
	 * @param $comment 注释
	 *
	 * @return array
	 */
	public static function parseComment($comment){
		$mats = $meta = array();

		$lines    = explode("\n", $comment);
		$in_block = false;
		$block    = null;
		$phd      = '';
		foreach($lines as $line){
			$line = trim($line);
			if($line == '*/' || $line == '/**'){
				continue;
			}
			if(preg_match('#^\*\s+@(\S+)#', $line, $mats)){
				$tag = $mats[1];
				if($tag == 'param'){
					$meta[$tag][] = '';
					$block        = & $meta[$tag][count($meta[$tag]) - 1];
				} else{
					$meta[$tag] = '';
					$block      = & $meta[$tag];
				}
				$line     = trim(substr($line, strpos($line, $tag) + strlen($tag)));
				$in_block = true;
			}
			if($in_block){
				$block .= "\n" . $line;
			} else{
				$phd .= "$line\n";
			}
		}

		if(isset($meta['param'])){
			$params = array();
			foreach($meta['param'] as &$text){
				$text = trim($text);
				$text = preg_replace('#^\*\s+#m', '', $text);
				preg_match('#^(\S+)\s+\$(\S+)\s+(.*)#s', $text, $mats);
				$params[$mats[2]] = array(
					'type'    => $mats[1],
					'comment' => $mats[3],
				);
			}
			$meta['param'] = $params;
		}

		if(isset($meta['return'])){
			$meta['return'] = trim($meta['return']);
			$meta['return'] = preg_replace('#^\s*\*\s+#m', '', $meta['return']);
			preg_match('#^(\S+)\s*(.*)?#s', $meta['return'], $mats);
			$meta['return'] = array(
				'type'    => $mats[1],
				'comment' => $mats[2],
			);
		}

		$phd         = trim($phd);
		$meta['phd'] = preg_replace('#^\s*\*\s+#m', '', $phd);

		foreach($meta as &$prop){
			if(is_string($prop)){
				$prop = trim($prop);
			}
		}

		return $meta;
	}
}
