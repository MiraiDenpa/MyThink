<?php
/**
 * 确应该输出的标题
 */
function CompileTitle(){
	return isset($GLOBALS['title']) ? $GLOBALS['title'] : '<?php echo isset($title)?$title:LANG_DEFAULT_TITLE;?>';
}
