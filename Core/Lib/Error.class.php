<?php
class Error extends ThinkException{
	protected static $error_define;
	protected $info;
	protected $name;

	/*protected $message;
	protected $code;
	protected $file;
	protected $line;*/

	public function __construct($code, $trace = '', Exception $previous = null){
		if(!self::$error_define){
			self::$error_define = hidef_load('error-info');
		}

		$this->code = $code;
		$trace      = $trace? $trace : $this->getTrace()[0];
		$this->file = $trace['file'];
		$this->line = $trace['line'];
		unset($trace);

		if(!isset(self::$error_define[$code])){
			Think::halt('错误码 [' . $code . '] 没有定义。', false, BASE_CONF_PATH . 'error.php', 1);
		}
		$this->message = self::$error_define[$code]['message'];
		$this->name    = self::$error_define[$code]['name'];
		$this->info    = self::$error_define[$code];
	}

	public function getUrl(){
		$r = apc_fetch('ThinkError' . $this->code, $success);
		if(!$success){
			$r = '';
			foreach((array)$this->info['url'] as $title => $url){
				$r .= '<a href="' . $url . '" class="btn btn-lg btn-default">' . $title . '</a>';
			}
			apc_store('ThinkError' . $this->code, $r);
		}
		return $r;
	}

	public function getInfo(){
		return $this->info['info'];
	}

	public function getName(){
		return $this->info['name'];
	}

	public function getWhere(){
		return $this->file . ':' . $this->code;
	}

	public function __toString(){
		return $this->name;
	}
}
