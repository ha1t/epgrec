<?php
/**
*
*
*/

define('ROOT_DIR', dirname(__DIR__) . '/');
define('APP_DIR', ROOT_DIR);

require_once ROOT_DIR.'dietcake/dietcake.php';

require_once CONFIG_DIR.'bootstrap.php';
require_once CONFIG_DIR.'core.php';

Dispatcher::invoke();
