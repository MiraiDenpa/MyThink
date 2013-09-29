<?php
require $alias['COM\\MyThink\\Strings'];
use \COM\MyThink\Strings;

if(isset($GLOBALS['COMPILE'])){
	echo_line("编译静态文件。");

	$array = get_defined_constants(true)['user'];
	// 生成less变量定义文件
	echo_line("\t生成less定义");
	$f    = PUBLIC_PATH . 'basevar.less';
	$less = '';
	foreach($array as $name => $val){
		if($val === ''){
			continue;
		}
		if(is_string($val)){
			$val = var_export($val, true);
		}
		$less .= "@$name: $val;\n";
	}
	file_put_contents($f, $less);

	// 生成js全局变量定义文件
	echo_line("\t生成js定义");
	$f = PUBLIC_PATH . 'jslib/basevar.js';
	foreach($array as $name => $value){
		if(Strings::isEndWith($name, '_PATH') || Strings::isStartWith($name, 'TMPL_') ||
		   Strings::isStartWith($name, 'DB_') || Strings::isStartWith($name, 'COOKIE_') ||
		   Strings::isEndWith($name, '_DEBUG') || Strings::isStartWith($name, 'LANG_') ||
		   Strings::isEndWith($name, '_FILE') || Strings::isStartWith($name, 'MAIL_')
		){
			unset($array[$name]);
			continue;
		}
		if(in_array($name, ['APP_NAME'])){
			unset($array[$name]);
			continue;
		}
	}
	$array['URL_MAP'] = $GLOBALS['URL_MAP'];
	$js               = 'window.Think = ' . json_encode($array, JSON_PRETTY_PRINT) . ';';
	$js .= "\nwindow.JS_DEBUG = " . json_encode(JS_DEBUG) . ';';
	if(APP_DEBUG){
		echo_line("\tjs定义： 附加less");
		$js .= "\n" . file_get_contents(THINK_PATH . 'Tpl/debugless.js');
	}
	file_put_contents($f, $js);
	echo_line('');

	$tmpl = file_get_contents(__FILE__);
	$tmpl = explode('/*[SIG]*/', $tmpl)[2];
	$tmpl = '<?php
		use \COM\MyThink\Strings;
		function echo_line($msg){echo $msg . "\n";}
		require "' . RUNTIME_PATH . APP_NAME . '/functions.php";
		require "' . RUNTIME_PATH . APP_NAME . '/const.php";
		require "' . $alias['COM\\MyThink\\Strings'] . '";
		' . $tmpl;

	//$tmpl = preg_replace('#^(use|namespace).*$#m', '', $tmpl);
	$tmpl = str_replace('unlink(', '@unlink(', $tmpl);
	file_put_contents(PUBLIC_PATH . '_recompile_static.php', $tmpl);

	return;
}
/*[SIG]*/

/**  */
function export_php($data){
	$export = var_export($data, true);
	return preg_replace([
						'#\s+array\s+\(\s+\)#',
						'#\s+array\s+\(#',
						'#array \(#',
						'#\)#'
						],
						[
						' []',
						' [',
						' [',
						']'
						],
						$export
	);
}

if($argv[0] == '_recompile_static.php' && isset($argv[1])){
	$file = $argv[1];
	if(!is_file($file)){
		fwrite(STDERR, "No such file: $file.");
		die(100);
	}
	if(Strings::isEndWith($file, '.js')){
		$ret = compile_js($file);
	} elseif(Strings::isEndWith($file, '.css')){
		$ret = compile_css($file);
	} elseif(Strings::isEndWith($file, '.less')){
		$ret = compile_less($file);
	} else{
		$ret = 255;
	}
	echo "\nReturn $ret\n";
	exit($ret);
}

if(!is_dir(PUBLIC_PATH . 'getjs')){
	mkdir(PUBLIC_PATH . 'getjs', 0777, true);
}
if(!is_dir(PUBLIC_PATH . 'getcss')){
	mkdir(PUBLIC_PATH . 'getcss', 0777, true);
}

// Run Actions
if(!is_file(PUBLIC_PATH . 'BrowserLib.components.php')){
	touch(PUBLIC_PATH . 'BrowserLib.components.php');
}
$fileset = [];
require PUBLIC_PATH . 'BrowserLib.components.php';
require PUBLIC_PATH . 'BrowserLib.php';
$mergeIndex = function ($arr) use (&$fileset){
	array_walk($arr,
		function ($val) use (&$fileset){
			$val = str_replace(PUBLIC_PATH . '', '', $val);
			$pth = explode('/', $val);
			if(count($pth) == 1){
				$name = trim($val, '/');
			} else{
				$name = $pth[0] . '/' . basename($val);
			}
			$val            = '/' . trim($val, '/');
			$fileset[$name] = $val;
		}
	);
};
$mergeIndex($files = find('js'));
$line_js = array_map('compile_js', $files);
$mergeIndex($files = find('css'));
$line_css = array_map('compile_css', $files);
$mergeIndex($files = find('less'));
$line_less = array_map('compile_less', $files);

