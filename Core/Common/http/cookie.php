<?php


/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 */
function cookie($name, $value='', $option=null) {
	// 默认设置
	$config = array(
		'prefix'    =>  COOKIE_PREFIX, // cookie 名称前缀
		'expire'    =>  COOKIE_EXPIRE, // cookie 保存时间
		'path'      =>  COOKIE_PATH, // cookie 保存路径
		'domain'    =>  COOKIE_DOMAIN, // cookie 有效域名
	);
	// 参数设置(会覆盖黙认设置)
	if (!is_null($option)) {
		if (is_numeric($option))
			$option = array('expire' => $option);
		elseif (is_string($option))
			parse_str($option, $option);
		$config     = array_merge($config, array_change_key_case($option));
	}
	// 清除指定前缀的所有cookie
	if (is_null($name)) {
		if (empty($_COOKIE))
			return;
		// 要删除的cookie前缀，不指定则删除config设置的指定前缀
		$prefix = empty($value) ? $config['prefix'] : $value;
		if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
			foreach ($_COOKIE as $key => $val) {
				if (0 === stripos($key, $prefix)) {
					setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
					unset($_COOKIE[$key]);
				}
			}
		}
		return;
	}
	$name = $config['prefix'] . $name;
	if ('' === $value) {
		if(isset($_COOKIE[$name])){
			$value =    $_COOKIE[$name];
			if(0===strpos($value,'think:')){
				$value  =   substr($value,6);
				return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
			}else{
				return $value;
			}
		}else{
			return null;
		}
	} else {
		if (is_null($value)) {
			setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
			unset($_COOKIE[$name]); // 删除指定cookie
		} else {
			// 设置cookie
			if(is_array($value)){
				$value  = 'think:'.json_encode(array_map('urlencode',$value));
			}
			$expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
			setcookie($name, $value, $expire, $config['path'], $config['domain']);
			$_COOKIE[$name] = $value;
		}
	}
}
