<?php
class PostInputStream extends InputStream{
	function __construct(){
		$this->_DATA = &$_POST;
	}

	/**
	 * 使用预定义方法过滤
	 *
	 * @param string $name
	 * @param string $filter
	 * @param mixed  $args
	 *
	 * @return $this
	 */
	protected function _filter($name, $filter, $args = null){
	}
}
