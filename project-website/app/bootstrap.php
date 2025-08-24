<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('APP_PATH', BASE_PATH . '/app');
define('DATA_PATH', BASE_PATH . '/data');

if (!is_dir(DATA_PATH)) {
	mkdir(DATA_PATH, 0777, true);
}

// Load config
$CONFIG = require APP_PATH . '/config.php';

require_once APP_PATH . '/db.php';
require_once APP_PATH . '/migrations.php';
require_once APP_PATH . '/auth.php';
require_once APP_PATH . '/util.php';
require_once APP_PATH . '/mail.php';

$db = get_database_connection();
run_migrations($db);

