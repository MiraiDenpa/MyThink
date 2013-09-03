<?php
class GetInputStream extends InputStream{
	function __construct(){
		$this->_DATA = &$_GET;
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
