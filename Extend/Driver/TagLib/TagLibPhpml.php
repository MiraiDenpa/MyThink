<?php
/**
 * 提供<select/><textarea/>等HTML标签
 * 覆盖HTML标准定义
 * 提供动态生成HTML功能
 */

class TagLibPhpml extends TagLib{
	protected $tags = array(
		'select' => array(
			'attr' => 'data,value',
			'must' => 'value',
			'close' => 1
		),
		'textarea' => array(
			'attr' => 'id,name,data-width,data-height',
			'must' => 'id,data-width,data-height',
			'close' => 1
		),
	);

	public function _select($attr,$content){
		$attr = $this->parseXmlAttr($attr, 'select');
		$value = $attr["value"];

		if(isset($attr["data"])){
			$data = $attr["data"];
			if(strpos($data, '=')>0){
				parse_str($attr["data"], $data);
			}else{
				$data = $this->autoBuildVar($data);
			}
		}else{
			$data = '';
		}

		$value = $this->autoBuildVar($value);
		unset($attr["data"],$attr["value"]);
		if(empty($value)) throw_exception('TagLibGongT:select 没有传入一个正确的默认值');

		$a = ' ';
		foreach($attr as $k=>$v){
			$a .= $k.'="'.$v.'" ';
		}
		$a = trim($a);
		$parseStr = "<select {$a}>";
		$parseStr .= "<?php ";

		$content = var_export($content,true);
		if($content){
			$parseStr .= "echo @str_replace('value=\"'.{$value}.'\"','value=\"'.{$value}.'\" selected=\"selected\"',{$content});";
		}

		if(!empty($data)) $parseStr .= "foreach({$data} as \$value => \$label) echo '<option value=\"'.\$value.'\"'.(\$value=={$value}?'selected=selected':'').'>'.\$label.'</option>';";
		$parseStr .= "?></select>";

		return $parseStr;
	}

	public function _textarea($attr,$content){
		$attArr = $this->parseXmlAttr($attr, 'textarea');

		$textArea = "<textarea {$attr} data-init=\"editor\">{$content}</textarea>";
		$script = "<Header:KindEdit /><script type=\"text/javascript\">
		
		
		</script>";
		return $textArea.$script;
	}
}
