<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP Model模型类
 * 实现了ORM和ActiveRecords模式
 * @category    Think
 * @package     Think
 * @subpackage  Core
 * @author      liu21st <liu21st@gmail.com>
 */
class Model{
	// 操作状态
	const MODEL_INSERT = 1; //  插入模型数据
	const MODEL_UPDATE = 2; //  更新模型数据
	const MODEL_BOTH = 3; //  包含上面两种方式
	const MUST_VALIDATE = 1; // 必须验证
	const EXISTS_VALIDATE = 0; // 表单存在字段则验证
	const VALUE_VALIDATE = 2; // 表单值不为空则验证
	// 当前使用的扩展模型
	private $_extModel = null;
	/** @var DbPdo */
	protected $db = null;
	// 主键名称
	protected $pk = 'id';
	// 模型名称
	protected $name = '';
	// 数据库名称
	protected $dbName = '';
	//数据库配置
	protected $connection = 'default';
	// 数据表名（不包含表前缀）
	protected $tableName = '';
	// 实际数据表名（包含表前缀）
	protected $trueTableName = '';
	// 最近错误信息
	protected $error = '';
	protected $errorCode = '';
	// 字段信息
	protected $fields = array();
	// 数据信息
	protected $data = array();
	// 查询表达式参数
	protected $options = array();
	protected $_validate = array(); // 自动验证定义
	protected $_auto = array(); // 自动完成定义
	protected $_map = array(); // 字段映射定义
	protected $_scope = array(); // 命名范围定义
	// 是否自动检测数据表字段信息
	protected $autoCheckFields = true;
	// 是否批处理验证
	protected $patchValidate = false;
	// 链操作方法列表
	protected $methods = array(
		'table',
		'order',
		'alias',
		'having',
		'group',
		'lock',
		'distinct',
		'auto',
		'filter',
		'validate',
		'result',
		'bind',
		'token',
		'page'
	);
	// 回调函数名称列表
	protected $callback = array(
		'create', // 调用create后（数据对象，x）
		'after_db', // 切换完数据库链接 （DbPdo对象，x）
		'options_filter', // 过滤options（options，x） 发生在where调用，带数据调用add、save，调用buildSql
		'data_filter', // 过滤data （data，x） 发生在data调用，带数据调用add、save
		
		// 操作前回调（紧邻实际操作，在其他before回调后），参数是options。没有第二参数。不能取消操作
		'before_read',
		'before_write', 
		
		// 操作准备回调，参数是options。没有第二参数。返回false将取消本次操作并返回 null
		'before_insert',
		'before_insert_all',
		'before_update',
		'before_delete',
		'before_find',
		'before_select',
		
		// 结果回调，第一个参数是执行结果，可以进行修改，返回修改后的值。第二个是options。
		'after_insert',
		'after_insert_all',
		'after_update',
		'after_delete',
		'after_find',
		'after_select'
	);
	/** @var Page 分页类对象 */
	protected $page = null;
	protected $perPage = 20;
	protected $page_not_init = true;

	protected $cache_cas = 'sql';

	/**
	 * 取得DB类的实例对象 字段检查
	 * @param $arg1
	 * @param $arg2
	 */
	public function __construct($arg1, $arg2){
		if(func_num_args() > 2){
			Think::halt('MODEL参数错误：多于2个 (' . func_num_args() . ').');
		}
		// 模型初始化
		$this->_initialize($arg1, $arg2);
		// 获取模型名称
		$this->getModelName();

		// 数据库初始化操作
		// 获取数据库操作对象
		// 当前模型有独立的数据库连接信息
		$this->db(0, $this->connection);
	}

	/**  */
	public function _initialize($arg1, $arg2){
	}

	/**
	 * 自动检测数据表信息
	 * @access protected
	 * @return void
	 */
	protected function _checkTableInfo(){
		// 如果不是Model类 自动记录数据表信息
		// 只在第一次执行记录
		if(empty($this->fields)){
			// 如果数据表字段没有定义则自动获取
			if(DB_FIELDS_CACHE){
				$db     = $this->dbName? $this->dbName : DB_NAME;
				$fields = S('ThinkDbField' . strtolower($db . '.' . $this->name));
				if($fields){
					$this->fields = $fields;

					return;
				}
			}
			// 每次都会读取数据表信息
			$this->flush();
		}
	}

	/**
	 * 获取字段信息并缓存
	 * @access public
	 * @return void
	 */
	public function flush(){
		// 缓存不存在则查询数据表信息
		$this->db->setModel($this->name);
		$fields = $this->db->getFields($this->getTableName());
		if(!$fields){ // 无法获取字段信息
			return;
		}
		$this->fields             = array_keys($fields);
		$this->fields['_autoinc'] = false;
		$type                     = array();
		foreach($fields as $key => $val){
			// 记录字段类型
			$type[$key] = $val['type'];
			if($val['primary']){
				$this->fields['_pk'] = $key;
				if($val['autoinc']){
					$this->fields['_autoinc'] = true;
				}
			}
		}
		// 记录字段类型信息
		$this->fields['_type'] = $type;

		// 永久缓存数据表信息
		$db = $this->dbName? $this->dbName : DB_NAME;
		S('ThinkDbField' . strtolower($db . '.' . $this->name), $this->fields);
	}

	/**
	 * 动态切换扩展模型
	 * @access public
	 *
	 * @param string $type 模型类型名称
	 * @param mixed  $vars 要传入扩展模型的属性变量
	 *
	 * @return Model
	 */
	public function switchModel($type, $vars = array()){
		$class = ucwords(strtolower($type)) . 'Model';
		if(!class_exists($class)){
			throw_exception($class . L('_MODEL_NOT_EXIST_'));
		}
		// 实例化扩展模型
		$this->_extModel = new $class($this->name);
		if(!empty($vars)){
			// 传入当前模型的属性到扩展模型
			foreach($vars as $var){
				$this->_extModel->setProperty($var, $this->$var);
			}
		}

		return $this->_extModel;
	}

	/**
	 * 设置数据对象的值
	 * @access public
	 *
	 * @param string $name  名称
	 * @param mixed  $value 值
	 *
	 * @return void
	 */
	public function __set($name, $value){
		// 设置数据对象属性
		$this->data[$name] = $value;
	}

