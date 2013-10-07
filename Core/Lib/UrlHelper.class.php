<?php
/**
 * URL生成主类
 *
 * @author ${USER}
 */
class UrlHelper{
	protected $app = APP_NAME;
	protected $action = ACTION_NAME;
	protected $method = METHOD_NAME;
	protected $suffix = DEFAULT_EXTENSION;
	protected $protocol = '';
	protected $param = [];
	protected $paramstr = '';
	protected $path = '';

	protected $urlm;

	/**
	 *
	 */
	public function __construct($urlm = null){
		// <DEBUG>
		if($urlm){
			$this->urlm = $urlm;
		} else{
			// </DEBUG>
			$this->urlm = hidef_load('urlmap');
			// <DEBUG>
		}
		// </DEBUG>
	}

	/**
	 * @param $app
	 */
	public function setApp($app){
		$this->app = $app;
	}

	/**
	 * @param string $action
	 */
	public function setAction($action){
		$this->action = $action;
	}

	/**
	 * @param boolean $protocol
	 */
	public function setProtocol($protocol){
		$this->protocol = $protocol;
	}

	/**
	 * @param string $method
	 */
	public function setMethod($method){
		$this->method = $method;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	public function setParam($name, $value){
		$this->param[$name] = $value;
	}

	/**
	 * @param string &$param
	 */
	public function setParamAll(&$param){
		$this->param = & $param;
	}

	/**
	 * @param string $paramstr
	 */
	public function setParamStr($paramstr){
		$this->paramstr = $paramstr;
	}

	/**
	 * @param string $suffix
	 */
	public function setSuffix($suffix){
		$this->suffix = $suffix;
	}

	/**
	 * @param string|array $path
	 */
	public function setPath($path){
		if(is_array($path)){
			foreach($path as $k => $com){
				if("" == $com){
					$path[$k] = 'null';
				}
			}
			$path = implode(URL_PATHINFO_DEPR, $path);
		}
		$this->path = $path;
	}

	public function reset(){
		$this->app      = APP_NAME;
		$this->action   = ACTION_NAME;
		$this->method   = METHOD_NAME;
		$this->suffix   = DEFAULT_EXTENSION;
		$this->protocol = 'http';
		$this->param    = [];
		$this->paramstr = '';
		$this->path     = '';
	}

	/**
	 * @return string
	 */
	public function getUrl(){
		/* A://B */
		// <DEBUG>
		if(!isset($this->urlm[$this->app])){
			Think::halt('URL_MAP[' . $this->app . '] -- 定义有误(BASE_CONF_PATH/urlmap.php)');
		}
		// </DEBUG>
		$domain   = $this->urlm[$this->app];
		$protocol = $this->protocol;
		if($protocol){
			$perfix = $protocol . '://' . $domain;
		} elseif($domain != $_SERVER['HTTP_HOST']){
			$perfix = '//' . $domain;
		} else{
			$perfix = '';
		}

		/* /action/method/path/to */
		$params = $this->param;
		$path   = $this->action . URL_PATHINFO_DEPR . $this->method;
		if($this->path){
			$path .= URL_PATHINFO_DEPR . $this->path;
		}

		$url = $perfix . URL_PATHINFO_DEPR . $path . '.' . $this->suffix;
		if(empty($params)){
			if($this->paramstr){
				$url .= '?' . $this->paramstr;
			}
		} else{
			$this->paramstr .= $this->paramstr? '&' : '';
			foreach($params as $k=>$v){
				if($v{0} != '{'){
					$params[$k]=urlencode($v);
				}
			}
			$url .= '?' . $this->paramstr . dbl_implode('&','=',$params);
		}

		return $url;
	}

	/**
	 * 根 getUrl 作用一样
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->getUrl(false);
	}
}
