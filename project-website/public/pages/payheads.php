<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$error = '';
$notice = '';

if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$name = trim($_POST['name'] ?? '');
		$type = trim($_POST['type'] ?? 'earning');
		$amount = (float)($_POST['amount'] ?? 0);
		if ($name === '' || !in_array($type, ['earning','deduction'], true)) {
			$error = 'Name and type are required';
		} else {
			$stmt = $db->prepare('INSERT INTO payheads (name, type, amount) VALUES (?,?,?)');
			$stmt->execute([$name, $type, $amount]);
			$notice = 'Payhead created';
		}
	} else if ($action === 'assign') {
		$employeeId = (int)($_POST['employee_id'] ?? 0);
		$payheadId = (int)($_POST['payhead_id'] ?? 0);
		$amount = (float)($_POST['amount'] ?? 0);
		if ($employeeId > 0 && $payheadId > 0) {
			$stmt = $db->prepare('INSERT INTO employee_payheads (employee_id, payhead_id, amount) VALUES (?,?,?)');
			$stmt->execute([$employeeId, $payheadId, $amount]);
			$notice = 'Assigned to employee';
		}
	}
}

$payheads = $db->query('SELECT * FROM payheads ORDER BY id DESC')->fetchAll();
$employees = $db->query("SELECT id, first_name, last_name FROM users WHERE role='employee' ORDER BY first_name")->fetchAll();
$assignments = $db->query('SELECT ep.id, u.first_name, u.last_name, p.name, p.type, ep.amount FROM employee_payheads ep JOIN users u ON u.id=ep.employee_id JOIN payheads p ON p.id=ep.payhead_id ORDER BY ep.id DESC')->fetchAll();
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Payheads</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<?php if (is_admin()): ?>
	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<h2 class="h6">Create Payhead</h2>
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="create">
				<div class="col-md-4">
					<label class="form-label">Name</label>
					<input name="name" class="form-control" placeholder="Bonus / PF / Medical / Transport" required>
				</div>
				<div class="col-md-3">
					<label class="form-label">Type</label>
					<select name="type" class="form-select">
						<option value="earning">Earning</option>
						<option value="deduction">Deduction</option>
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label">Default Amount (₹)</label>
					<input type="number" step="0.01" name="amount" class="form-control" value="0">
				</div>
				<div class="col-md-2 align-self-end">
					<button class="btn btn-primary">Create</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<h2 class="h6">Assign to Employee</h2>
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="assign">
				<div class="col-md-4">
					<label class="form-label">Employee</label>
					<select name="employee_id" class="form-select" required>
						<option value="">-- Select --</option>
						<?php foreach ($employees as $e): ?>
							<option value="<?php echo (int)$e['id']; ?>"><?php echo h($e['first_name'] . ' ' . $e['last_name']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-4">
					<label class="form-label">Payhead</label>
					<select name="payhead_id" class="form-select" required>
						<option value="">-- Select --</option>
						<?php foreach ($payheads as $p): ?>
							<option value="<?php echo (int)$p['id']; ?>"><?php echo h($p['name']); ?> (<?php echo h($p['type']); ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label">Amount (₹)</label>
					<input type="number" step="0.01" name="amount" class="form-control" value="0">
				</div>
				<div class="col-md-1 align-self-end">
					<button class="btn btn-primary">Assign</button>
				</div>
			</form>
		</div>
	</div>
	<?php endif; ?>

	<div class="card shadow-sm">
		<div class="card-body">
			<h2 class="h6">Assignments</h2>
			<div class="table-responsive">
				<table class="table align-middle">
					<thead><tr><th>Employee</th><th>Payhead</th><th>Type</th><th class="text-end">Amount (₹)</th></tr></thead>
					<tbody>
						<?php foreach ($assignments as $a): ?>
							<tr>
								<td><?php echo h($a['first_name'] . ' ' . $a['last_name']); ?></td>
								<td><?php echo h($a['name']); ?></td>
								<td><?php echo h($a['type']); ?></td>
								<td class="text-end"><?php echo number_format((float)$a['amount'], 2); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</main>