	/**
	 * 获取数据对象的值
	 * @access public
	 *
	 * @param string $name 名称
	 *
	 * @return mixed
	 */
	public function __get($name){
		return isset($this->data[$name])? $this->data[$name] : null;
	}

	/**
	 * 检测数据对象的值
	 * @access public
	 *
	 * @param string $name 名称
	 *
	 * @return boolean
	 */
	public function __isset($name){
		return isset($this->data[$name]);
	}

	/**
	 * 销毁数据对象的值
	 * @access public
	 *
	 * @param string $name 名称
	 *
	 * @return void
	 */
	public function __unset($name){
		unset($this->data[$name]);
	}

	/**
	 * 利用__call方法实现一些特殊的Model方法
	 * @access public
	 *
	 * @param string $method 方法名称
	 * @param array  $args   调用参数
	 *
	 * @return mixed
	 */
	public function __call($method, $args){
		if(in_array(strtolower($method), $this->methods, true)){
			// 连贯操作的实现
			$this->options[strtolower($method)] = $args[0];

			return $this;
		} elseif(in_array(strtolower($method), ['count', 'sum', 'min', 'max', 'avg'], true)){
			// 统计查询的实现
			$field = isset($args[0])? $args[0] : '*';
			$ret   = $this->getField(strtoupper($method) . '(' . $field . ') AS tp_' . $method);
			return strpos($ret, ',')? floatval($ret) : intval($ret);
		} elseif(strtolower(substr($method, 0, 5)) == 'getby'){
			// 根据某个字段获取记录
			$field         = parse_name(substr($method, 5));
			$where[$field] = $args[0];

			return $this
					->where($where)
					->find();
		} elseif(strtolower(substr($method, 0, 10)) == 'getfieldby'){
			// 根据某个字段获取记录的某个值
			$name         = parse_name(substr($method, 10));
			$where[$name] = $args[0];

			return $this
					->where($where)
					->getField($args[1]);
		} elseif(isset($this->_scope[$method])){ // 命名范围的单独调用支持
			return $this->scope($method, $args[0]);
		} else{
			$class = get_class($this);
			Think::halt(xdebug_filepath_anchor(find_one([
														LIB_PATH . 'Model/' . $class . '.php',
														BASE_LIB_PATH . 'Model/' . $file . '.php',
														EXTEND_PATH . 'Model/' . $file . '.php'
														]
											   ),
											   0,
											   $class . ':' . $method
						) . LANG_METHOD_NOT_EXIST,
						true
			);
			return null;
		}
	}

	/**
	 * 对保存到数据库的数据进行处理
	 * @access protected
	 *
	 * @param mixed $data 要操作的数据
	 *
	 * @return boolean
	 */
	protected function _facade($data){
		if(isset($data['$pk'])){
			$data[$this->pk] = $data['$pk'];
			unset($data['$pk']);
		}
		// 检查非数据字段
		if(!empty($this->fields)){
			foreach($data as $key => $val){
				if(!in_array($key, $this->fields, true)){
					unset($data[$key]);
				} elseif(is_scalar($val)){
					// 字段类型检查
					$this->_parseType($data, $key);
				}
			}
		}
		// 安全过滤
		if(!empty($this->options['filter'])){
			$data = array_map($this->options['filter'], $data);
			unset($this->options['filter']);
		}
		if(false === $this->doCallback('data_filter', $data, $data)){
			return false;
		}

		return $data;
	}

	/**
	 * 新增数据
	 * @access public
	 *
	 * @param mixed   $data    数据
	 * @param array   $options 表达式
	 * @param boolean $replace 是否replace
	 *
	 * @return mixed
	 */
	public function add($data = '', $options = array(), $replace = false){
		if(empty($data)){
			// 没有传递数据，获取当前数据对象的值
			if(!empty($this->data)){
				$data = $this->data;
				// 重置数据
				$this->data = array();
			} else{
				$this->error = L('_DATA_TYPE_INVALID_');

				return false;
			}
		}
		// 分析表达式
		$options = $this->_parseOptions($options);
		// 数据处理
		$data = $this->_facade($data);
		if(false === $this->doCallback('before_insert', $data, $options)){
			return false;
		}
		$this->doCallback('before_write', $data, $options);
		// 写入数据到数据库
		$result = $this->db->insert($data, $options, $replace);
		if(false !== $result){
			$insertId = $this->getLastInsID();
			if($insertId){
				// 自增主键返回插入ID
				$data[$this->getPk()] = $insertId;
				$this->doCallback('after_insert', $data, $options);

				return $insertId;
			}
			$this->doCallback('after_insert', $data, $options);
		}

		return $result;
	}

	/**
	 * 添加一组数据
	 *
	 * @param array $dataList
	 * @param array $options
	 * @param bool  $replace
	 *
	 * @return bool|string
	 */
	public function addAll($dataList, $options = array(), $replace = false){
		if(empty($dataList)){
			$this->error = L('_DATA_TYPE_INVALID_');

			return false;
		}
		// 分析表达式
		$options = $this->_parseOptions($options);
		// 数据处理
		foreach($dataList as $key => $data){
			$dataList[$key] = $this->_facade($data);
		}
		if(false === $this->doCallback('before_insert_all', $dataList, $options)){
			return false;
		}
		$this->doCallback('before_write', $dataList, $options);
		// 写入数据到数据库
		$result = $this->db->insertAll($dataList, $options, $replace);
		if(false !== $result){
			$insertId = $this->getLastInsID();
			if($insertId){
				return $insertId;
			}
		}
		$this->doCallback('after_insert_all', $result, $options);
		return $result;
	}

	/**
	 * 清除这个对象的缓存
	 *
	 * @return void
	 */
	function clear(){
		SAS($this->cache_cas, null);
	}

