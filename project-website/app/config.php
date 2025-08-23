<?php
declare(strict_types=1);

return [
	'app_name' => 'Payroll',
	'from_email' => 'no-reply@example.com',
	'from_name' => 'Payroll System',
	'geofence_default_radius_m' => 200,
	// Database (MySQL)
	'db' => [
		'driver' => 'mysql',
		'host' => 'localhost',
		'port' => 3306,
		'database' => 'payroll',
		'username' => 'root',
		'password' => '',
		'charset' => 'utf8mb4',
	],
];

