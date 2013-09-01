<?php
function broswer_css_perfix($css){
	static $cssPerfixMap = array(
		'animation'         => ['webkit'],
		'transition'        => ['webkit'],
		'transform'         => ['webkit', 'ms'],
		'column-count'      => ['moz', 'webkit', 'ms'],
		'column-gap'        => ['moz', 'webkit', 'ms'],
		'column-rule'       => ['moz', 'webkit', 'ms'],
		'column-box-sizing' => ['moz'],
		'border-image'      => ['o'],
	);
	
	// 属性值里的 css3 前缀
	$css = preg_replace_callback('#(?<!@)(?<=;|^|\{|\s)([a-z\-]*)\s*:\s*.*?(?=;|$|\})#m', function ($mats) use (&$cssPerfixMap){
		$name = $mats[1];
		if(!isset($cssPerfixMap[$name])){
			return $mats[0];
		}
		$ret = $mats[0];
		foreach($cssPerfixMap[$name] as $perfix){
			$ret .= ';-' . $perfix . '-' . $mats[0];
		}
		return $ret;
	}, $css, -1);

	// 处理动画关键帧(加-webkit-
	$itr   = -1;
	$count = strlen($css);
	$keyframes = '';
	while($itr < $count){
		$itr = strpos($css, '@keyframes', $itr + 1);
		if($itr === false){
			break;
		}
		$keystart = $itr;
		$itr += 10; // strlen('@keyframes');
		
		$stack = 0;
		do{
			$itr++;
			$p1 = strpos($css, '{', $itr);
			$p2 = strpos($css, '}', $itr);
			
			$itr = $p1?min($p1,$p2):$p2;
			if(!$itr){
				Think::halt('broswer_css_perfix不能解析@keyframes段，大括号不匹配。');
			}
			if($css{$itr} == '{'){
				$stack++;
			} else{
				$stack--;
			}
		} while($stack);
		
		$itr++;
		$keyframes .= '@-webkit-'.substr($css, $keystart+1, $itr-$keystart);
	}
	if(!empty($keyframes)){
		$css .= "\n/* - AutoGen KeyFrames - */\n".$keyframes;
	}

	return $css;
}
