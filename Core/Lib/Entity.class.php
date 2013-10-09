<?php
abstract class Entity implements ArrayAccess{
	protected $data = [];

	public function __construct($data){
		foreach($data as $name => $value){
			$this->$name = $value;
		}
	}

	public function mpath($path){
		$path = explode('.', $path);
		$base = array_shift($path);
		$var  = $this->$base;
		while(is_array($var) && isset($var[$path[0]])){
			$var = $var[array_shift($path)];
		}
		return empty($path)? $var : null;
	}

	/**
	 * @param mixed $name
	 * @return mixed
	 */
	public function __get($name){
		return $this->data[$name];
	}

	/**
	 * @param mixed $name
	 * @param mixed $val
	 * @return void
	 */
	public function __set($name, $val){
		$this->data[$name] = $val;
	}

	/**
	 * @param mixed $name
	 * @return void
	 */
	public function __unset($name){
		unset($this->data[$name]);
	}

	/**
	 * @param mixed $name
	 * @return bool
	 */
	public function __isset($name){
		return isset($this->data[$name]);
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset){
		return isset($this->$offset);
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset){
		return $this->$offset;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value){
		$this->$offset=$value;
	}

	/**
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset){
		unset($this->$offset);
	}

	/**
	 *
	 * @return array
	 */
	public function toArray(){
		$data =get_object_vars($this);
		$ret= array_merge($data, $data['data']);
		unset($ret['data'],$ret['exist']);
		return $ret;
	}
}
