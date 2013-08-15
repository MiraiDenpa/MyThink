<?php
define('APP_NAME', 'test');
define('ROOT_PATH', __DIR__ . '/');
define('APP_PATH', __DIR__ . '/test_app/');

define('APP_DEBUG', true);
define('SHOW_TRACE', true);
define('CORE_DEBUG', true);
define('APP_STATUS', 'debug');
define('LIB_PATH', APP_PATH);
define('BASE_LIB_PATH', __DIR__ . '/base/');

define('LOG_PATH', '/data/log/mirai/');

define('RUNTIME_PATH', __DIR__ . '/runtime/');

include '../mytp_include.php';
