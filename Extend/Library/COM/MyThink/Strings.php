<?php
namespace COM\MyThink;

/**
 * 一些处理字符串的通用函数
 * @package GFW
 */
class Strings{
	const SEC_DEFAULT       = 0x00;
	const SEC_SKIP          = 0x01;
	const SEC_INCLUDE_LEAD  = 0x02;
	const SEC_INCLUDE_TRAIL = 0x04;

	/**
	 * “弹出”字符串的最后一个元素
	 * 如 a-b-c-d ，弹出后，字符串变为 a-b-c，返回 d
	 * 如果没有最后一项，返回null
	 *
	 * @param $string 源字符串
	 * @param $needle 分隔符
	 *
	 * @return string
	 */
	public static function pop(&$string, $needle){
		$pos = strrpos($string, $needle);
		if(is_int($pos)){
			$ret    = substr($string, $pos + strlen($needle));
			$string = substr($string, 0, $pos);

			return $ret;
		}

		$ret    = $string;
		$string = '';

		return $ret;
	}

	/**
	 * “弹出”字符串的第一个元素
	 * 如 a-b-c-d ，弹出后，字符串变为 b-c-d，返回 a
	 * 如果没有第一项，返回null
	 *
	 * @param $string 源字符串
	 * @param $needle 分隔符
	 *
	 * @return null|string
	 */
	public static function shift(&$string, $needle){
		$pos = strpos($string, $needle);
		if(is_int($pos)){
			$ret    = substr($string, 0, $pos);
			$string = substr($string, $pos + strlen($needle));

			return $ret;
		}

		return null;
	}

	/**
	 * 获得字符串中指定范围的字串
	 * $start<$end时返回反向的字符串
	 * 如果 实际$start<0 或者 实际$end>总数 则分别取“0”和“总数”
	 *
	 * @param     $delimiter 分隔符
	 * @param     $string    源字符串
	 * @param     $start     开始位置[0,1,2...]。负数代表从后向前[-1,-2...]。
	 * @param     $end       结束位置[0,1,2...]。负数代表从后向前[-1,-2...]。
	 * @param int $flag
	 *
	 * @return string 链接后的字符串
	 */
	public static function section($delimiter, $string, $start, $end = -1, $flag = self::SEC_DEFAULT){
		static $scache = [];
		if(isset($scache[$delimiter][$string])){
			$arr = $scache[$delimiter][$string];
		}else{
			$scache[$delimiter][$string] = $arr = explode($delimiter, $string);
		}
		
		if($flag&self::SEC_SKIP){
			$arr = array_filter($arr);
		}
		$lead  = $flag&self::SEC_INCLUDE_LEAD;
		$trail = $flag&self::SEC_INCLUDE_TRAIL;

		$max = count($arr);

		/* 处理参数中的负数 */
		if($start < 0){
			$start = $real_start = $max + $start;
			if($real_start < 0){
				$real_start = 0;
			}
		} else{
			$real_start = $start;
		}
		if($end < 0){
			$end = $real_end = $max + $end;
			if($real_end < 0){
				$real_end = 0;
			}
		} else{
			$real_end = $end;
		}
		/* 负参数处理完毕 */

		/* 超出范围处理 */
		if(($end >= $max && $start >= $max) || ($end < 0 && $start < 0)){
			return '';
		}
		if($real_start > $max){
			$real_start = $max;
			$lead       = false;
		} elseif($real_start == 0){
			$lead = false;
		}

		if($real_end >= $max){
			$real_end = $max - 1;
			$trail    = false;
		} elseif($real_end == $max - 1){
			$trail = false;
		}
		/* 超出范围处理完毕 */

		/* 只有一项直接返回 */
		if($real_start == $real_end){
			return isset($arr[$real_start])?
					($lead? $delimiter : '') . $arr[$real_start] . ($trail? $delimiter : '') : '';
		}

		/* 反向处理 */
		$start = $real_start;
		$end   = $real_end;
		if($real_start > $real_end){
			$real_start = $end;
			$real_end   = $start;
		}

		/* 主要操作 */
		$ret = array_slice($arr, $real_start, $real_end - $real_start + 1);

		/* 反向处理２ */
		if($real_start == $end){
			$ret = array_reverse($ret);
		}

		return ($lead? $delimiter : '') . implode($delimiter, $ret) . ($trail? $delimiter : '');
	}

	/**
	 * 限定字符串长度，超长则截断，过短则补空
	 * @param string $str    源字符串
	 * @param int    $length 目标长度
	 * @param string $pad    补空字符
	 * @param int    $type   补空方式
	 *
	 * @return string
	 */
	public static function str_mix($str, $length, $pad = " ", $type = STR_PAD_LEFT){
		return str_pad(substr($str, 0, $length), $length, $pad, $type);
	}

	/**
	 * 缩进整段文字
	 *
	 * @param string $str
	 * @param string $tab
	 *
	 * @return string
	 */
	public static function tabMultiline($str, $tab = "\t"){
		return $tab . str_replace("\n", "\n" . $tab, $str);
	}

	/**
	 * 测试结尾，如果不符则添加，否则不变
	 *
	 * @param $string  string 字符串1
	 * @param $pattern string 字符串2
	 *
	 * @return string
	 */
	public static function endWith($string, $pattern){
		if(substr($string, strlen($string) - strlen($pattern)) === $pattern){
			return $string;
		} else{
			return $string . $pattern;
		}
	}

	/**字符串1结尾是否为字符串2
	 *
	 * @param $string  string 字符串1
	 * @param $pattern string 字符串2
	 *
	 * @return bool
	 */
	public static function isEndWith($string, $pattern){
		return substr($string, strlen($string) - strlen($pattern)) === $pattern;
	}

	/**
	 * 测试开头，如果不符则添加，否则不变
	 *
	 * @param $string  string 字符串1
	 * @param $pattern string 字符串2
	 *
	 * @return string
	 */
	public static function startWith($string, $pattern){
		if(substr($string, 0, strlen($pattern)) === $pattern){
			return $string;
		} else{
			return $pattern . $string;
		}
	}

	/**
	 * 字符串1开头是否为字符串2
	 *
	 * @param $string  string 字符串1
	 * @param $pattern string 字符串2
	 *
	 * @return bool
	 */
	public static function isStartWith($string, $pattern){
		return substr($string, 0, strlen($pattern)) === $pattern;
	}

	public static function blocktrim($string, $pattern, $flag = STR_TRIM_BOTH){
		$pSize = strlen($pattern);
		if($flag&STR_TRIM_LEFT){
			while(strpos($string, $pattern) === 0){
				$string = substr($string, $pSize);
			}
		}
		if($flag&STR_TRIM_RIGHT){
			while(strpos($string, $pattern) === strlen($string) - $pSize){
				$string = substr($string, 0, -$pSize);
			}
		}
		return $string;
	}

}
