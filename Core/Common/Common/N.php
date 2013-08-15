<?php
/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @return mixed
 */
function N($key, $step=0,$save=false) {
	static $_num    = array();
	if (!isset($_num[$key])) {
		$_num[$key] = (false !== $save)? S('N_'.$key) :  0;
	}
	if (empty($step))
		return $_num[$key];
	else
		$_num[$key] = $_num[$key] + (int) $step;
	if(false !== $save){ // 保存结果
		S('N_'.$key,$_num[$key],$save);
	}
}
