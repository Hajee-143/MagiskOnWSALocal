<?php
declare(strict_types=1);

function get_database_connection(): PDO {
	global $CONFIG;
	$dbConf = $CONFIG['db'] ?? [];
	$driver = $dbConf['driver'] ?? 'mysql';
	if ($driver === 'mysql') {
		$host = $dbConf['host'] ?? 'localhost';
		$port = (int)($dbConf['port'] ?? 3306);
		$dbname = $dbConf['database'] ?? 'payroll';
		$user = $dbConf['username'] ?? 'root';
		$pass = $dbConf['password'] ?? '';
		$charset = $dbConf['charset'] ?? 'utf8mb4';
		$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
		$pdo = new PDO($dsn, $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
		// Strict mode and FK checks
		$pdo->exec('SET sql_mode="STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"');
		return $pdo;
	}

	// Fallback: sqlite (unused after migration)
	$databaseFile = DATA_PATH . '/database.sqlite';
	$pdo = new PDO('sqlite:' . $databaseFile);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	return $pdo;
}

