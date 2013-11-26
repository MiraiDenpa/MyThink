<?php
/**
 * 处理文件系统
 *
 * @author ${USER}
 */
class MongooFile extends MongoGridFS{
	/** @var string */
	protected $collectionName = 'fs';
	/** @var string */
	protected $connection='mongo-file';

	/** @var MongoClient */
	protected $_link;
	/** @var string */
	protected $name;

	/** @var array */
	protected $page;

	/** @var string */
	protected $database;
	/** @var MongoDB */
	protected $db;

	/**
	 * @param $arg1
	 * @param $arg2
	 */
	public function __construct($arg1, $arg2){
		$this->_initialize($arg1, $arg2);
		$this->init_connection($this->connection);
		$this->db = $this->_link->selectDB($this->database);
		parent::__construct($this->db, $this->collectionName);
	}

	public function _initialize($arg1, $arg2){
	}

	/**
	 *
	 * @param $db_config
	 *
	 * @return void
	 */
	private function init_connection($db_config){
		static $_cache = [];
		if(isset($_cache[$db_config])){
			$this->_link = $_cache[$db_config];
			return;
		}
		if(!$db_config){
			Think::halt(get_class($this) . ' 空的连接信息[' . $db_config . ']');
		}
		$config         = hidef_load('ThinkDb' . $db_config);
		$this->_link    = new MongoClient($config['dsn'], (array)$config['params']);
		$this->database = $config['params']['db'];
	}
}
