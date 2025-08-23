<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$stmtCounts = [
	'employees' => $db->query('SELECT COUNT(*) AS c FROM employees')->fetch()['c'] ?? 0,
	'departments' => $db->query('SELECT COUNT(*) AS c FROM departments')->fetch()['c'] ?? 0,
	'periods' => $db->query('SELECT COUNT(*) AS c FROM pay_periods')->fetch()['c'] ?? 0,
];
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1 class="h4 mb-0">Dashboard</h1>
		<div>
			<a href="/index.php?page=logout" class="btn btn-outline-secondary btn-sm">Logout</a>
		</div>
	</div>
	<div class="row g-3">
		<div class="col-md-4">
			<div class="card shadow-sm">
				<div class="card-body">
					<div class="text-muted small">Employees</div>
					<div class="display-6"><?php echo (int)$stmtCounts['employees']; ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card shadow-sm">
				<div class="card-body">
					<div class="text-muted small">Departments</div>
					<div class="display-6"><?php echo (int)$stmtCounts['departments']; ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card shadow-sm">
				<div class="card-body">
					<div class="text-muted small">Pay Periods</div>
					<div class="display-6"><?php echo (int)$stmtCounts['periods']; ?></div>
				</div>
			</div>
		</div>
	</div>
</main>
