<?php
/**
 * 获取对象的方法
 *
 * @author GongT
 */
class ThinkInstance{
	private static $cache = array();
	protected static $ACTION = array();
	protected static $MODEL = array();
	protected static $VIEW = array();

	/**
	 * 取得对象实例 支持调用类的静态方法
	 * @param string $name   对象类名
	 * @param string $method 类的静态方法名
	 * @param array  $args   静态方法名参数
	 *
	 * @return object
	 */
	static public function instance($name, $method = '', $args = array()){
		$identify = empty($args)? $name . $method : $name . $method . md5(serialize($args));
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
	public static function D($name, $arg1 = null, $arg2 = null){
		if(isset(self::$MODEL[$name])){
			return self::$MODEL[$name];
		}
		$class = $name . 'Model';

		/*vvv debug vvv*/
		/*if(!class_exists($class, false)){
			echo "[$class] 模型文件加载成功，但没有定义正确的类型 -- 名称不符？";
			SPT();
		}*/
		/*^^^ debug ^^^*/
		
		return self::$MODEL[$name] = new $class($arg1, $arg2);
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
	public static function M($name = '', $connection = ''){
		static $_model = array();
		if(strpos($name, ':')){
			list($class, $name) = explode(':', $name);
		} else{
			$class = 'Model';
		}
		$guid = $name . '_' . $class;
		if(!isset($_model[$guid])){
			$_model[$guid] = new $class($name, $connection);
		}

		return $_model[$guid];
	}

	/**
	 * 实例化Action
	 *
	 * @param string $name   Action资源地址
	 *
	 * @return Action
	 */
	public static function A($name){
		$name = ucfirst($name) . 'Action';

		if(isset(self::$ACTION[$name])){
			return self::$ACTION[$name];
		}
		$path = apc_fetch('ThinkAction' . $name, $sucess);

		if($sucess){
			require_once $path;
		} else{
			require_one(array(
							 LIB_PATH . 'Action/' . $name . '.php',
							 BASE_LIB_PATH . 'Action/' . $name . '.php',
							 EXTEND_PATH . 'Action/' . $name . '.php'
						), $path);
			apc_store('ThinkAction' . $name, $path);
		}

		if(class_exists($name, false)){
			return self::$ACTION[$name] = new $name();
		} else{
			return self::$ACTION[$name] = false;
		}
	}

	/**
	 *
	 *
	 * @param null|string $id
	 *
	 * @return View
	 * @static
	 */
	public static function View($id = null){
		if(!$id){
			return new View();
		} else{
			if(isset(self::$VIEW[$id])){
				return self::$VIEW[$id];
			} else{
				self::$VIEW[$id] = new View();
			}
		}
	}
}

/**
 * 能被 ThinkInstance 实例化的对象
 *
 * @author ${USER}
 */
interface Factory{
	static public function getInstance($data);
}
