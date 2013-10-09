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
	protected static $TAGLIB = array();

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
	 * @param string $arg1 参数1
	 * @param string $arg2 参数2
	 *
	 * @return Model
	 */
	public static function &D($name, $arg1 = null, $arg2 = null){
		$cid = $name . ($arg1? md5(var_export($arg1,true) . var_export($arg2,true)) : '');
		if(isset(self::$MODEL[$cid])){
			return self::$MODEL[$cid];
		}
		$class = $name . 'Model';
		/* <DEBUG>
		if(!class_exists($class, false)){
			echo "[$class] 模型文件加载成功，但没有定义正确的类型 -- 名称不符？";
			SPT();
		}
		</DEBUG>*/
		self::$MODEL[$cid] = new $class($arg1, $arg2);
		return self::$MODEL[$cid];
	}

	public static function &Db($config){
		static $c = [];
		if(isset($c[$config])){
			return $c[$config];
		}
		$c[$config] = Db::factory($config);
		return $c[$config];
	}

	/**
	 * 实例化Action
	 *
	 * @param string     $name    Action资源地址
	 * @param Dispatcher $param   Action初始化参数（当前action的meta）
	 *
	 * @return Action
	 */
	public static function &A($name, Dispatcher &$param){
		$name .= 'Action';

		if(!isset(self::$ACTION[$name])){
			if(class_exists($name, true)){
				self::$ACTION[$name] = new $name($param);
			} else{
				self::$ACTION[$name] = false;
			}
		}
		return self::$ACTION[$name];
	}

	/**
	 *
	 *
	 * @param null|string $id
	 *
	 * @return View
	 * @static
	 */
	public static function &View($id = null){
		if(!$id){
			$e = new View();
			return $e;
		} else{
			if(!isset(self::$VIEW[$id])){
				self::$VIEW[$id] = new View();
			}
		}
		return self::$VIEW[$id];
	}

	public static function &ThinkTemplate(){
		static $cache = null;
		if($cache){
			return $cache;
		}
		$cache = new ThinkTemplate();
		return $cache;
	}

	public static function &TagLib($tagLib){
		$class = 'TagLib' . ucwords($tagLib);
		if(isset(self::$TAGLIB[$class])){
			return self::$TAGLIB[$class];
		}
		self::$TAGLIB[$class] = new $class;
		return self::$TAGLIB[$class];
	}

	/**
	 * @return UrlHelper
	 * @static
	 */
	public static function &UrlHelper(){
		static $c;
		if($c){
			return $c;
		}
		$c = new UrlHelper();
		return $c;
	}

	public static function &InStream($type){
		return InputStream::_getInstance($type);
	}

	public static function &OutStream($arg){
		/*$factory = [ucfirst($type).'Stream','_getInstance'];

		return $factory($arg);*/
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