	/**
	 * 保存数据
	 * @access public
	 *
	 * @param mixed $data    数据
	 * @param array $options 表达式
	 *
	 * @return boolean
	 */
	public function save($data = '', $options = array()){
		if(empty($data)){
			// 没有传递数据，获取当前数据对象的值
			if(!empty($this->data)){
				$data = $this->data;
				// 重置数据
				$this->data = array();
			} else{
				$this->error = L('_DATA_TYPE_INVALID_');

				return false;
			}
		}
		// 数据处理
		$data = $this->_facade($data);
		// 分析表达式
		$options = $this->_parseOptions($options);
		$pk      = $this->getPk();
		if(!isset($options['where'])){
			// 如果存在主键数据 则自动作为更新条件
			if(isset($data[$pk])){
				$where[$pk]       = $data[$pk];
				$options['where'] = $where;
				unset($data[$pk]);
			} else{
				// 如果没有任何更新条件则不执行
				$this->error = LANG_OPERATION_WRONG;
				return false;
			}
		}
		if(is_array($options['where']) && isset($options['where'][$pk])){
			$pkValue = $options['where'][$pk];
		}
		if(false === $this->doCallback('before_update', $data, $options)){
			return false;
		}
		$this->doCallback('before_write', $data, $options);
		$result = $this->db->update($data, $options);
		if(false !== $result){
			if(isset($pkValue)){
				$data[$pk] = $pkValue;
			}
			$this->doCallback('after_update', $result, $options);
		}

		return $result;
	}

	/**
	 * 删除数据
	 * @access public
	 *
	 * @param mixed $options 表达式
	 *
	 * @return mixed
	 */
	public function delete($options = array()){
		if(empty($options) && empty($this->options['where'])){
			// 如果删除条件为空 则删除当前数据对象所对应的记录
			if(!empty($this->data) && isset($this->data[$this->getPk()])){
				return $this->delete($this->data[$this->getPk()]);
			} else{
				// 当前数据对象也空，错误
				$this->error = 'Empty where delete.';
				return false;
			}
		}
		$pk = $this->getPk();
		if(is_numeric($options) || is_string($options)){
			// 根据主键删除记录
			if(strpos($options, ',')){
				$where[$pk] = array('IN', $options);
			} else{
				$where[$pk] = $options;
			}
			$options          = array();
			$options['where'] = $where;
		}
		// 分析表达式
		$options = $this->_parseOptions($options);
		if(is_array($options['where']) && isset($options['where'][$pk])){
			$pkValue = $options['where'][$pk];
		}
		if(empty($options['where'])){
			Think::fail_error(ERR_DELETE_TABEL);
		}
		if(false === $this->doCallback('before_delete', $options, $options)){
			return false;
		}
		
		$this->doCallback('before_write', $options, $options);
		$result = $this->db->delete($options);
		if(false !== $result){
			$data = array();
			if(isset($pkValue)){
				$data[$pk] = $pkValue;
			}
			$this->doCallback('after_delete', $result, $options);
		}

		// 返回删除记录个数
		return $result;
	}

	/**
	 * 查询数据集
	 * @access public
	 *
	 * @param array $options 表达式参数
	 *
	 * @return mixed
	 */
	public function select($options = array()){
		if(is_string($options) || is_numeric($options)){
			// 根据主键查询
			$pk = $this->getPk();
			if(strpos($options, ',')){
				$where[$pk] = array('IN', $options);
			} else{
				$where[$pk] = $options;
			}
			$options          = array();
			$options['where'] = $where;
		} elseif(false === $options){ // 用于子查询 不查询只返回SQL
			$options = array();
			// 分析表达式
			$options = $this->_parseOptions($options);

			return '( ' . $this->db->buildSelectSql($options) . ' )';
		}
		// 分析表达式
		$options = $this->_parseOptions($options);
		if(false === $this->doCallback('before_select', $options, $options)){
			return false;
		}
		$this->doCallback('before_read', $options, $options);
		$resultSet = $this->db->select($options);
		if(false === $resultSet){
			return false;
		}
		if(empty($resultSet)){ // 查询结果为空
			return null;
		}
		$this->doCallback('after_select', $resultSet, $options);

		return $resultSet;
	}

	/**
	 * 生成查询SQL 可用于子查询
	 * @access public
	 *
	 * @param array $options 表达式参数
	 *
	 * @return string
	 */
	public function buildSql($options = array()){
		// 分析表达式
		$options = $this->_parseOptions($options);

		return '( ' . $this->db->buildSelectSql($options) . ' )';
	}

