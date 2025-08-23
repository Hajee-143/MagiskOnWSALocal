<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();
$user = current_user();

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
			<?php if (is_admin()): ?>
				<a href="/index.php?page=profile" class="btn btn-outline-secondary btn-sm me-2">Profile (Admin)</a>
			<?php else: ?>
				<span class="me-2 small text-muted"><?php echo h(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></span>
				<a href="/index.php?page=profile" class="btn btn-outline-secondary btn-sm me-2">Profile</a>
			<?php endif; ?>
			<a href="/index.php?page=logout" class="btn btn-primary btn-sm">Logout</a>
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

	<div class="row g-3 mt-1">
		<?php if (is_admin()): ?>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=attendance">Attendance</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=leaves">Leaves</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=payheads">Payheads</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=holidays">Holidays</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=payslips">Payslips</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=employees">Employees</a></div>
		<?php else: ?>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=attendance">Attendance</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=leaves">Leaves</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=payslips">Payslips</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=payheads">Payheads</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=holidays">Holidays</a></div>
			<div class="col-md-3"><a class="btn w-100 btn-outline-primary" href="/index.php?page=profile">Profile</a></div>
		<?php endif; ?>
	</div>
</main>
