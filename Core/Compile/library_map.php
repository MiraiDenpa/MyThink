<?php

$map = array();
// ORG，COM层
$lib = dir(LIBRARY_PATH);
while($provider = $lib->read()){
	if(strpos($provider, '.') === 0){
		continue;
	}
	$provider_dir = LIBRARY_PATH . $provider . '/';

	// Crypt,Net,Util,GongT 层
	$lib_p = dir($provider_dir);
	while($category = $lib_p->read()){
		if(strpos($category, '.') === 0){
			continue;
		}
		$category_dir = $provider_dir . $category . '/';

		// 具体文件（如Page.class.php）层
		$lib_c = dir($category_dir);
		while($file = $lib_c->read()){
			if(!is_file($category_dir . $file)){
				continue;
			}
			$name = str_replace('.class.php', '', $file);

			$map[strtolower("$provider\\$category\\$name")] = $provider_dir . $file;
		}
		$lib_c->close();
		// 具体文件（如Page.class.php）层 END
	}
	$lib_p->close();
	// Crypt,Net,Util,GongT 层 END
}
$lib->close();
return $map;
