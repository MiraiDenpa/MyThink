<?php
/**
 * session管理函数
 * @param string|array $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name,$value='') {
	$prefix   =  SESSION_PREFIX;
	if(is_array($name)) { // session初始化 在session_start 之前调用
		if(isset($name['prefix'])) C('SESSION_PREFIX',$name['prefix']);
		if(VAR_SESSION_ID && isset($_REQUEST[VAR_SESSION_ID])){
			session_id($_REQUEST[VAR_SESSION_ID]);
		}elseif(isset($name['id'])) {
			session_id($name['id']);
		}
		ini_set('session.auto_start', 0);
		if(isset($name['name']))            session_name($name['name']);
		if(isset($name['path']))            session_save_path($name['path']);
		if(isset($name['domain']))          ini_set('session.cookie_domain', $name['domain']);
		if(isset($name['expire']))          ini_set('session.gc_maxlifetime', $name['expire']);
		if(isset($name['use_trans_sid']))   ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
		if(isset($name['use_cookies']))     ini_set('session.use_cookies', $name['use_cookies']?1:0);
		if(isset($name['cache_limiter']))   session_cache_limiter($name['cache_limiter']);
		if(isset($name['cache_expire']))    session_cache_expire($name['cache_expire']);
		if(isset($name['type']))            C('SESSION_TYPE',$name['type']);
		if(SESSION_TYPE) { // 读取session驱动
			$class      = 'Session'. ucwords(strtolower(SESSION_TYPE));
			// 检查驱动类
			if(require_cache(EXTEND_PATH.'Driver/Session/'.$class.'.class.php')) {
				$hander = new $class();
				$hander->execute();
			}else {
				// 类没有定义
				throw_exception(L('_CLASS_NOT_EXIST_').': ' . $class);
			}
		}
		// 启动session
		if(SESSION_AUTO_START)  session_start();
	}elseif('' === $value){
		if(0===strpos($name,'[')) { // session 操作
			if('[pause]'==$name){ // 暂停session
				session_write_close();
			}elseif('[start]'==$name){ // 启动session
				session_start();
			}elseif('[destroy]'==$name){ // 销毁session
				$_SESSION =  array();
				session_unset();
				session_destroy();
			}elseif('[regenerate]'==$name){ // 重新生成id
				session_regenerate_id();
			}
		}elseif(0===strpos($name,'?')){ // 检查session
			$name   =  substr($name,1);
			if(strpos($name,'.')){ // 支持数组
				list($name1,$name2) =   explode('.',$name);
				return $prefix?isset($_SESSION[$prefix][$name1][$name2]):isset($_SESSION[$name1][$name2]);
			}else{
				return $prefix?isset($_SESSION[$prefix][$name]):isset($_SESSION[$name]);
			}
		}elseif(is_null($name)){ // 清空session
			if($prefix) {
				unset($_SESSION[$prefix]);
			}else{
				$_SESSION = array();
			}
		}elseif($prefix){ // 获取session
			if(strpos($name,'.')){
				list($name1,$name2) =   explode('.',$name);
				return isset($_SESSION[$prefix][$name1][$name2])?$_SESSION[$prefix][$name1][$name2]:null;
			}else{
				return isset($_SESSION[$prefix][$name])?$_SESSION[$prefix][$name]:null;
			}
		}else{
			if(strpos($name,'.')){
				list($name1,$name2) =   explode('.',$name);
				return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;
			}else{
				return isset($_SESSION[$name])?$_SESSION[$name]:null;
			}
		}
	}elseif(is_null($value)){ // 删除session
		if($prefix){
			unset($_SESSION[$prefix][$name]);
		}else{
			unset($_SESSION[$name]);
		}
	}else{ // 设置session
		if($prefix){
			if (!is_array($_SESSION[$prefix])) {
				$_SESSION[$prefix] = array();
			}
			$_SESSION[$prefix][$name]   =  $value;
		}else{
			$_SESSION[$name]  =  $value;
		}
	}
}
