<?php
/**
 * @var string $fdml
 * @var string $formId
 */
$fdml     = explode("\n", $fdml);
$blocks   = [];
$validate = [
	'rules'    => [],
	'messages' => [],
];
$html     = [];

$parse_control  = function ($line, &$itr) use (&$blocks, &$validate, &$html){
	if(strpos($line, 'set') === 0){
		// 分割 fieldset
		list ($id, $attr) = explode('!', trim(substr($line, 3)));
		$blocks[] = [
			'htmltype' => 'fieldset',
			'id'       => $id,
			'attrs'    => $attr
		];
		return false;
	}
	if(strpos($line, 'validate') === 0){
		$script = trim(substr($line, 8));
		preg_match('#<JSON>.*?(\{.*\})[;\s]*</JSON>#s', $script, $mats);
		if(!$mats[1]){
			Think::halt('HTML::form #validate 语句没有正确的script标签');
		}
		$validate['_json'] = $mats[1];
		return false;
	}elseif(strpos($line, 'submit') === 0){
		$title = trim(substr($line, 6));
		if(strpos($title, '!') !== false){
			list($title, $attr) = explode('!', $title, 2);
		} else{
			$attr = 'class="btn btn-primary"';
		}
		$blocks[] = [
			'htmltype' => 'submit',
			'name'     => '',
			'default'  => $title,
			'varname'  => '',
			'attrs'    => $attr,
		];
		return false;
	}elseif($line =='end'){
		return ''; // end last block
	}elseif(strpos($line,'<lit')!==false){
		$blocks[] = [
			'htmltype' => 'literal',
			'name'     => '',
			'varname'  => '',
			'text'    => $line,
		];
		return false;
	}
	
	return $line;
};
$parse          = function ($line, &$itr){
	$lid = $line{0};
	switch($lid){
	case '>': // min, minlength
		$type = $itr['htmltype'] == 'number'? 'min' : 'minlength';
		break;
	case '<': // max, minlength
		$type = $itr['htmltype'] == 'number'? 'max' : 'maxlength';
		break;
	case '~': // range,rangelength
		$type = $itr['htmltype'] == 'number'? 'range' : 'rangelength';
		break;
	case '=': // equalTo
		$type = 'equalTo';
		break;
	case '?': // ajax
		$type = 'remote';
		break;
		break;
	case '{': // 不是validate（设置默认填写值）
		$itr['default'] = $line;
		return;
	case '!': // 不是validate（添加属性）
		list($n, $v) = explode('=', substr($line, 1), 2);
		$itr['attrs'][$n] = trim($v, '"');
		return;
	default:
		if(isset($itr['info'])){
			trigger_error('HTML::form多行文本描述(' . $itr['id'] . '): <br>L:' . dump_some($itr['info']) . '<br>C:' .
						  dump_some($line), E_USER_ERROR);
		}

		if(strpos($line, '!') === false){
			$line .= '!';
		}
		list($itr['info'], $itr['infoattr']) = explode('!', $line);
		return;
	}

	$line = substr($line, 1);
	list($itr['rule'][$type], $itr['message'][$type]) = explode(' - ', $line);
};
$get_input_type = function (&$itr){
	// 吧参数设置成validate.[type] = true 的type值
	// 返回html文本框的样式
	$itr['htmltype'] = $itr['type'];
	$itr['type']     = '';

	switch($itr['htmltype']){
	case 'digits':
		$itr['htmltype'] = 'number';
		break;
	case 'repassword':
		$itr['htmltype']        = 'password';
		$itr['rule']['equalTo'] = '#password';
		$itr['varname']         = '';
	default:
		$itr['type'] = '';
	}
};

