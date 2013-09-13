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

defined('THINK_PATH') or exit();
/**
 * PDO数据库驱动
 * @category    Extend
 * @package     Extend
 * @subpackage  Driver.Db
 * @author      liu21st <liu21st@gmail.com>
 */
class DbPdo extends Db{

	protected $PDOStatement = null;
	private $table = '';

	/**
	 * 架构函数 读取数据库配置信息
	 * @access public
	 *
	 * @param array $config 数据库配置数组
	 */
	public function __construct($config = ''){
		if(!class_exists('PDO')){
			throw_exception(L('_NOT_SUPPERT_') . ':PDO');
		}
		if(!empty($config)){
			$this->config = $config;
		}
	}

	/**
	 * 连接数据库方法
	 * @access public
	 */
	public function connect($config = '', $linkNum = 0){
		if(!isset($this->linkID[$linkNum])){
			if(empty($config)){
				$config = $this->config;
			}
			
			try{
				$this->linkID[$linkNum] = new PDO($config['dsn'], $config['username'], $config['password'], (array)$config['params']);
			} catch(Exception $e){
				Think::appException($e);
				die;
			}
			// 因为PDO的连接切换可能导致数据库类型不同，因此重新获取下当前的数据库类型
			$this->dbType = $this->_getDsnType($config['dsn']);
			$this->linkID[$linkNum]->exec('SET NAMES ' . DB_CHARSET);
			// 标记连接成功
			$this->connected = true;
			// 注销数据库连接配置信息
			if(1 != DB_DEPLOY_TYPE){
				unset($this->config);
			}
		}

		return $this->linkID[$linkNum];
	}

	/**
	 * 释放查询结果
	 * @access public
	 */
	public function free(){
		$this->PDOStatement = null;
	}

	/**
	 * 执行查询 返回数据集
	 * @access public
	 *
	 * @param string $str  sql指令
	 *
	 * @return mixed
	 */
	public function query($str){
		$this->initConnect(false);
		if(!$this->_linkID){
			return false;
		}
		$this->queryStr = $str;
		//释放前次的查询结果
		if(!empty($this->PDOStatement)){
			$this->free();
		}
		N('db_query', 1);
		// 记录开始执行时间
		G('queryStartTime');
		$this->PDOStatement = $this->_linkID->prepare($str);
		if(false === $this->PDOStatement){
			throw_exception($this->error());
		}
		$result = $this->PDOStatement->execute();
		$this->debug();
		if(false === $result){
			$this->error();

			return false;
		} else{
			return $this->getAll();
		}
	}

