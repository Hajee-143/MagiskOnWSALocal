<?php
declare(strict_types=1);

function column_exists(PDO $db, string $table, string $column): bool {
	try {
		$stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
		$stmt->execute([$table, $column]);
		return (bool)$stmt->fetchColumn();
	} catch (Throwable $e) {
		return false;
	}
}

function run_migrations(PDO $db): void {
	$db->exec('CREATE TABLE IF NOT EXISTS users (
		id INT AUTO_INCREMENT PRIMARY KEY,
		username VARCHAR(191) NOT NULL UNIQUE,
		password_hash VARCHAR(255) NOT NULL,
		role ENUM("admin","employee") NOT NULL DEFAULT "employee",
		email VARCHAR(191) NULL,
		first_name VARCHAR(191) NULL,
		last_name VARCHAR(191) NULL,
		code VARCHAR(64) NULL UNIQUE,
		bank_account VARCHAR(64) NULL,
		ifsc VARCHAR(32) NULL,
		dob DATE NULL,
		phone VARCHAR(32) NULL,
		address VARCHAR(255) NULL,
		department_id INT NULL,
		photo_path VARCHAR(255) NULL,
		INDEX(department_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	// Extend users table for registration fields if missing
	if (!column_exists($db, 'users', 'dob')) {
		$db->exec('ALTER TABLE users ADD COLUMN dob TEXT');
	}
	if (!column_exists($db, 'users', 'phone')) {
		$db->exec('ALTER TABLE users ADD COLUMN phone TEXT');
	}
	if (!column_exists($db, 'users', 'address')) {
		$db->exec('ALTER TABLE users ADD COLUMN address TEXT');
	}
	if (!column_exists($db, 'users', 'department_id')) {
		$db->exec('ALTER TABLE users ADD COLUMN department_id INTEGER');
	}
	if (!column_exists($db, 'users', 'photo_path')) {
		$db->exec('ALTER TABLE users ADD COLUMN photo_path TEXT');
	}

	$db->exec('CREATE TABLE IF NOT EXISTS departments (
		id INT AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(191) NOT NULL UNIQUE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
	// Seed departments
	$defaultDepartments = ["IT", "Sales", "HR", "Finance", "Operations"];
	foreach ($defaultDepartments as $depName) {
		$stmt = $db->prepare('INSERT OR IGNORE INTO departments (name) VALUES (?)');
		$stmt->execute([$depName]);
	}

	$db->exec('CREATE TABLE IF NOT EXISTS employees (
		id INT AUTO_INCREMENT PRIMARY KEY,
		first_name VARCHAR(191) NOT NULL,
		last_name VARCHAR(191) NOT NULL,
		email VARCHAR(191) NOT NULL UNIQUE,
		department_id INT NULL,
		base_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
		allowances DECIMAL(12,2) NOT NULL DEFAULT 0,
		deductions DECIMAL(12,2) NOT NULL DEFAULT 0,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(department_id) REFERENCES departments(id) ON DELETE SET NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	$db->exec('CREATE TABLE IF NOT EXISTS pay_periods (
		id INT AUTO_INCREMENT PRIMARY KEY,
		period_start DATE NOT NULL,
		period_end DATE NOT NULL,
		status ENUM("open","closed") NOT NULL DEFAULT "open"
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	$db->exec('CREATE TABLE IF NOT EXISTS payroll_runs (
		id INT AUTO_INCREMENT PRIMARY KEY,
		pay_period_id INT NOT NULL,
		run_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(pay_period_id) REFERENCES pay_periods(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	$db->exec('CREATE TABLE IF NOT EXISTS payslips (
		id INT AUTO_INCREMENT PRIMARY KEY,
		payroll_run_id INT NOT NULL,
		employee_id INT NOT NULL,
		gross DECIMAL(12,2) NOT NULL,
		deductions DECIMAL(12,2) NOT NULL,
		net DECIMAL(12,2) NOT NULL,
		FOREIGN KEY(payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	// Attendance
	$db->exec('CREATE TABLE IF NOT EXISTS attendance (
		id INT AUTO_INCREMENT PRIMARY KEY,
		employee_id INT NOT NULL,
		date DATE NOT NULL,
		status VARCHAR(32) NOT NULL,
		latitude DOUBLE NULL,
		longitude DOUBLE NULL,
		photo_path VARCHAR(255) NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	// Leave requests
	$db->exec('CREATE TABLE IF NOT EXISTS leaves (
		id INT AUTO_INCREMENT PRIMARY KEY,
		employee_id INT NOT NULL,
		start_date DATE NOT NULL,
		end_date DATE NOT NULL,
		reason VARCHAR(255) NULL,
		status ENUM("pending","approved","rejected") NOT NULL DEFAULT "pending",
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	// Holidays
	$db->exec('CREATE TABLE IF NOT EXISTS holidays (
		id INT AUTO_INCREMENT PRIMARY KEY,
		date DATE NOT NULL,
		title VARCHAR(191) NOT NULL,
		description VARCHAR(255) NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	// Payheads and assignments
	$db->exec('CREATE TABLE IF NOT EXISTS payheads (
		id INT AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(191) NOT NULL,
		type ENUM("earning","deduction") NOT NULL,
		amount DECIMAL(12,2) NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
	$db->exec('CREATE TABLE IF NOT EXISTS employee_payheads (
		id INT AUTO_INCREMENT PRIMARY KEY,
		employee_id INT NOT NULL,
		payhead_id INT NOT NULL,
		amount DECIMAL(12,2) NOT NULL,
		FOREIGN KEY(employee_id) REFERENCES employees(id) ON DELETE CASCADE,
		FOREIGN KEY(payhead_id) REFERENCES payheads(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	$hasAdmin = (int)$db->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'] ?? 0;
	if ($hasAdmin === 0) {
		$username = 'admin';
		$passwordHash = password_hash('admin', PASSWORD_DEFAULT);
		$stmt = $db->prepare('INSERT INTO users (username, password_hash, role, email, first_name, last_name, code) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([$username, $passwordHash, 'admin', 'admin@example.com', 'Admin', 'User', 'ADM001']);
	}
}

