<?php

class OutputBuffer{
	public $end_flush = false;
	public function __construct(callable $func = null, $size = 0){
		ob_start($func, $size);
	}
	
	public function __destruct(){
		if($this->end_flush){
			ob_end_flush();
		}else{
			ob_end_clean();
		}
	}
	
	public function get(){
		return ob_get_contents();
	}

	public function clean(){
		ob_clean();
	}
	
	public function flush(){
		ob_flush();
	}
}
