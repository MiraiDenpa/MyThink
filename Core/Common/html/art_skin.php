<?php
function art_skin(){
	$agent = $_SERVER['HTTP_USER_AGENT'];
	if(strpos($agent, 'Windows NT 6.2')){
		return 'simple';
	}
	
	if(strpos($agent, 'Chrome')){
		return 'chrome';
	}
	if(strpos($agent, 'Firefox')){
		return 'twitter';
	}
	if(strpos($agent, 'Opera')){
		return 'opera';
	}
	if(strpos($agent, 'Safari')){
		return 'idialog';
	}
	
	if(strpos($agent, 'Windows NT 6.1')){
		return 'aero';
	}
	return 'default';
}
