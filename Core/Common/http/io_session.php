<?php
/**
 * session管理函数
 *
 * @param string|array $name  session名称 如果为数组则表示进行session设置
 * @param mixed        $value session值
 *
 * @return mixed
 */
function session($name, $value = ''){
	if(session_status() !== PHP_SESSION_ACTIVE){
		session_start();
	}
	if($name{0} == '['){ // session 操作
		if('[pause]' == $name){ // 暂停session
			session_write_close();
		} elseif('[start]' == $name){ // 启动session
			session_start();
		} elseif('[destroy]' == $name){ // 销毁session
			$_SESSION = array();
			session_unset();
			session_destroy();
		} elseif('[regenerate]' == $name){ // 重新生成id
			session_regenerate_id();
		} else{
			Think::halt('session操作错误: ' . $name);
		}
	}

	if('' === $value){
		if(0 === strpos($name, '?')){ // 检查session
			$name = substr($name, 1);
			if(strpos($name, '.')){ // 支持数组
				list($name1, $name2) = explode('.', $name);
				return isset($_SESSION[$name1][$name2]);
			} else{
				return isset($_SESSION[$name]);
			}
		} else{
			if(strpos($name, '.')){
				list($name1, $name2) = explode('.', $name);
				return isset($_SESSION[$name1][$name2])? $_SESSION[$name1][$name2] : null;
			} else{
				return isset($_SESSION[$name])? $_SESSION[$name] : null;
			}
		}
	} elseif(is_null($value)){ // 删除session
		unset($_SESSION[$name]);
	} else{ // 设置session
		$_SESSION[$name] = $value;
	}
}
