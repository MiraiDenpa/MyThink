<?php
/**
 * 清理HTML代码，删除所有不必要的空格和各种注释
 *
 * @param string $content
 *
 * @return string
 */
function html_whitespace($content){
	$cache_replace = [];
	if(preg_match_all('#(<script.*?[^\?]>)(.*?)</script>#s', $content, $mats)){
		foreach($mats[2] as $id => $script){
			if(!trim($script)){
				continue;
			}
			$minize = script_whitespace($script);

			$cid                 = md5(rand()) . '-js' . $id;
			$cache_replace[$cid] = $minize;
			$content             = str_replace($mats[0][$id],  "\n".$mats[1][$id] . $cid . '</script>', $content);
		}
	}
	if(preg_match_all('#(<style.*?>)(.*?)</style>#s', $content, $mats)){
		foreach($mats[2] as $id => $script){
			if(!trim($script)){
				continue;
			}
			$minize = style_whitespace($script);

			$cid                 = md5(rand()) . '-css' . $id;
			$cache_replace[$cid] = $minize;
			$content             = str_replace($mats[0][$id], "\n".$mats[1][$id] . $cid . '</style>', $content);
		}
	}
	$content = str_replace(array_keys($cache_replace), array_values($cache_replace), $content);

	$search  = [
		'#^\s+#is',
		'#\s+(<[a-z!/])#is',
	];
	$replace = [
		'',
		'\1',
	];
	$content = preg_replace($search, $replace, $content);
	
	return $content;
}
