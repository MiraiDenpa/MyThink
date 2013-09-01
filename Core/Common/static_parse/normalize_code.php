<?php
/**
 * 清理PHP代码，删除所有不必要的空格和非文档注释
 *
 * @param string $content
 *
 * @return string
 */
function normalize_code($content){
	$stripStr = '';
	//分析php源码
	$tokens     = token_get_all($content);
	for($i = 0, $j = count($tokens); $i < $j; $i++){
		if(is_string($tokens[$i])){
			$stripStr .= $tokens[$i];
		} else{
			switch($tokens[$i][0]){
			case T_COMMENT:
				break;
			case T_OPEN_TAG_WITH_ECHO:
				$stripStr .= "<?php echo ";
				break;
			case T_OPEN_TAG:
				$stripStr .= "<?php\n";
				break;
			case T_START_HEREDOC:
				$stripStr .= "<<<THINK\n";
				break;
			case T_END_HEREDOC:
				$stripStr .= "THINK;\n";
				for($k = $i + 1; $k < $j; $k++){
					if(is_string($tokens[$k]) && $tokens[$k] == ';'){
						$i = $k;
						break;
					} else if($tokens[$k][0] == T_CLOSE_TAG){
						break;
					}
				}
				break;
			default:
				$stripStr .= $tokens[$i][1];
			}
		}
	}

	return $stripStr;
}
