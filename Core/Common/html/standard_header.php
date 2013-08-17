<?php
/**
 * 返回标准头部
 *
 * @param $title
 *
 * @return string
 */
function StandardHeader($title = 'test'){
	$ret = '<!DOCTYPE html><head><title>'.$title.'</title></head><body style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif;">';
	return $ret;
}

function StandardFooter(){
	$ret = '';
	return $ret;
}
