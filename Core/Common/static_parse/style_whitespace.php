<?php
/**
 * @param string $style
 *
 * @return string
 */
function style_whitespace($style){
	return preg_replace([
						'#;\s+#',
						'#\s*:\s*#',
						'#^\s+#m',
						'#\s+$#m',
						'#\n#',
						],[
						  ';',
						  ':',
						  '',
						  '',
						  '',
						  ],$style);
}