	/**
	 * 执行语句
	 * @access public
	 *
	 * @param string $str  sql指令
	 *
	 * @return integer
	 */
	public function execute($str){
		$this->initConnect(true);
		if(!$this->_linkID){
			return false;
		}
		$this->queryStr = $str;
		$flag           = false;
		if($this->dbType == 'OCI'){
			if(preg_match("/^\s*(INSERT\s+INTO)\s+(\w+)\s+/i", $this->queryStr, $match)){
				$this->table = C("DB_SEQUENCE_PREFIX") . str_ireplace(C("DB_PREFIX"), "", $match[2]);
				$flag        = (boolean)$this->query("SELECT * FROM user_sequences WHERE sequence_name='" .
													 strtoupper($this->table) . "'");
			}
		}
		//modify by wyfeng at 2009.08.28
		//释放前次的查询结果
		if(!empty($this->PDOStatement)){
			$this->free();
		}
		N('db_write', 1);
		// 记录开始执行时间
		G('queryStartTime');
		$this->PDOStatement = $this->_linkID->prepare($str);
		if(false === $this->PDOStatement){
			throw_exception($this->error());
		}
		$result = $this->PDOStatement->execute();
		$this->debug();
		if(false === $result){
			$this->error();

			return false;
		} else{
			$this->numRows = $this->PDOStatement->rowCount();
			if($flag || preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)){
				$this->lastInsID = $this->getLastInsertId();
			}

			return $this->numRows;
		}
	}

	/**
	 * 启动事务
	 * @access public
	 * @return void
	 */
	public function startTrans(){
		$this->initConnect(true);
		if(!$this->_linkID){
			return false;
		}
		//数据rollback 支持
		if($this->transTimes == 0){
			$this->_linkID->beginTransaction();
		}
		$this->transTimes++;

		return;
	}

	/**
	 * 用于非自动提交状态下面的查询提交
	 * @access public
	 * @return boolen
	 */
	public function commit(){
		if($this->transTimes > 0){
			$result           = $this->_linkID->commit();
			$this->transTimes = 0;
			if(!$result){
				$this->error();

				return false;
			}
		}

		return true;
	}

	/**
	 * 事务回滚
	 * @access public
	 * @return boolen
	 */
	public function rollback(){
		if($this->transTimes > 0){
			$result           = $this->_linkID->rollback();
			$this->transTimes = 0;
			if(!$result){
				$this->error();

				return false;
			}
		}

		return true;
	}

	/**
	 * 获得所有的查询数据
	 * @access private
	 * @return array
	 */
	private function getAll(){
		//返回数据集
		$result        = $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
		$this->numRows = count($result);

		return $result;
	}

	/**
	 * 取得数据表的字段信息
	 * @access public
	 */
	public function getFields($tableName){
		$this->initConnect(true);
		if(defined('DB_DESCRIBE_TABLE_SQL')){
			// 定义特殊的字段查询SQL
			$sql = str_replace('%table%', $tableName, DB_DESCRIBE_TABLE_SQL);
		} else{
			switch($this->dbType){
			case 'SQLITE':
				$sql = 'PRAGMA table_info (' . $tableName . ') ';
				break;
			default:
				$sql = 'DESCRIBE ' . $tableName;
				//备注: 驱动类不只针对mysql，不能加``
			}
		}
		$result = $this->query($sql);
		$info   = array();
		if($result){
			foreach($result as $key => $val){
				$val         = array_change_key_case($val);
				$val['name'] = isset($val['name'])? $val['name'] : "";
				$val['type'] = isset($val['type'])? $val['type'] : "";
				$name        = isset($val['field'])? $val['field'] : $val['name'];
				$info[$name] = array(
					'name'    => $name,
					'type'    => $val['type'],
					'notnull' => (bool)(((isset($val['null'])) && ($val['null'] === '')) ||
										((isset($val['notnull'])) && ($val['notnull'] === ''))),
					// not null is empty, null is yes
					'default' => isset($val['default'])? $val['default'] : (isset($val['dflt_value'])? $val['dflt_value'] : ""),
					'primary' => isset($val['dey'])?
							strtolower($val['dey']) == 'pri' : (isset($val['pk'])? $val['pk'] : false),
					'autoinc' => isset($val['extra'])?
							strtolower($val['extra']) == 'auto_increment' : (isset($val['key'])? $val['key'] : false),
				);
			}
		}

		return $info;
	}

	/**
	 * 取得数据库的表信息
	 * @access public
	 */
	public function getTables($dbName = ''){
		if(defined('DB_FETCH_TABLES_SQL')){
			// 定义特殊的表查询SQL
			$sql = str_replace('%db%', $dbName, DB_FETCH_TABLES_SQL);
		} else{
			switch($this->dbType){
			case 'ORACLE':
			case 'OCI':
				$sql = 'SELECT table_name FROM user_tables';
				break;
			case 'MSSQL':
			case 'SQLSRV':
				$sql = "SELECT TABLE_NAME	FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
				break;
			case 'PGSQL':
				$sql = "select tablename as Tables_in_test from pg_tables where  schemaname ='public'";
				break;
			case 'SQLITE':
				$sql = "SELECT name FROM sqlite_master WHERE type='table' " .
					   "UNION ALL SELECT name FROM sqlite_temp_master " . "WHERE type='table' ORDER BY name";
				break;
			case 'MYSQL':
			default:
				if(!empty($dbName)){
					$sql = 'SHOW TABLES FROM ' . $dbName;
				} else{
					$sql = 'SHOW TABLES ';
				}
			}
		}
		$result = $this->query($sql);
		$info   = array();
		foreach($result as $key => $val){
			$info[$key] = current($val);
		}

		return $info;
	}

	/**
	 * limit分析
	 * @access protected
	 *
	 * @param mixed $lmit
	 *
	 * @return string
	 */
	protected function parseLimit($limit){
		$limitStr = '';
		if(!empty($limit)){
			switch($this->dbType){
			case 'PGSQL':
			case 'SQLITE':
				$limit = explode(',', $limit);
				if(count($limit) > 1){
					$limitStr .= ' LIMIT ' . $limit[1] . ' OFFSET ' . $limit[0] . ' ';
				} else{
					$limitStr .= ' LIMIT ' . $limit[0] . ' ';
				}
				break;
			case 'MSSQL':
			case 'SQLSRV':
				break;
			case 'IBASE':
				// 暂时不支持
				break;
			case 'ORACLE':
			case 'OCI':
				break;
			case 'MYSQL':
			default:
				$limitStr .= ' LIMIT ' . $limit . ' ';
			}
		}

		return $limitStr;
	}

	/**
	 * 字段和表名处理
	 * @access protected
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function parseKey(&$key){
		if($this->dbType == 'MYSQL'){
			$key = trim($key);
			if(!preg_match('/[,\'\"\*\(\)`.\s]/', $key)){
				$key = '`' . $key . '`';
			}

			return $key;
		} else{
			return parent::parseKey($key);
		}
	}

	/**
	 * 关闭数据库
	 * @access public
	 */
	public function close(){
		$this->_linkID = null;
	}

	/**
	 * 数据库错误信息
	 * 并显示当前的SQL语句
	 * @access public
	 * @return string
	 */
	public function error(){
		if($this->PDOStatement){
			$error       = $this->PDOStatement->errorInfo();
			$this->error = $error[2];
		} else{
			$this->error = '';
		}
		if('' != $this->queryStr){
			$this->error .= "\n -> " . $this->queryStr;
		}
		trace($this->error, 'SQL语句', 'ERR');

		return $this->error;
	}

	/**
	 * SQL指令安全过滤
	 * @access public
	 *
	 * @param string $str  SQL指令
	 *
	 * @return string
	 */
	public function escapeString($str){
		switch($this->dbType){
		case 'PGSQL':
		case 'MSSQL':
		case 'SQLSRV':
		case 'MYSQL':
			return addslashes($str);
		case 'IBASE':
		case 'SQLITE':
		case 'ORACLE':
		case 'OCI':
			return str_ireplace("'", "''", $str);
		}
	}

	/**
	 * 获取最后插入id
	 * @access public
	 * @return integer
	 */
	public function getLastInsertId(){
		switch($this->dbType){
		case 'PGSQL':
		case 'SQLITE':
		case 'MSSQL':
		case 'SQLSRV':
		case 'IBASE':
		case 'MYSQL':
			return $this->_linkID->lastInsertId();
		case 'ORACLE':
		case 'OCI':
			$sequenceName = $this->table;
			$vo           = $this->query("SELECT {$sequenceName}.currval currval FROM dual");

			return $vo? $vo[0]["currval"] : 0;
		}
	}
}
