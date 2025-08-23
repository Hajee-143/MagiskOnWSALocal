<?php
declare(strict_types=1);

function get_database_connection(): PDO {
	$databaseFile = DATA_PATH . '/database.sqlite';
	$needInitPragmas = !file_exists($databaseFile);
	$pdo = new PDO('sqlite:' . $databaseFile);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	if ($needInitPragmas) {
		$pdo->exec('PRAGMA journal_mode=WAL;');
		$pdo->exec('PRAGMA foreign_keys=ON;');
	}
	return $pdo;
}

