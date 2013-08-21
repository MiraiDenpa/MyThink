<?php
/**
 * 返回标准头部
 *
 * @param $title
 *
 * @return string
 */
function StandardHeader(){
	$head = '';
	$head .= HTML::importFile(searchPublic('bootstrap.css'));
	$head .= HTML::css(PUBLIC_URL.'/artDialog/skins/<?php echo art_skin();?>.css');
	
	$head .= HTML::importFile(searchPublic('jquery.js'));
	$head .= HTML::importFile(searchPublic('bootstrap.js'));
	$head .= HTML::importFile(searchPublic('jquery.artDialog.js'));
	$head .= HTML::importFile(searchPublic('artDialog.plugins.js'));
	if(APP_DEBUG){
		$head .= HTML::importFile(searchPublic('less.js'));
	}
	
	return $head;
}
