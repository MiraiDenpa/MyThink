<?php
$ret = '';
/**
 * @var array $html
 * @var array $validate
 */
$inset = false;
foreach($html as $data){
	$type    = $data['type'];
	$default = $data['default'];
	unset($data['type'], $data['default']);
	
	if($type == 'radio' || $type == 'checkbox'){
		$line = '<div class="'.$type.'">';
	} elseif($type == 'fieldset'){
		if($inset){
			$ret .='</fieldset>';
		}
		$ret .= '<fieldset id="'.$data['id']."\">\n";
		$inset = true;
		$ret .= $line;
		continue;
	}else{
		$line = '<div class="form-group">';
	}

	if($type == 'radio' || $type == 'checkbox'){
		$info = $data['info'];
		$infoattr = $data['infoattr'];
		unset($data['info'],$data['infoattr']);
		$input = HTML::input($type, $default, $data);
		$line .= HTML::label($data['id'], $input.$info, $infoattr);
	} elseif($type == 'submit'){
		$line .= HTML::input($type, $default, $data);
	} elseif($type == 'literal'){
		$line .= $data['text'];
	}else{
		$info = $data['info'];
		$infoattr = $data['infoattr'];
		unset($data['info'],$data['infoattr']);
		$line .= HTML::input($type, $default, $data);
		$line .= HTML::label($data['id'], $info, $infoattr);
	}

	$line .= '</div>';
	
	$ret .= $line."\n";
}


return $ret;
/* Basic example
<div class="form-group">
	<label for="exampleInputEmail1">Email address</label>
	<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
</div>
*/

/*Inline form
<div class="form-group">
	<label class="sr-only" for="exampleInputEmail2">Email address</label>
	<input type="email" class="form-control" id="exampleInputEmail2" placeholder="Enter email">
</div>
 */

/*Horizontal form
<div class="form-group">
	<label for="inputEmail1" class="col-lg-2 control-label">Email</label>
	<div class="col-lg-10">
		<input type="email" class="form-control" id="inputEmail1" placeholder="Email">
	</div>
</div>
*/

/*Static control
<div class="form-group">
	<label class="col-lg-2 control-label">Email</label>
	<div class="col-lg-10">
		<p class="form-control-static">email@example.com</p>
	</div>
</div>
*/

/*Checkboxes and radios
<div class="radio">
	<label>
		<input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
		Option two can be something else and selecting it will deselect option one
	</label>
</div>
*/
