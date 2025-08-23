<?php
declare(strict_types=1);

function run_migrations(PDO $db): void {
	$db->exec('CREATE TABLE IF NOT EXISTS users (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT UNIQUE NOT NULL,
		password_hash TEXT NOT NULL,
		role TEXT NOT NULL DEFAULT "employee",
		email TEXT,
		first_name TEXT,
		last_name TEXT,
		code TEXT UNIQUE,
		bank_account TEXT,
		ifsc TEXT
	)');

	$db->exec('CREATE TABLE IF NOT EXISTS departments (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name TEXT UNIQUE NOT NULL
	)');

	$db->exec('CREATE TABLE IF NOT EXISTS employees (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		first_name TEXT NOT NULL,
		last_name TEXT NOT NULL,
		email TEXT UNIQUE NOT NULL,
		department_id INTEGER,
		base_salary REAL NOT NULL DEFAULT 0,
		allowances REAL NOT NULL DEFAULT 0,
		deductions REAL NOT NULL DEFAULT 0,
		created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(department_id) REFERENCES departments(id) ON DELETE SET NULL
	)');

	$db->exec('CREATE TABLE IF NOT EXISTS pay_periods (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		period_start TEXT NOT NULL,
		period_end TEXT NOT NULL,
		status TEXT NOT NULL DEFAULT "open"
	)');

	$db->exec('CREATE TABLE IF NOT EXISTS payroll_runs (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		pay_period_id INTEGER NOT NULL,
		run_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(pay_period_id) REFERENCES pay_periods(id) ON DELETE CASCADE
	)');

	$db->exec('CREATE TABLE IF NOT EXISTS payslips (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		payroll_run_id INTEGER NOT NULL,
		employee_id INTEGER NOT NULL,
		gross REAL NOT NULL,
		deductions REAL NOT NULL,
		net REAL NOT NULL,
		FOREIGN KEY(payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE
	)');

	// Attendance
	$db->exec('CREATE TABLE IF NOT EXISTS attendance (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		employee_id INTEGER NOT NULL,
		date TEXT NOT NULL,
		status TEXT NOT NULL, -- present, absent, remote, etc
		latitude REAL,
		longitude REAL,
		photo_path TEXT,
		created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE
	)');

	// Leave requests
	$db->exec('CREATE TABLE IF NOT EXISTS leaves (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		employee_id INTEGER NOT NULL,
		start_date TEXT NOT NULL,
		end_date TEXT NOT NULL,
		reason TEXT,
		status TEXT NOT NULL DEFAULT "pending", -- pending, approved, rejected
		created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE
	)');

	// Holidays
	$db->exec('CREATE TABLE IF NOT EXISTS holidays (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		date TEXT NOT NULL,
		title TEXT NOT NULL,
		description TEXT
	)');

	// Payheads and assignments
	$db->exec('CREATE TABLE IF NOT EXISTS payheads (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name TEXT NOT NULL,
		type TEXT NOT NULL, -- earning or deduction
		amount REAL NOT NULL DEFAULT 0
	)');
	$db->exec('CREATE TABLE IF NOT EXISTS employee_payheads (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		employee_id INTEGER NOT NULL,
		payhead_id INTEGER NOT NULL,
		amount REAL NOT NULL,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE,
		FOREIGN KEY(payhead_id) REFERENCES payheads(id) ON DELETE CASCADE
	)');

	$hasAdmin = (int)$db->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'] ?? 0;
	if ($hasAdmin === 0) {
		$username = 'admin';
		$passwordHash = password_hash('admin', PASSWORD_DEFAULT);
		$stmt = $db->prepare('INSERT INTO users (username, password_hash, role, email, first_name, last_name, code) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([$username, $passwordHash, 'admin', 'admin@example.com', 'Admin', 'User', 'ADM001']);
	}
}

