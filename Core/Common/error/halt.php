<?php
function halt($msg){
	trace("使用halt函数", '', 'ERROR');
	Think::halt($msg);
}
