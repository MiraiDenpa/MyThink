<?php

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type=0) {
	if ($type) {
		return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
	} else {
		return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
	}
}
