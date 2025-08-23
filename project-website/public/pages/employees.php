<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$first = trim($_POST['first_name'] ?? '');
		$last = trim($_POST['last_name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$deptId = (int)($_POST['department_id'] ?? 0);
		$base = (float)($_POST['base_salary'] ?? 0);
		$allow = (float)($_POST['allowances'] ?? 0);
		$ded = (float)($_POST['deductions'] ?? 0);
		if ($first === '' || $last === '' || $email === '') {
			$error = 'First, last name and email are required';
		} else {
			try {
				$stmt = $db->prepare('INSERT INTO employees (first_name, last_name, email, department_id, base_salary, allowances, deductions) VALUES (?,?,?,?,?,?,?)');
				$stmt->execute([$first, $last, $email, $deptId ?: null, $base, $allow, $ded]);
				$notice = 'Employee created';
			} catch (Throwable $e) {
				$error = 'Could not create employee';
			}
		}
	} else if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		if ($id > 0) {
			$stmt = $db->prepare('DELETE FROM employees WHERE id = ?');
			$stmt->execute([$id]);
			$notice = 'Employee deleted';
		}
	}
}

$departments = $db->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
$employees = $db->query('SELECT e.*, d.name AS department_name FROM employees e LEFT JOIN departments d ON d.id = e.department_id ORDER BY e.created_at DESC')->fetchAll();
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Employees</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="create">
				<div class="col-md-3">
					<label class="form-label">First name</label>
					<input type="text" name="first_name" class="form-control" required>
				</div>
				<div class="col-md-3">
					<label class="form-label">Last name</label>
					<input type="text" name="last_name" class="form-control" required>
				</div>
				<div class="col-md-3">
					<label class="form-label">Email</label>
					<input type="email" name="email" class="form-control" required>
				</div>
				<div class="col-md-3">
					<label class="form-label">Department</label>
					<select name="department_id" class="form-select">
						<option value="">-- None --</option>
						<?php foreach ($departments as $d): ?>
							<option value="<?php echo (int)$d['id']; ?>"><?php echo h($d['name']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label">Base salary</label>
					<input type="number" step="0.01" name="base_salary" class="form-control" value="0">
				</div>
				<div class="col-md-2">
					<label class="form-label">Allowances</label>
					<input type="number" step="0.01" name="allowances" class="form-control" value="0">
				</div>
				<div class="col-md-2">
					<label class="form-label">Deductions</label>
					<input type="number" step="0.01" name="deductions" class="form-control" value="0">
				</div>
				<div class="col-md-2 align-self-end">
					<button type="submit" class="btn btn-primary">Add Employee</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card shadow-sm">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr>
							<th>ID</th><th>Name</th><th>Email</th><th>Department</th><th class="text-end">Base</th><th class="text-end">Allow</th><th class="text-end">Deduct</th><th class="text-end">Net</th><th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($employees as $e): $gross = (float)$e['base_salary'] + (float)$e['allowances']; $net = $gross - (float)$e['deductions']; ?>
							<tr>
								<td><?php echo (int)$e['id']; ?></td>
								<td><?php echo h($e['first_name'] . ' ' . $e['last_name']); ?></td>
								<td><?php echo h($e['email']); ?></td>
								<td><?php echo h($e['department_name'] ?? ''); ?></td>
								<td class="text-end"><?php echo number_format((float)$e['base_salary'], 2); ?></td>
								<td class="text-end"><?php echo number_format((float)$e['allowances'], 2); ?></td>
								<td class="text-end"><?php echo number_format((float)$e['deductions'], 2); ?></td>
								<td class="text-end"><?php echo number_format($net, 2); ?></td>
								<td class="text-end">
									<form method="post" class="d-inline">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="id" value="<?php echo (int)$e['id']; ?>">
										<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this employee?')">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</main>
