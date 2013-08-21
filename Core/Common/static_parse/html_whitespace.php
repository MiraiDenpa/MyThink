<?php
function html_whitespace($content, $force = false){
	$find        = array('#^\s+<#ms','#>\s+$#ms');
	$replace     = array('<','>');
	
	// <DEBUG>
	if(!$force){
		$find        = array('~>\s*?\n\s*(\n\s*?)\s*<~s');
		$replace     = array('>\1<');
	}
	// </DEBUG>
	
	/* 去除html空格与换行 */
	$content = preg_replace($find, $replace, $content);
	if($force){
		$content = str_replace(">\n", '>', $content);
	}

	// style & js
	$content = preg_replace_callback('#<style type="text/css".*?>.*?</style>#s', function($mats){
		return (APP_DEBUG?"\n":'').preg_replace('#\s+#s',' ',$mats[0]);
	}, $content);
	$content = preg_replace_callback('#<script type="text/javascript".*?>.*?</script>#s', function($mats){
		return (APP_DEBUG?"\n":'').preg_replace('#\n\s*#s',' ',$mats[0]);
	}, $content);
	
	return $content;
}
