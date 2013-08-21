<?php

class OutputBuffer{
	public $end_flush = false;
	public $level = 0;

	public function __construct(callable $func = null, $size = 0){
		$this->level = ob_get_level();
		trace('打开缓冲区。', 'OB.'.$this->level, 'INFO');

		// <DEBUG>
		if(1){
			$level = $this->level;
			ob_start(Closure::bind(function ($content) use ($func,$level){
				trace('缓冲区结束，调用处理程序。' . dump_some($func), 'OB.'.$level, 'INFO');
				if(is_callable($func)){
					return $func($content);
				}else{
					return $content;
				}
			}, NULL), $size);
			return;
		}
		// </DEBUG>
		ob_start($func, $size);
	}

	public function __destruct(){
		if($this->end_flush){
			ob_end_flush();
		} else{
			ob_end_clean();
		}
		trace('缓冲区关闭。', 'OB.'.$this->level, 'INFO');
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
