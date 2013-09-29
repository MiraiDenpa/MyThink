<?php
class PostInputStream extends InputStream{
	use BaseInputStream;
	
	function __construct(){
		$this->_DATA = & $_POST;
	}
}
