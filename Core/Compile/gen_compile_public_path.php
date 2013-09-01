<?php
if(isset($GLOBALS['COMPILE'])){
	echo_line("编译静态文件。");

	$array = get_defined_constants(true)['user'];
	// 生成less变量定义文件
	echo_line("\t生成less定义");
	$f = PUBLIC_PATH.'basevar.less';
	$less = '';
	foreach($array as $name => $val){
		if($val === '') continue;
		if(is_string($val)) $val= var_export($val, true);
		$less .= "@$name: $val;\n";
	}
	file_put_contents($f,$less);

	// 生成js全局变量定义文件
	echo_line("\t生成js定义");
	$f = PUBLIC_PATH.'basevar.js';
	$js = 'window.Think = '.json_encode($array).';';
	if(APP_DEBUG){
		echo_line("\tjs定义： 附加less");
		$js .= "\n".file_get_contents(THINK_PATH.'Tpl/debugless.js');
	}
	file_put_contents($f,$js);
	echo_line('');
	
	// 连接phpjs
	echo_line("\t生成js定义");
	$f = PUBLIC_PATH.'phpjs.js';
	$files = array_merge(glob(PUBLIC_PATH.'phpjs/*.js'),glob(PUBLIC_PATH.'phpjs/*/*.js'));
	foreach($files as $file){
		file_put_contents($f,file_get_contents($file)."\n");
	}
	echo_line('');
	
	
	require $alias['COM\\MyThink\\Strings'];
	$tmpl = file_get_contents(__FILE__);
	$tmpl = explode('/*[SIG]*/', $tmpl)[2];
	$tmpl = '<?php
		function echo_line($msg){echo $msg . "\n";}
		require "'.RUNTIME_PATH . APP_NAME .'/const.php";
		require "'.$alias['COM\\MyThink\\Strings'].'";
		' . $tmpl;
	
	//$tmpl = preg_replace('#^(use|namespace).*$#m', '', $tmpl);
	$tmpl = str_replace('unlink(', '@unlink(', $tmpl);
	file_put_contents(PUBLIC_PATH . '_recompile_static.php', $tmpl);
	
	return;
}
/*[SIG]*/

use \COM\MyThink\Strings;

if($argv[0] == '_recompile_static.php' && isset($argv[1])){
	$file = $argv[1];
	if(!is_file($file)){
		fwrite(STDERR, "No such file: $file.");
		die(100);
	}
	if(Strings::isEndWith($file, '.js')){
		$ret = compile_js($file);
	} elseif(Strings::isEndWith($file, '.coffee')){
		$ret = compile_coffee($file);
	} elseif(Strings::isEndWith($file, '.css')){
		$ret = compile_css($file);
	} elseif(Strings::isEndWith($file, '.less')){
		$ret = compile_less($file);
	}else{
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
foreach(find('js') as $file){
	compile_js($file);
}
foreach(find('coffee') as $file){
	compile_coffee($file);
}
foreach(find('css') as $file){
	compile_css($file);
}
foreach(find('less') as $file){
	compile_less($file);
}

/* Support */
function find($type){
	$files = [];
	exec('/bin/find ' . escapeshellarg(PUBLIC_PATH) . ' -name \'*.' . $type . "' -a -not -path '" . PUBLIC_PATH .
		 "get*' ", $files, $ret);

	return $files;
}

function compile_less($file){
	$base = str_replace(PUBLIC_PATH, PUBLIC_PATH . 'getcss/', Strings::blocktrim(delVer($file), '.less'));
	unlink($base . '.css');
	passthru('lessc --no-ie-compat --yui-compress -sm=on --rp='.escapeshellarg(PUBLIC_URL).' --include-path='.escapeshellarg(PUBLIC_PATH).' ' . escapeshellarg($file) . ' ' .
			 escapeshellarg($base . '.css'), $ret);
	echo_line("\t - " . $file . "\n\t\t \\lessc-> $base.css");

	return $ret;
}

function compile_css($file){
	if(Strings::isEndWith($file, '.min.css')){
		$base = PUBLIC_PATH . 'getcss/' . basename(Strings::blocktrim(delVer($file), '.min.css'));
		unlink($base . '.min.css');
		$ret  = copy($file, $base . '.css');
		echo_line("\t - " . $file . "\n\t\t \\copy-> $base.css");
	} else{
		$base = PUBLIC_PATH . 'getcss/' . basename(Strings::blocktrim(delVer($file), '.css'));
		unlink($base . '.css');
		passthru('uglifycss ' . escapeshellarg($file) . ' > ' . escapeshellarg($base . '.css'), $ret);
		echo_line("\t - " . $file . "\n\t\t \\uglifycss-> $base.css");
	}

	return $ret;
}

function compile_js($file){
	if(Strings::isEndWith($file, '.min.js')){
		$base = PUBLIC_PATH . 'getjs/' . basename(Strings::blocktrim(delVer($file), '.min.js'));
		unlink($base . '.js');
		$ret  = copy($file, $base . '.js');
		echo_line("\t - " . $file . "\n\t\t \\copy-> $base.js");
	} else{
		$base = PUBLIC_PATH . 'getjs/' . basename(Strings::blocktrim(delVer($file), '.js'));
		unlink($base . '.js');
		passthru('uglifyjs2 ' . escapeshellarg($file) . ' -o ' . escapeshellarg($base . '.js'), $ret);
		echo_line("\t - " . $file . "\n\t\t \\uglifyjs2-> $base.js");
	}

	return $ret;
}

function compile_coffee($file){
	echo_line("\t - " . $file);
	$base = str_replace(PUBLIC_PATH, PUBLIC_PATH . 'getjs/', Strings::blocktrim(delVer($file), '.coffee'));
	$dir  = dirname($base);
	if(!is_dir($dir)){
		mkdir($dir, 0777, true);
	}
	unlink($base . '.js');
	passthru('coffee -b -c -p ' . escapeshellarg($file) . ' | uglifyjs2 -o ' . escapeshellarg($base . '.js'), $ret);
	echo_line("\t - " . $file . "\n\t\t \\coffee|uglifyjs2-> $base.js");

	return $ret;
}

function delVer($file){
	return preg_replace('#[-_][0-9]+\.[0-9]+\.[0-9]+#', '', $file);
}
