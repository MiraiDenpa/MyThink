<?php
echo_line('ALIAS: ');
$alias = merge_config(array(
						   '核心导入定义' => THINK_PATH . 'Conf/alias.php',
						   '第三方类定义' => __DIR__ . '/library_map.php',
						   '用户定义'   => CONF_PATH . 'alias.php',
					  ));

echo_line("\t - 共".count($alias)."个类");

return 'global $_think_import_alias;$_think_import_alias = ' . var_export($alias, true) . ';';
