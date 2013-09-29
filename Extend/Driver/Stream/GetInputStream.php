<?php
class GetInputStream extends InputStream{
	use BaseInputStream;
	
	function __construct(){
		$this->_DATA = & $_GET;
	}

	protected function valid_pager($var, $notuse){
		if(is_array($var)){
			if($var[0] < 0 || $var[1] < 0){
				return false;
			}
		} else{
			if($var < 0){
				return false;
			}
		}
		return true;
	}
}
