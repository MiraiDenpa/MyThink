<?php
/**
 * 获取对象的方法
 *
 * @author GongT
 */
class ThinkInstance{
	private static $cache = array();

	/**
	 * 取得对象实例 支持调用类的静态方法
	 * @param string $name   对象类名
	 * @param string $method 类的静态方法名
	 * @param array  $args   静态方法名参数
	 *
	 * @return object
	 */
	static public function instance($name, $method = '', $args = array()){
		$identify = empty($args)? $name . $method : $name . $method . to_guid_string($args);
		if(!isset(self::$cache[$identify])){
			if(class_exists($name)){
				$o = new $name();
				if(method_exists($o, $method)){
					if(!empty($args)){
						self::$cache[$identify] = call_user_func_array(array(&$o, $method), $args);
					} else{
						self::$cache[$identify] = $o->$method();
					}
				} else{
					self::$cache[$identify] = $o;
				}
			} else{
				Think::halt(LANG_CLASS_NOT_EXIST . ':' . $name);
			}
		}

		return self::$cache[$identify];
	}

	/**
	 * D函数用于实例化Model 格式 项目://分组/模块
	 *
	 * @param string $name  Model资源地址
	 * @param string $layer 业务层名称
	 *
	 * @return Model
	 */
	function D($name = '', $layer = ''){
		static $_model = array();
		$layer = $layer? $layer : DEFAULT_M_LAYER;
		if(strpos($name, '://')){ // 指定项目
			list($app) = explode('://', $name);
			$name = str_replace('://', '/' . $layer . '/', $name);
		} else{
			$app  = DEFAULT_APP;
			$name = $app . '/' . $layer . '/' . $name;
		}
		if(isset($_model[$name])){
			return $_model[$name];
		}
		$path = explode('/', $name);
		if($list = EXTEND_GROUP_LIST && isset($list[$app])){ // 扩展分组
			$baseUrl = $list[$app];
			import($path[2] . '/' . $path[1] . '/' . $path[3] . $layer, $baseUrl);
		} elseif(count($path) > 3 && 1 == APP_GROUP_MODE){ // 独立分组
			$baseUrl =
					$path[0] == '@'? dirname(BASE_LIB_PATH) : APP_PATH . '../' . $path[0] . '/' . APP_GROUP_PATH . '/';
			import($path[2] . '/' . $path[1] . '/' . $path[3] . $layer, $baseUrl);
		} else{
			import($name . $layer);
		}
		$class = basename($name . $layer);
		if(class_exists($class)){
			$model = new $class(basename($name));
		} else{
			halt('Model不存在：' . $class);
		}
		$_model[$name] = $model;

		return $model;
	}

	/**
	 * M函数用于实例化一个没有模型文件的Model
	 *
	 * @param string $name        Model名称 支持指定基础模型 例如 MongoModel:User
	 * @param string $tablePrefix 表前缀
	 * @param mixed  $connection  数据库连接信息
	 *
	 * @return Model
	 */
	function M($name = '', $tablePrefix = '', $connection = ''){
		static $_model = array();
		if(strpos($name, ':')){
			list($class, $name) = explode(':', $name);
		} else{
			$class = 'Model';
		}
		$guid = $tablePrefix . $name . '_' . $class;
		if(!isset($_model[$guid])){
			$_model[$guid] = new $class($name, $tablePrefix, $connection);
		}

		return $_model[$guid];
	}

	/**
	 * A函数用于实例化Action 格式：[项目://][分组/]模块
	 *
	 * @param string  $name   Action资源地址
	 * @param string  $layer  控制层名称
	 * @param boolean $common 是否公共目录
	 *
	 * @return Action|false
	 */
	function A($name, $layer = '', $common = false){
		static $_action = array();
		$layer = $layer? $layer : DEFAULT_C_LAYER;
		if(strpos($name, '://')){ // 指定项目
			list($app) = explode('://', $name);
			$name = str_replace('://', '/' . $layer . '/', $name);
		} else{
			$app  = '@';
			$name = '@/' . $layer . '/' . $name;
		}
		if(isset($_action[$name])){
			return $_action[$name];
		}
		$path = explode('/', $name);
		if($list = EXTEND_GROUP_LIST && isset($list[$app])){ // 扩展分组
			$baseUrl = $list[$app];
			import($path[2] . '/' . $path[1] . '/' . $path[3] . $layer, $baseUrl);
		} elseif(count($path) > 3 && 1 == APP_GROUP_MODE){ // 独立分组
			$baseUrl =
					$path[0] == '@'? dirname(BASE_LIB_PATH) : APP_PATH . '../' . $path[0] . '/' . APP_GROUP_PATH . '/';
			import($path[2] . '/' . $path[1] . '/' . $path[3] . $layer, $baseUrl);
		} elseif($common){ // 加载公共类库目录
			import(str_replace('@/', '', $name) . $layer, LIB_PATH);
		} else{
			import($name . $layer);
		}
		$class = basename($name . $layer);
		if(class_exists($class, false)){
			$action         = new $class();
			$_action[$name] = $action;

			return $action;
		} else{
			return false;
		}
	}
}

/**
 * 能被 ThinkInstance 实例化的对象
 *
 * @author ${USER}
 */
interface Factory{
	static public function  getInstance($data);
}
