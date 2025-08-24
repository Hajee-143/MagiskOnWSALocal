<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'run') {
		$periodId = (int)($_POST['pay_period_id'] ?? 0);
		if ($periodId <= 0) {
			$error = 'Select a pay period';
		} else {
			try {
				$db->beginTransaction();
				$stmt = $db->prepare('INSERT INTO payroll_runs (pay_period_id) VALUES (?)');
				$stmt->execute([$periodId]);
				$runId = (int)$db->lastInsertId();
				$employees = $db->query('SELECT id, base_salary, allowances, deductions FROM employees')->fetchAll();
				$ins = $db->prepare('INSERT INTO payslips (payroll_run_id, employee_id, gross, deductions, net) VALUES (?,?,?,?,?)');
				foreach ($employees as $e) {
					$gross = (float)$e['base_salary'] + (float)$e['allowances'];
					$ded = (float)$e['deductions'];
					$net = $gross - $ded;
					$ins->execute([$runId, (int)$e['id'], $gross, $ded, $net]);
				}
				$db->commit();
				$notice = 'Payroll run created for period #' . $periodId;
			} catch (Throwable $e) {
				$db->rollBack();
				$error = 'Failed to run payroll';
			}
		}
	}
}

$periods = $db->query('SELECT * FROM pay_periods ORDER BY id DESC')->fetchAll();
$runs = $db->query('SELECT r.id, r.run_at, r.pay_period_id, COUNT(p.id) AS payslips
	FROM payroll_runs r LEFT JOIN payslips p ON p.payroll_run_id = r.id
	GROUP BY r.id ORDER BY r.id DESC')->fetchAll();
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Payroll</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="run">
				<div class="col-md-6">
					<label class="form-label">Pay period</label>
					<select name="pay_period_id" class="form-select" required>
						<option value="">-- Select period --</option>
						<?php foreach ($periods as $p): ?>
							<option value="<?php echo (int)$p['id']; ?>">#<?php echo (int)$p['id']; ?> — <?php echo h($p['period_start']); ?> to <?php echo h($p['period_end']); ?> (<?php echo h($p['status']); ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-3 align-self-end">
					<button type="submit" class="btn btn-primary">Run Payroll</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card shadow-sm">
		<div class="card-body">
			<h2 class="h6">Recent Runs</h2>
			<div class="table-responsive">
				<table class="table align-middle">
					<thead><tr><th>ID</th><th>Period</th><th>Run at</th><th>Payslips</th></tr></thead>
					<tbody>
						<?php foreach ($runs as $r): ?>
							<tr>
								<td><?php echo (int)$r['id']; ?></td>
								<td>#<?php echo (int)$r['pay_period_id']; ?></td>
								<td><?php echo h($r['run_at']); ?></td>
								<td><?php echo (int)$r['payslips']; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</main>
