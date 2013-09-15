<?php
class PostInputStream extends InputStream{
	function __construct(){
		$this->_DATA = & $_POST;
	}

	protected function valid_is_same($var, $other){
		return $this->get($other) == $var;
	}

	protected function valid_equal($var, $data){
		return $data == $var;
	}

	protected function valid_length($var, $range){
		$len = strlen($var);
		if($range[0] && $len < $range[0]){
			return false;
		}
		if($range[1] && $len > $range[1]){
			return false;
		}
		return true;
	}
}