// 前置循环
$new_fdml = [];
for($i = 0, $j = count($fdml); $i < $j; $i++){
	$line = trim($fdml[$i]);
	if(!$line){
		continue;
	}

	if(strpos($line, '<JSON') !== false){
		while(true){
			$nl = $fdml[++$i];
			$line .= "\n" . rtrim($nl);
			if(strpos($nl, '</JSON>') !== false){
				$new_fdml[count($new_fdml) - 1] .= $line;
				break;
			}
			if($i > $j){
				Think::halt('HTML::form找不到script的关闭标签');
			}
		}
	} elseif(strpos($line, '<lit') !== false){
		$line = str_replace('<lit', '# <lit', $line);
		while(true){
			$nl = $fdml[++$i];
			$line .= "\n" . rtrim($nl);
			if(strpos($nl, '</lit>') !== false){
				$new_fdml[count($new_fdml)] = $line;
				break;
			}
			if($i > $j){
				Think::halt('HTML::form找不到literal的关闭标签');
			}
		}
	} else{
		$new_fdml[] = $line;
	}
}

// 主循环
foreach($new_fdml as $line){
	if($line{0} == '#'){
		$line = $parse_control(substr($line, 1), @$tmp);
		if(isset($tmp)){
			$blocks[] = $tmp;
		}
		unset($tmp);
	}
	if(!$line){
		continue;
	}

	if(preg_match('%^(.*?)#(.*?)\->(.*?)$%', $line, $mats)){
		if(isset($tmp)){
			$blocks[] = $tmp;
		}
		// 发现基础行，新建表单项目
		$tmp = [];

		// 检查名称，第一个是*则必须填写
		$name = trim($mats[1]);
		if($tmp['required'] = ($name{0} === '*')){
			$tmp['name'] = substr($name, 1);
			$tmp['rule'] = ['required' => true];
		} else{
			$tmp['name'] = $name;
			$tmp['rule'] = [];
		}

		// 基础属性
		$tmp['message'] = [];
		$id             = $tmp['id'] = trim($mats[2]); // input.id label.for
		$tmp['varname'] = $tmp['id']; // input.name
		$tmp['type']    = trim($mats[3]); // validate.type: email/digits/xxxx
		$get_input_type($tmp); // htmltype -> input.type
		continue;
	}

	// 普通行处理
	if(!$tmp){
		Think::halt('HTML::form无法生成要求的表单，意料外的行： ' . $line);
	}

	//检查行ID
	$parse($line, $tmp);
}
if(isset($tmp)){
	$blocks[] = $tmp;
}

unset($fdml, $line, $parse, $get_input_type, $parse_control);
// 分离数据
foreach($blocks as &$item){
	$htmltype = $item['htmltype'];
	
	if(isset($item['required']) && $item['required']){
		$item['required'] = 'required';
	} else{
		unset($item['required']);
	}
	$item['placeholder'] = $item['name'];
	$item['name']        = $item['varname'];
	$item['type']        = $htmltype;
	unset($item['htmltype'], $item['varname']);

	if(!empty($item['rule'])){
		$validate['rules'][$item['id']]    = $item['rule'];
		$validate['messages'][$item['id']] = $item['message'];
	}
	unset($item['rule'], $item['message']);

	if(!isset($item['default'])){
		$item['default'] = '';
	}
	if(isset($item['attrs'])){
		if(is_array($item['attrs'])){
			$item = array_merge($item['attrs'], $item);
		} else{
			$item['_text'] = $item['attrs'];
		}
		unset($item['attrs']);
	}

	if($item['type'] != 'radio' && $item['type'] != 'checkbox' && $item['type'] != 'submit'){
		if(isset($item['class'])){
			$item['class'] .= ' form-control';
		} else{
			$item['class'] = 'form-control';
		}
	}

	$html[] = $item;
}
unset($item, $htmltype);
/*var_display_max_depth(20);
var_dump($html, $validate);*/

if(JS_DEBUG){
	$validate['debug'] = true;
}

if(isset($validate['_json']) && $validate['_json']){
	$extra = trim($validate['_json']);
	$extra = substr($extra, 1, -1);
	unset($validate['_json']);
	$val_script = trim(json_encode($validate, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE + JSON_FORCE_OBJECT));
	$val_script = rtrim(substr($val_script, 0, -1), "\n,") . ",\n" . $extra . "\n}";
} else{
	$val_script = trim(json_encode($validate, JSON_PRETTY_PRINT + JSON_PRETTY_PRINT));
}
$val_function = "$('#{$formId}').validate({$val_script})";
HTML::ReadyCode($val_function);

/**
 * @var string $parser
 */
return require __DIR__ . '/form/' . $parser . '.php';

