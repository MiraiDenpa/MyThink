<?php
function html_whitespace($content, $force = false){
	
	// <DEBUG>
	if(!$force){
		$content = str_replace("\n\n", "\n", $content);
		$content = str_replace("\n\n", "\n", $content);
		$content = str_replace("\n\n", "\n", $content);
	}else{
		// </DEBUG>
		$find        = array('#^\s+<#ms','#>\s+$#ms');
		$replace     = array('<','>');
		$content = preg_replace($find, $replace, $content);
		// <DEBUG>
	}
	// </DEBUG>
	
	/* 去除html空格与换行 */
	if($force){
		$content = str_replace(">\n", '>', $content);
	}
	
	if(!STATIC_DEBUG){
		// inline style & js
		$content = preg_replace_callback('#<style type="text/css".*?>.*?</style>#s', function($mats){
			return "\n".preg_replace('#\s+#s',' ',$mats[0]);
		}, $content);
		$content = preg_replace_callback('#<script type="text/javascript".*?>.*?</script>#s', function($mats){
			return "\n".preg_replace('#\n\s*#s',' ',$mats[0]);
		}, $content);
	}
	
	return $content;
}
