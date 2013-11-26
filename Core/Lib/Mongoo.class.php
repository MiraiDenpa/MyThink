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

	/** @var array */
	protected $page;

	/** @var string */
	protected $database;
	/** @var MongoDB */
	protected $db;

	public $perPage = 30;
	public $url = '';

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

	public function execute($code, $args = []){
		return $this->db->command(array('$eval' => $code, 'args' => $args));
	}

	public function findById($id, $field = []){
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
	 * @param MongoCursor $cur
	 * @param int         $current
	 *
	 * @return array
	 */
	protected function pageCursor(MongoCursor &$cur, $current){
		$count = $cur->count();
		$cur
				->skip(($current - 1)*$this->perPage)
				->limit($this->perPage);
		$this->page = [
			'nowPage'   => $current,
			'totalPage' => ceil($count/$this->perPage),
			'totalRows' => $count,
		];
	}

	/**
	 * 获取分页对象
	 * @return array
	 */
	public function getPage(){
		$this->page['url'] = $this->url;
		return $this->page;
	}
}
