<?php

class SimpleCURL{
	static private $ch;
	static public $headers = array();
	static public $cache = 0;
	static public $proxy = '';

	private static function __init(){
		trace("CURL - Simple 初始化，缓存：" . self::$cache . "秒，在 C(LOG_LEVEL) 添加“CURL”来查看详细日志。");
		self::$ch = curl_init();
		register_shutdown_function('curl_close', self::$ch);
	}

	public static function POST($url, $data=[]){
		if(!self::$ch){
			self::__init();
		}
		if(self::$cache){
			$cache_id = md5('simple_curl_post_' . $url . var_export($data, true) . var_export(self::$headers, true));
			if($ret = S($cache_id)){
				trace($url, "POST 缓存", "CURL");
				if(is_string($data)){
					$debug = '<pre class="xdebug-var-dump" dir="ltr">' . htmlentities($data) . '</pre>';
				} else{
					ob_start();
					var_dump($data);
					$debug = ob_get_clean();
				}
				trace($debug, "发送数据", "CURL");
				trace($ret, "缓存内容", "CURL");
				return $ret;
			}
		}
		set_time_limit(0);
		$defaults = array(
			CURLOPT_POST           => 1,
			CURLOPT_HEADER         => 0,
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT        => 10000,
			CURLOPT_POSTFIELDS     => $data,
			CURLOPT_HTTPHEADER     => self::$headers,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_MAXCONNECTS    => 30,
		);
		if(self::$proxy){
			$defaults += array(
				CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
				CURLOPT_PROXY     => self::$proxy
			);
		}

		curl_setopt_array(self::$ch, $defaults);
		$ret = trim(curl_exec(self::$ch));

		trace($url, "POST TO", "CURL");
		if(is_string($data)){
			$debug = '<pre class="xdebug-var-dump" dir="ltr">' . htmlentities($data) . '</pre>';
		} else{
			ob_start();
			var_dump($data);
			$debug = ob_get_clean();
		}
		trace($debug, "发送数据", "CURL");
		trace(htmlentities($ret), "服务器返回内容", "CURL");

		if(self::$cache){
			S($cache_id, $ret, self::$cache);
		}
		return $ret;
	}

	public static function GET($url, $data = null){
		if(!self::$ch){
			self::__init();
		}
		if(self::$cache){
			$cache_id = md5('simple_curl_get_' . $url . var_export($data, true) . var_export(self::$headers, true));
			if($ret = S($cache_id)){
				trace('GET 缓存' . $url, 'CURL', 'CURL');
				trace("缓存的内容:[$ret]", "CURL", "CURL");
				return $ret;
			}
		}
		set_time_limit(0);
		if($data){
			$url .= '?' . http_build_query($data);
		}
		$defaults = array(
			CURLOPT_POST           => false,
			CURLOPT_HEADER         => 0,
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT        => 40000,
			CURLOPT_HTTPHEADER     => self::$headers,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_MAXCONNECTS    => 30,
		);
		curl_setopt_array(self::$ch, $defaults);
		$ret = trim(curl_exec(self::$ch));

		trace($url, 'GET FROM', "CURL");
		trace("服务器返回内容:[$ret]", "CURL", "CURL");

		if(self::$cache){
			S($cache_id, $ret, self::$cache);
		}
		return $ret;
	}

	public static function error(){
		if(!self::$ch){
			self::__init();
		}
		return curl_error(self::$ch);
	}
}
