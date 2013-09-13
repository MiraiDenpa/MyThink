<?php
abstract class Entity{
	public function __construct($data){
		foreach($data as $name => $value){
			$this->$name = $value;
		}
	}
}
