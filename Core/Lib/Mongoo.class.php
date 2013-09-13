<?php
/**
 * 连接mongo数据库，必须在子类里指定
 * 	$connection -> 连接信息
 * 	$collectionName -> 集合名
 * 
 * -- 防止和其他东西冲突所以多一个 o 
 *
 * @author ${USER}
 */
class Mongoo extends MongoCollection{
	protected $collectionName = 'list';
	protected $connection;
	
	/** @var MongoClient */
	protected $_link;
	protected $name;
	protected $database;

	/**
	 * @param string $name
	 */
	public function __construct($name){
		$this->name = $name;
		$this->init_connection($this->connection);
		parent::__construct($this->_link->selectDB($this->database), $this->collectionName);
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
			Think::halt(get_class($this).' 空的连接信息['.$db_config.']');
		}
		$config      = hidef_load('ThinkDb' . $db_config);
		$this->_link = new MongoClient($config['dsn'], (array)$config['params']);
		$this->database = $config['params']['db'];
	}
}