	/**
	 * 分析表达式
	 * @access protected
	 *
	 * @param array $options 表达式参数
	 *
	 * @return array
	 */
	protected function _parseOptions($options = array()){
		if(is_array($options)){
			$options = array_merge($this->options, $options);
		}
		// 查询过后清空sql表达式组装 避免影响下次查询
		$this->options = array();
		if(!isset($options['table'])){
			// 自动获取表名
			$options['table'] = $this->getTableName();
			$fields           = $this->fields;
		} else{
			// 指定数据表 则重新获取字段列表 但不支持类型检测
			$fields = $this->getDbFields();
		}

		if(!empty($options['alias'])){
			$options['table'] .= ' ' . $options['alias'];
		}
		// 记录操作的模型名称
		$options['model'] = $this->name;

		// 字段类型验证
		if(isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])){
			// 对数组查询条件进行字段类型检查
			foreach($options['where'] as $key => $val){
				$key = trim($key);
				if(in_array($key, $fields, true)){
					if(is_scalar($val)){
						$this->_parseType($options['where'], $key);
					}
				} elseif(!is_numeric($key) && '_' != substr($key, 0, 1) && false === strpos($key, '.') &&
						 false === strpos($key, '(') && false === strpos($key, '|') && false === strpos($key, '&')
				){
					unset($options['where'][$key]);
				}
			}
		}

		// 表达式过滤
		$this->doCallback('options_filter', $options, $options);

		return $options;
	}

	/**
	 * 数据类型检测
	 * @access protected
	 *
	 * @param mixed  $data 数据
	 * @param string $key  字段名
	 *
	 * @return void
	 */
	protected function _parseType(&$data, $key){
		if(empty($this->options['bind'][':' . $key])){
			$fieldType = strtolower($this->fields['_type'][$key]);
			if(false !== strpos($fieldType, 'enum')){
				// 支持ENUM类型优先检测
			} elseif(false === strpos($fieldType, 'bigint') && false !== strpos($fieldType, 'int')){
				$data[$key] = intval($data[$key]);
			} elseif(false !== strpos($fieldType, 'float') || false !== strpos($fieldType, 'double')){
				$data[$key] = floatval($data[$key]);
			} elseif(false !== strpos($fieldType, 'bool')){
				$data[$key] = (bool)$data[$key];
			}
		}
	}

	/**
	 * 查询数据
	 * @access public
	 *
	 * @param mixed $options 表达式参数
	 *
	 * @return mixed
	 */
	public function find($options = array()){
		if(is_numeric($options) || is_string($options)){
			$where[$this->getPk()] = $options;
			$options               = array();
			$options['where']      = $where;
		}
		// 总是查找一条记录
		$options['limit'] = 1;
		// 分析表达式
		$options = $this->_parseOptions($options);
		if(false === $this->doCallback('before_find', $options, $options)){
			return false;
		}
		$this->doCallback('before_read', $options, $options);
		$resultSet = $this->db->select($options);
		if(false === $resultSet){
			return false;
		}
		if(empty($resultSet)){ // 查询结果为空
			return null;
		}
		$this->data = $resultSet[0];
		$this->doCallback('after_find', $resultSet, $options);

		/*if(!empty($this->options['result'])) {
			return $this->returnResult($this->data,$this->options['result']);
		}*/

		return $this->data;
	}

	/*protected function returnResult($data,$type=''){
        if ($type){
            if(is_callable($type)){
                return call_user_func($type,$data);
            }
            switch (strtolower($type)){
                case 'json':
                    return json_encode($data);
                case 'xml':
                    return xml_encode($data);
            }
        }
        return $data;
    }*/

	/**
	 * 处理字段映射
	 * @access public
	 *
	 * @param array   $data 当前数据
	 * @param integer $type 类型 0 写入 1 读取
	 *
	 * @return array
	 */
	public function parseFieldsMap($data, $type = 1){
		// 检查字段映射
		if(!empty($this->_map)){
			foreach($this->_map as $key => $val){
				if($type == 1){ // 读取
					if(isset($data[$val])){
						$data[$key] = $data[$val];
						unset($data[$val]);
					}
				} else{
					if(isset($data[$key])){
						$data[$val] = $data[$key];
						unset($data[$key]);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * 设置记录的某个字段值
	 * 支持使用数据库字段和方法
	 * @access public
	 *
	 * @param string|array $field  字段名
	 * @param string       $value  字段值
	 *
	 * @return boolean
	 */
	public function setField($field, $value = ''){
		if(is_array($field)){
			$data = $field;
		} else{
			$data[$field] = $value;
		}

		return $this->save($data);
	}

	/**
	 * 字段值增长
	 * @access public
	 *
	 * @param string  $field  字段名
	 * @param integer $step   增长值
	 *
	 * @return boolean
	 */
	public function setInc($field, $step = 1){
		return $this->setField($field, array('exp', $field . '+' . $step));
	}

	/**
	 * 字段值减少
	 * @access public
	 *
	 * @param string  $field  字段名
	 * @param integer $step   减少值
	 *
	 * @return boolean
	 */
	public function setDec($field, $step = 1){
		return $this->setField($field, array('exp', $field . '-' . $step));
	}

	/**
	 * 获取一条记录的某个字段值
	 * @access   public
	 *
	 * @param string      $field  字段名
	 * @param null|string $sepa   字段数据间隔符号 NULL返回数组
	 *
	 * @return mixed
	 */
	public function getField($field, $sepa = null){
		$options['field'] = $field;
		$options          = $this->_parseOptions($options);
		$field            = trim($field);
		if(strpos($field, ',')){ // 多字段
			if(!isset($options['limit'])){
				$options['limit'] = is_numeric($sepa)? $sepa : '';
			}
			$this->doCallback('before_read', $options, $options);
			$resultSet = $this->db->select($options);
			if(!empty($resultSet)){
				$_field = explode(',', $field);
				$field  = array_keys($resultSet[0]);
				$key    = array_shift($field);
				$key2   = array_shift($field);
				$cols   = array();
				$count  = count($_field);
				foreach($resultSet as $result){
					$name = $result[$key];
					if(2 == $count){
						$cols[$name] = $result[$key2];
					} else{
						$cols[$name] = is_string($sepa)? implode($sepa, $result) : $result;
					}
				}

				return $cols;
			}
		} else{ // 查找一条记录
			// 返回数据个数
			if(true !== $sepa){ // 当sepa指定为true的时候 返回所有数据
				$options['limit'] = is_numeric($sepa)? $sepa : 1;
			}
			$this->doCallback('before_read', $options, $options);
			$result = $this->db->select($options);
			if(!empty($result)){
				if(true !== $sepa && 1 == $options['limit']){
					return reset($result[0]);
				}
				foreach($result as $val){
					$array[] = $val[$field];
				}

				return isset($array)? $array : null;
			}
		}

		return null;
	}

	/**
	 * 创建数据对象 但不保存到数据库
	 * @access public
	 *
	 * @param mixed  $data 创建数据
	 * @param string $type 状态
	 *
	 * @return mixed
	 */
	public function create($data = '', $type = ''){
		// 如果没有传值默认取POST数据
		if(empty($data)){
			$data = $_POST;
		} elseif(is_object($data)){
			$data = get_object_vars($data);
		}
		// 验证数据
		if(empty($data) || !is_array($data)){
			$this->error = L('_DATA_TYPE_INVALID_');

			return false;
		}

		// 检查字段映射
		$data = $this->parseFieldsMap($data, 0);

		// 状态
		$type = $type? $type : (!empty($data[$this->getPk()])? self::MODEL_UPDATE : self::MODEL_INSERT);

		// 检测提交字段的合法性
		if(isset($this->options['field'])){ // $this->field('field1,field2...')->create()
			$fields = $this->options['field'];
			unset($this->options['field']);
		} elseif($type == self::MODEL_INSERT && isset($this->insertFields)){
			$fields = $this->insertFields;
		} elseif($type == self::MODEL_UPDATE && isset($this->updateFields)){
			$fields = $this->updateFields;
		}
		if(isset($fields)){
			if(is_string($fields)){
				$fields = explode(',', $fields);
			}
			// 判断令牌验证字段
			if(TOKEN_ON){
				$fields[] = TOKEN_NAME;
			}
			foreach($data as $key => $val){
				if(!in_array($key, $fields)){
					unset($data[$key]);
				}
			}
		}

		// 数据自动验证
		if(!$this->autoValidation($data, $type)){
			return false;
		}

		// 表单令牌验证
		if(!$this->autoCheckToken($data)){
			$this->error = L('_TOKEN_ERROR_');

			return false;
		}

		// 验证完成生成数据对象
		if($this->autoCheckFields){ // 开启字段检测 则过滤非法字段数据
			$fields = $this->getDbFields();
			foreach($data as $key => $val){
				if(!in_array($key, $fields)){
					unset($data[$key]);
				} elseif(MAGIC_QUOTES_GPC && is_string($val)){
					$data[$key] = stripslashes($val);
				}
			}
		}

		// 创建完成对数据进行自动处理
		$this->autoOperation($data, $type);
		
		$this->doCallback('create', $data);
		
		// 赋值当前数据对象
		$this->data = $data;

		// 返回创建的数据以供其他调用
		return $data;
	}

	/**
	 * 自动表单令牌验证
	 * TODO  ajax无刷新多次提交暂不能满足
	 */
	public function autoCheckToken($data){
		// 支持使用token(false) 关闭令牌验证
		if(isset($this->options['token']) && !$this->options['token']){
			return true;
		}
		if(TOKEN_ON){
			$name = TOKEN_NAME;
			if(!isset($data[$name]) || !isset($_SESSION[$name])){ // 令牌数据无效
				return false;
			}

			// 令牌验证
			list($key, $value) = explode('_', $data[$name]);
			if($value && $_SESSION[$name][$key] === $value){ // 防止重复提交
				unset($_SESSION[$name][$key]); // 验证完成销毁session
				return true;
			}
			// 开启TOKEN重置
			if(TOKEN_RESET){
				unset($_SESSION[$name][$key]);
			}

			return false;
		}

		return true;
	}

	/**
	 * 使用正则验证数据
	 * @access public
	 *
	 * @param string $value  要验证的数据
	 * @param string $rule   验证规则
	 *
	 * @return boolean
	 */
	public function regex($value, $rule){
		$validate = array(
			'require'  => '/.+/',
			'email'    => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
			'url'      => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
			'currency' => '/^\d+(\.\d+)?$/',
			'number'   => '/^\d+$/',
			'zip'      => '/^\d{6}$/',
			'integer'  => '/^[-\+]?\d+$/',
			'double'   => '/^[-\+]?\d+(\.\d+)?$/',
			'english'  => '/^[A-Za-z]+$/',
		);
		// 检查是否有内置的正则表达式
		if(isset($validate[strtolower($rule)])){
			$rule = $validate[strtolower($rule)];
		}

		return preg_match($rule, $value) === 1;
	}

	/**
	 * 自动表单处理
	 * @access public
	 *
	 * @param array  $data 创建数据
	 * @param string $type 创建类型
	 *
	 * @return mixed
	 */
	private function autoOperation(&$data, $type){
		if(!empty($this->options['auto'])){
			$_auto = $this->options['auto'];
			unset($this->options['auto']);
		} elseif(!empty($this->_auto)){
			$_auto = $this->_auto;
		}
		// 自动填充
		if(isset($_auto)){
			foreach($_auto as $auto){
				// 填充因子定义格式
				// array('field','填充内容','填充条件','附加规则',[额外参数])
				if(empty($auto[2])){
					$auto[2] = self::MODEL_INSERT;
				} // 默认为新增的时候自动填充
				if($type == $auto[2] || $auto[2] == self::MODEL_BOTH){
					switch(trim($auto[3])){
					case 'function': //  使用函数进行填充 字段的值作为参数
					case 'callback': // 使用回调方法
						$args = isset($auto[4])? (array)$auto[4] : array();
						if(isset($data[$auto[0]])){
							array_unshift($args, $data[$auto[0]]);
						}
						if('function' == $auto[3]){
							$data[$auto[0]] = call_user_func_array($auto[1], $args);
						} else{
							$data[$auto[0]] = call_user_func_array(array(&$this, $auto[1]), $args);
						}
						break;
					case 'field': // 用其它字段的值进行填充
						$data[$auto[0]] = $data[$auto[1]];
						break;
					case 'ignore': // 为空忽略
						if('' === $data[$auto[0]]){
							unset($data[$auto[0]]);
						}
						break;
					case 'string':
					default: // 默认作为字符串填充
						$data[$auto[0]] = $auto[1];
					}
					if(false === $data[$auto[0]]){
						unset($data[$auto[0]]);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * 自动表单验证
	 * @access protected
	 *
	 * @param array  $data 创建数据
	 * @param string $type 创建类型
	 *
	 * @return boolean
	 */
	protected function autoValidation($data, $type){
		if(!empty($this->options['validate'])){
			$_validate = $this->options['validate'];
			unset($this->options['validate']);
		} elseif(!empty($this->_validate)){
			$_validate = $this->_validate;
		}
		// 属性验证
		if(isset($_validate)){ // 如果设置了数据自动验证则进行数据验证
			if($this->patchValidate){ // 重置验证错误信息
				$this->error = array();
			}
			foreach($_validate as $val){
				// 验证因子定义格式
				// array(field,rule,message,condition,type,when,params)
				// 判断是否需要执行验证
				if(empty($val[5]) || $val[5] == self::MODEL_BOTH || $val[5] == $type){
					if(0 == strpos($val[2], '{%') && strpos($val[2], '}')) // 支持提示信息的多语言 使用 {%语言定义} 方式
					{
						$val[2] = L(substr($val[2], 2, -1));
					}
					$val[3] = isset($val[3])? $val[3] : self::EXISTS_VALIDATE;
					$val[4] = isset($val[4])? $val[4] : 'regex';
					// 判断验证条件
					switch($val[3]){
					case self::MUST_VALIDATE: // 必须验证 不管表单是否有设置该字段
						if(false === $this->_validationField($data, $val)){
							return false;
						}
						break;
					case self::VALUE_VALIDATE: // 值不为空的时候才验证
						if('' != trim($data[$val[0]])){
							if(false === $this->_validationField($data, $val)){
								return false;
							}
						}
						break;
					default: // 默认表单存在该字段就验证
						if(isset($data[$val[0]])){
							if(false === $this->_validationField($data, $val)){
								return false;
							}
						}
					}
				}
			}
			// 批量验证的时候最后返回错误
			if(!empty($this->error)){
				return false;
			}
		}

		return true;
	}

	/**
	 * 验证表单字段 支持批量验证
	 * 如果批量验证返回错误的数组信息
	 * @access protected
	 *
	 * @param array $data 创建数据
	 * @param array $val  验证因子
	 *
	 * @return boolean
	 */
	protected function _validationField($data, $val){
		if(false === $this->_validationFieldItem($data, $val)){
			if($this->patchValidate){
				$this->error[$val[0]] = $val[2];
			} else{
				$this->error = $val[2];

				return false;
			}
		}

		return true;
	}

	/**
	 * 根据验证因子验证字段
	 * @access protected
	 *
	 * @param array $data 创建数据
	 * @param array $val  验证因子
	 *
	 * @return boolean
	 */
	protected function _validationFieldItem($data, $val){
		switch(strtolower(trim($val[4]))){
		case 'function': // 使用函数进行验证
		case 'callback': // 调用方法进行验证
			$args = isset($val[6])? (array)$val[6] : array();
			if(is_string($val[0]) && strpos($val[0], ',')){
				$val[0] = explode(',', $val[0]);
			}
			if(is_array($val[0])){
				// 支持多个字段验证
				foreach($val[0] as $field){
					$_data[$field] = $data[$field];
				}
				array_unshift($args, $_data);
			} else{
				array_unshift($args, $data[$val[0]]);
			}
			if('function' == $val[4]){
				return call_user_func_array($val[1], $args);
			} else{
				return call_user_func_array(array(&$this, $val[1]), $args);
			}
		case 'confirm': // 验证两个字段是否相同
			return $data[$val[0]] == $data[$val[1]];
		case 'unique': // 验证某个值是否唯一
			if(is_string($val[0]) && strpos($val[0], ',')){
				$val[0] = explode(',', $val[0]);
			}
			$map = array();
			if(is_array($val[0])){
				// 支持多个字段验证
				foreach($val[0] as $field){
					$map[$field] = $data[$field];
				}
			} else{
				$map[$val[0]] = $data[$val[0]];
			}
			if(!empty($data[$this->getPk()])){ // 完善编辑的时候验证唯一
				$map[$this->getPk()] = array('neq', $data[$this->getPk()]);
			}
			if($this
					->where($map)
					->find()
			){
				return false;
			}

			return true;
		default: // 检查附加规则
			return $this->check($data[$val[0]], $val[1], $val[4]);
		}
	}

	/**
	 * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
	 * @access public
	 *
	 * @param string $value 验证数据
	 * @param mixed  $rule  验证表达式
	 * @param string $type  验证方式 默认为正则验证
	 *
	 * @return boolean
	 */
	public function check($value, $rule, $type = 'regex'){
		$type = strtolower(trim($type));
		switch($type){
		case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
		case 'notin':
			$range = is_array($rule)? $rule : explode(',', $rule);

			return $type == 'in'? in_array($value, $range) : !in_array($value, $range);
		case 'between': // 验证是否在某个范围
		case 'notbetween': // 验证是否不在某个范围            
			if(is_array($rule)){
				$min = $rule[0];
				$max = $rule[1];
			} else{
				list($min, $max) = explode(',', $rule);
			}

			return $type == 'between'? $value >= $min && $value <= $max : $value < $min || $value > $max;
		case 'equal': // 验证是否等于某个值
		case 'notequal': // 验证是否等于某个值            
			return $type == 'equal'? $value == $rule : $value != $rule;
		case 'length': // 验证长度
			$length = mb_strlen($value, 'utf-8'); // 当前数据长度
			if(strpos($rule, ',')){ // 长度区间
				list($min, $max) = explode(',', $rule);

				return $length >= $min && $length <= $max;
			} else{ // 指定长度
				return $length == $rule;
			}
		case 'expire':
			list($start, $end) = explode(',', $rule);
			if(!is_numeric($start)){
				$start = strtotime($start);
			}
			if(!is_numeric($end)){
				$end = strtotime($end);
			}

			return NOW_TIME >= $start && NOW_TIME <= $end;
		case 'ip_allow': // IP 操作许可验证
			return in_array(get_client_ip(), explode(',', $rule));
		case 'ip_deny': // IP 操作禁止验证
			return !in_array(get_client_ip(), explode(',', $rule));
		case 'regex':
		default: // 默认使用正则验证 可以使用验证类中定义的验证名称
			// 检查附加规则
			return $this->regex($value, $rule);
		}
	}

	/**
	 * SQL查询
	 * @access public
	 *
	 * @param string $sql    SQL指令
	 * @param mixed  $parse  是否需要解析SQL
	 *
	 * @return mixed
	 */
	public function query($sql, $parse = false){
		if(!is_bool($parse) && !is_array($parse)){
			$parse = func_get_args();
			array_shift($parse);
		}
		$sql = $this->parseSql($sql, $parse);

		return $this->db->query($sql);
	}

	/**
	 * 执行SQL语句
	 * @access public
	 *
	 * @param string $sql    SQL指令
	 * @param mixed  $parse  是否需要解析SQL
	 *
	 * @return bool|integer
	 */
	public function execute($sql, $parse = false){
		if(!is_bool($parse) && !is_array($parse)){
			$parse = func_get_args();
			array_shift($parse);
		}
		$sql = $this->parseSql($sql, $parse);

		return $this->db->execute($sql);
	}

	/**
	 * 解析SQL语句
	 * @access public
	 *
	 * @param string  $sql    SQL指令
	 * @param boolean $parse  是否需要解析SQL
	 *
	 * @return string
	 */
	protected function parseSql($sql, $parse){
		// 分析表达式
		if(true === $parse){
			$options = $this->_parseOptions();
			$sql     = $this->db->parseSql($sql, $options);
		} elseif(is_array($parse)){ // SQL预处理
			$parse = array_map(array($this->db, 'escapeString'), $parse);
			$sql   = vsprintf($sql, $parse);
		} else{
			$sql = strtr($sql, array('__TABLE__' => $this->getTableName(), '__PREFIX__' => DB_PREFIX));
		}
		$this->db->setModel($this->name);

		return $sql;
	}

	/**
	 * 切换当前的数据库连接
	 * @access public
	 *
	 * @param mixed $linkNum  连接序号
	 * @param mixed $config   数据库连接信息
	 * @param array $params   模型参数
	 *
	 * @return Model
	 */
	protected function db($linkNum = '', $config = '', $params = array()){
		if('' === $linkNum && $this->db){
			return $this->db;
		}
		static $_linkNum = array();
		static $_db = array();
		if(!isset($_db[$linkNum]) || (isset($_db[$linkNum]) && $config && $_linkNum[$linkNum] != $config)){
			// 创建一个新的实例
			$_db[$linkNum] = ThinkInstance::Db($config);
		} elseif(null === $config){
			$_db[$linkNum]->close(); // 关闭数据库连接
			unset($_db[$linkNum]);

			return null;
		}
		if(!empty($params)){
			if(is_string($params)){
				parse_str($params, $params);
			}
			foreach($params as $name => $value){
				$this->setProperty($name, $value);
			}
		}
		// 记录连接信息
		$_linkNum[$linkNum] = $config;
		// 切换数据库连接
		$this->db = $_db[$linkNum];
		$this->doCallback('after_db', $this->db, $_db);
		// 字段检测
		if(!empty($this->name) && $this->autoCheckFields){
			$this->_checkTableInfo();
		}

		return $this;
	}

	/**
	 * 得到当前的数据对象名称
	 * @access public
	 * @return string
	 */
	public function getModelName(){
		if(empty($this->name)){
			$this->name = substr(get_class($this), 0, -5);
		}

		return $this->name;
	}

	/**
	 * 得到完整的数据表名
	 * @access public
	 * @return string
	 */
	public function getTableName(){
		if(empty($this->trueTableName)){
			$tableName = !empty($this->tablePrefix)? $this->tablePrefix : '';
			if(!empty($this->tableName)){
				$tableName .= $this->tableName;
			} else{
				$tableName .= parse_name($this->name);
			}
			$this->trueTableName = strtolower($tableName);
		}

		return (!empty($this->dbName)? $this->dbName . '.' : '') . $this->trueTableName;
	}

	/**
	 * 启动事务
	 * @access public
	 * @return void
	 */
	public function startTrans(){
		$this->commit();
		$this->db->startTrans();

		return;
	}

	/**
	 * 提交事务
	 * @access public
	 * @return boolean
	 */
	public function commit(){
		return $this->db->commit();
	}

	/**
	 * 事务回滚
	 * @access public
	 * @return boolean
	 */
	public function rollback(){
		return $this->db->rollback();
	}

	/**
	 * 返回模型的错误信息
	 * @access public
	 * @return string
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * 返回模型的错误信息
	 * @access public
	 * @return string
	 */
	public function getErrorCode(){
		return $this->errorCode;
	}

	/**
	 * 返回数据库的错误信息
	 * @access public
	 * @return string
	 */
	public function getDbError(){
		return $this->db->getError();
	}

	/**
	 * 返回最后插入的ID
	 * @access public
	 * @return string
	 */
	public function getLastInsID(){
		return $this->db->getLastInsID();
	}

	/**
	 * 返回最后执行的sql语句
	 * @access public
	 * @return string
	 */
	public function getLastSql(){
		return $this->db->getLastSql($this->name);
	}

	/* 鉴于getLastSql比较常用 增加_sql 别名
	public function _sql(){
		return $this->getLastSql();
	}*/

	/**
	 * 获取主键名称
	 * @access public
	 * @return string
	 */
	public function getPk(){
		return isset($this->fields['_pk'])? $this->fields['_pk'] : $this->pk;
	}

	/**
	 * 获取数据表字段信息
	 * @access public
	 * @return array
	 */
	public function getDbFields(){
		if(isset($this->options['table'])){ // 动态指定表名
			$fields = $this->db->getFields($this->options['table']);

			return $fields? array_keys($fields) : false;
		}
		if($this->fields){
			$fields = $this->fields;
			unset($fields['_autoinc'], $fields['_pk'], $fields['_type'], $fields['_version']);

			return $fields;
		}

		return false;
	}

	/**
	 * 设置数据对象值
	 * @access public
	 *
	 * @param mixed $data 数据
	 *
	 * @return Model
	 */
	public function data($data = null){
		if(func_num_args() === 0){
			return $this->data;
		}
		if(is_object($data)){
			$data = get_object_vars($data);
		} elseif(is_string($data)){
			parse_str($data, $data);
		} elseif(!is_array($data)){
			Think::halt(LANG_DATA_TYPE_INVALID);
		}
		$this->data = $data;

		return $this;
	}

	/**
	 * 查询SQL组装 join
	 * @access public
	 *
	 * @param mixed $join
	 *
	 * @return Model
	 */
	public function join($join){
		if(is_array($join)){
			$this->options['join'] = $join;
		} elseif(!empty($join)){
			$this->options['join'][] = $join;
		}

		return $this;
	}

	/**
	 * 查询SQL组装 union
	 * @access public
	 *
	 * @param mixed   $union
	 * @param boolean $all
	 *
	 * @return Model
	 */
	public function union($union, $all = false){
		if(empty($union)){
			return $this;
		}
		if($all){
			$this->options['union']['_all'] = true;
		}
		if(is_object($union)){
			$union = get_object_vars($union);
		}
		// 转换union表达式
		if(is_string($union)){
			$options = $union;
		} elseif(is_array($union)){
			if(isset($union[0])){
				$this->options['union'] = array_merge($this->options['union'], $union);

				return $this;
			} else{
				$options = $union;
			}
		} else{
			throw_exception(L('_DATA_TYPE_INVALID_'));

			return false;
		}
		$this->options['union'][] = $options;

		return $this;
	}

	/**
	 * 查询缓存
	 * @access public
	 *
	 * @param string|bool $key
	 * @param integer     $expire
	 *
	 * @return Model
	 */
	public function cache($key = true, $expire = DATA_CACHE_TIME){
		if(false !== $key){
			$this->options['cache'] = array('key' => $key, 'expire' => $expire, 'cas' => $this->cache_cas);
		}

		return $this;
	}

	/**
	 * 指定查询字段 支持字段排除
	 * @access public
	 *
	 * @param mixed   $field
	 * @param boolean $except 是否排除
	 *
	 * @return Model
	 */
	public function field($field, $except = false){
		if(true === $field){ // 获取全部字段
			$fields = $this->getDbFields();
			$field  = $fields? $fields : '*';
		} elseif($except){ // 字段排除
			if(is_string($field)){
				$field = explode(',', $field);
			}
			$fields = $this->getDbFields();
			$field  = $fields? array_diff($fields, $field) : $field;
		}
		$this->options['field'] = $field;

		return $this;
	}

	/**
	 * 调用命名范围
	 * @access public
	 *
	 * @param mixed $scope 命名范围名称 支持多个 和直接定义
	 * @param array $args  参数
	 *
	 * @return Model
	 */
	public function scope($scope = '', $args = null){
		if('' === $scope){
			if(isset($this->_scope['default'])){
				// 默认的命名范围
				$options = $this->_scope['default'];
			} else{
				return $this;
			}
		} elseif(is_string($scope)){ // 支持多个命名范围调用 用逗号分割
			$scopes  = explode(',', $scope);
			$options = array();
			foreach($scopes as $name){
				if(!isset($this->_scope[$name])){
					continue;
				}
				$options = array_merge($options, $this->_scope[$name]);
			}
			if(!empty($args) && is_array($args)){
				$options = array_merge($options, $args);
			}
		} elseif(is_array($scope)){ // 直接传入命名范围定义
			$options = $scope;
		}

		if(isset($options) && is_array($options) && !empty($options)){
			$this->options = array_merge($this->options, array_change_key_case($options));
		}

		return $this;
	}

	/**
	 * 指定查询条件 支持安全过滤
	 * @access public
	 *
	 * @param mixed $where 条件表达式
	 * @param mixed $parse 预处理参数
	 *
	 * @return $this
	 */
	public function where($where, $parse = null){
		if(!is_null($parse) && is_string($where)){
			if(!is_array($parse)){
				$parse = func_get_args();
				array_shift($parse);
			}
			$parse = array_map(array($this->db, 'escapeString'), $parse);
			$where = vsprintf($where, $parse);
		} elseif(is_object($where)){
			$where = get_object_vars($where);
		} elseif(is_null($parse) && is_array($where) && isset($where[0])){
			if(array_keys($where) === range(0, count($where) - 1)){
				$where = array($this->getPk() => array('IN', $where));
			} else{
				$where = array();
			}
		} elseif(is_null($parse) && is_string($where) || is_int($where)){
			$where = array($this->getPk() => $where);
		}

		if(is_string($where) && '' != $where){
			$map            = array();
			$map['_string'] = $where;
			$where          = $map;
		}
		if(isset($this->options['where'])){
			$this->options['where'] = array_merge($this->options['where'], $where);
		} else{
			$this->options['where'] = $where;
		}

		return $this;
	}

	/**
	 * 指定查询数量
	 * @access public
	 *
	 * @param mixed $offset 起始位置
	 * @param mixed $length 查询数量
	 *
	 * @return Model
	 */
	public function limit($offset, $length = null){
		$this->options['limit'] = is_null($length)? $offset : $offset . ',' . $length;

		return $this;
	}

	/**
	 * 指定分页
	 * @access public
	 *
	 * @param mixed $page     页数
	 * @param mixed $listRows 每页数量
	 *
	 * @return Model
	public function page($page,$listRows=null){
	$this->options['page'] =   is_null($listRows)?$page:$page.','.$listRows;
	return $this;
	}
	 */

	/**
	 * 查询注释
	 * @access public
	 *
	 * @param string $comment 注释
	 *
	 * @return Model
	 */
	public function comment($comment){
		$this->options['comment'] = $comment;

		return $this;
	}

	/**
	 * 设置模型的属性值
	 * @access public
	 *
	 * @param string $name  名称
	 * @param mixed  $value 值
	 *
	 * @return Model
	 */
	public function setProperty($name, $value){
		if(property_exists($this, $name)){
			$this->$name = $value;
		}

		return $this;
	}

	/**
	 * 分页查询
	 *
	 * @param string $page_var_name
	 * 分页如果需要修改参数，要添加到GET变量里
	 *
	 * @return $this
	 */
	public function page($page_var_name = 'p'){
		if($this->page_not_init){
			$this->register_callback('before_select',
				function ($data, &$options){
					if(!isset($options['pager']) || !$options['pager']){
						return;
					}
					$this->options    = $options;
					$total            = $this->count();
					$this->page       = new Page($total, $this->perPage, array(), $options['pager']);
					$options['limit'] = $this->page->firstRow . ',' . $this->perPage;
				}
			);
			$this->page_not_init = false;
		}
		$this->options['pager'] = $page_var_name;
		return $this;
	}

	/**
	 * 获取分页对象
	 * @return Page
	 */
	public function getPage(){
		return $this->page;
	}

	/**
	 * 注册回调函数
	 * @param $type
	 * @param $cb
	 *
	 * @return $this
	 */
	public function register_callback($type, $cb){
		if(!in_array($type, $this->callback)){
			throw_exception('Model： 注册不存在的事件`' . $type . '`。');
		}
		if(is_string($cb)){
			$cb = array($this, $cb);
		}
		$this->callback[$type][] = $cb;

		return $this;
	}

	/**
	 * @param string $type
	 * @param mixed  $data
	 * @param mixed  $opt
	 *
	 * @return bool
	 */
	private function doCallback($type, &$data, &$opt){
		if(!isset($this->callback[$type])){
			return true;
		}
		foreach($this->callback[$type] as $cb){
			$ret = $cb($data, $opt);
			if(false === $ret){
				return $ret;
			}
		}

		return true;
	}
}