$bash_script = array_merge(['#!/bin/bash', 'cd `dirname $BASH_SOURCE`'], $line_css, $line_less, $line_js);
$bash_script = implode("\n", $bash_script);
$bash_script = str_replace(PUBLIC_PATH, './', $bash_script);
file_put_contents(PUBLIC_PATH . 'recompile.bash', $bash_script);
chmod(PUBLIC_PATH . 'recompile.bash', 0777);

file_put_contents(PUBLIC_PATH . 'lastmodify.timestamp', time());

// phpjs
$jslib = [];
foreach($fileset as $key => $file){
	if(Strings::isStartWith($key, 'jslib/')){
		$jslib[explode('.', basename($key))[0]] = $file;
	}
	if(Strings::isStartWith($key, 'phpjs/')){
		$jslib['phpjs'][] = $file;
	}
	if(Strings::isStartWith($key, 'jslib-gt/')){
		$key           = Strings::blocktrim($file, '/jslib-gt/', STR_TRIM_LEFT);
		$key           = explode('/', $key)[0];
		$jslib[$key][] = 'jslib-gt/' . basename($file);
	}
}

ksort($fileset);
$php = '<?php $fileset =  ' . export_php($fileset) . ";\n";

$php .= '$libraries = ' . export_php($jslib) . ";\n";

file_put_contents(PUBLIC_PATH . 'BrowserLib.components.php', $php);

/* Support */
/**  */
function find($type){
	$files = [];
	exec('/bin/find ' . escapeshellarg(PUBLIC_PATH) . ' -name \'*.' . $type . "' -a -not -path '*/get*' ",
		 $files,
		 $ret
	);

	return $files;
}

/**  */
function compile_less($file){
	$base = PUBLIC_PATH . 'getcss/' . pubfile_guid($file);
	$prel = str_replace(PUBLIC_PATH, PUBLIC_URL . '/', dirname($file));
	$cmd  = 'lessc --no-ie-compat --yui-compress -sm=on --rp=' . escapeshellarg($prel . '/') . ' --include-path=' .
			escapeshellarg(PUBLIC_PATH) . ' ' . escapeshellarg($file) . ' ' . escapeshellarg($base . '.css');
	$cmd  = 'echo -e "\033[38;5;10m正在编译 ' . $base . '.css\n\t\t-> ' . $cmd . '\033[0m"' . "\n" . $cmd;
	$cmd .= "\nif [ $? -ne 0 ]; then\n\techo -e '\033[38;5;9m失败，原样复制...\033[0m';cp -f " . escapeshellarg($file) . ' ' .
			escapeshellarg($base . '.css') . "\nfi";
	return $cmd;
}

/**  */
function compile_css($file){
	$base = PUBLIC_PATH . 'getcss/' . pubfile_guid($file);
	$prel = str_replace(PUBLIC_PATH, PUBLIC_URL . '/', dirname($file));
	$cmd  = 'lessc --yui-compress -sm=on --rp=' . escapeshellarg($prel . '/') . ' ' . escapeshellarg($file) .
			' > ' . escapeshellarg($base . '.css');
	$cmd  = 'echo -e "\033[38;5;10m正在压缩 ' . $base . '.css\n\t\t-> ' . $cmd . '\033[0m"' . "\n" . $cmd;
	$cmd .= "\nif [ $? -ne 0 ]; then\n\techo -e '\033[38;5;9m失败，原样复制...\033[0m';cp -f " . escapeshellarg($file) . ' ' .
			escapeshellarg($base . '.css') . "\nfi";
	return $cmd;
}

/**  */
function compile_js($file){
	$base = PUBLIC_PATH . 'getjs/' . pubfile_guid($file);
	//$cmd  = 'unlink ' . escapeshellarg($base . '.js') . " 2>/dev/null\n";
	$run = 'yuicompressor --type js --nomunge --line-break 200 ' . escapeshellarg($file) .
		   ' -o ' . escapeshellarg($base . '.js');
	$cmd = 'echo -e "\033[38;5;10m正在压缩 ' . $base . '.css\n\t\t-> ' . $run . '\033[0m"' . "\n";
	$cmd .= 'ERR=$(' . $run . ' 2>&1 >/dev/tty)';
	$cmd .= "\nif [ -n \"\$ERR\" ]; then\n\techo -e '\033[38;5;9m'\${ERR}'\\n失败，原样复制...\033[0m';cp -f " .
			escapeshellarg($file) . ' ' . escapeshellarg($base . '.js') . "\nfi";
	return $cmd;
}

/**  */
function delVer($file){
	return preg_replace('#[-_][0-9]+\.[0-9]+\.[0-9]+#', '', $file);
}

