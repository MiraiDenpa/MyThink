<?php
/**
 * 连接mongo数据库，必须在子类里指定
 *    $connection -> 连接信息
 *    $collectionName -> 集合名
 *
 * -- 防止和其他东西冲突所以多一个 o
 *
 * @author ${USER}
 */
class Mongoo extends MongoCollection{
	/** @var string */
	protected $collectionName = 'list';
	/** @var string */
	protected $connection;

	/** @var MongoClient */
	protected $_link;
	/** @var string */
	protected $name;

	/** @var Page */
	protected $page;

	/** @var string */
	protected $database;
	/** @var MongoDB */
	protected $db;

	/**
	 * @param string $name
	 */
	public function __construct($name){
		$this->name = $name;
		$this->init_connection($this->connection);
		$this->db = $this->_link->selectDB($this->database);
		parent::__construct($this->db, $this->collectionName);
	}

	public function execute($code, $arg = []){
		return $this->db->command(array('$eval' => $code, 'args' => $args));
	}

	public function findById($id, $field){
		return $this->find(['_id' => new MongoId($id)], $field);
	}

	public function findOneById($id, $field = []){
		return $this->findOne(['_id' => new MongoId($id)], $field);
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

	/**
	 * 分页查询
	 *
	 * @param string $page_var_name
	 * 分页如果需要修改参数，要添加到GET变量里
	 *
	 * @return $this
	 */
	protected function page($page_var_name = 'p'){
		$this->page             = new Page($total, $this->perPage, array(), $options['pager']);
		$options['limit']       = $this->page->firstRow . ',' . $this->perPage;
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
}
