<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$payslips = $db->query('SELECT p.id, p.gross, p.deductions, p.net, p.payroll_run_id, r.run_at, e.first_name, e.last_name
FROM payslips p
JOIN payroll_runs r ON r.id = p.payroll_run_id
JOIN employees e ON e.id = p.employee_id
ORDER BY p.id DESC')->fetchAll();
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Payslips</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<div class="card shadow-sm">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table align-middle">
					<thead><tr><th>ID</th><th>Employee</th><th>Run</th><th class="text-end">Gross</th><th class="text-end">Deductions</th><th class="text-end">Net</th></tr></thead>
					<tbody>
						<?php foreach ($payslips as $ps): ?>
							<tr>
								<td><?php echo (int)$ps['id']; ?></td>
								<td><?php echo h($ps['first_name'] . ' ' . $ps['last_name']); ?></td>
								<td>#<?php echo (int)$ps['payroll_run_id']; ?> — <?php echo h($ps['run_at']); ?></td>
								<td class="text-end"><?php echo number_format((float)$ps['gross'], 2); ?></td>
								<td class="text-end"><?php echo number_format((float)$ps['deductions'], 2); ?></td>
								<td class="text-end"><?php echo number_format((float)$ps['net'], 2); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</main>
