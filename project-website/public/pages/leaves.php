<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$user = current_user();
$error = '';
$notice = '';

if (is_employee() && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$start = trim($_POST['start_date'] ?? '');
	$end = trim($_POST['end_date'] ?? '');
	$reason = trim($_POST['reason'] ?? '');
	if ($start === '' || $end === '') {
		$error = 'Start and end date are required';
	} else {
		$stmt = $db->prepare('INSERT INTO leaves (employee_id, start_date, end_date, reason) VALUES (?,?,?,?)');
		$stmt->execute([(int)$user['id'], $start, $end, $reason]);
		$notice = 'Leave request submitted';
		// Notify admin
		$admins = $db->query("SELECT email FROM users WHERE role='admin' AND email IS NOT NULL")->fetchAll();
		$emails = array_values(array_filter(array_map(fn($r) => $r['email'] ?? '', $admins)));
		if (!empty($emails)) {
			broadcast_email($emails, 'New leave request', 'A new leave request has been submitted.');
		}
	}
}

if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	$action = $_POST['action'];
	$id = (int)($_POST['id'] ?? 0);
	if (in_array($action, ['approve','reject'], true) && $id > 0) {
		$status = $action === 'approve' ? 'approved' : 'rejected';
		$stmt = $db->prepare('UPDATE leaves SET status=? WHERE id=?');
		$stmt->execute([$status, $id]);
		$emp = $db->prepare('SELECT u.email FROM leaves l JOIN users u ON u.id=l.employee_id WHERE l.id=?');
		$emp->execute([$id]);
		$row = $emp->fetch();
		if ($row && !empty($row['email'])) {
			send_email($row['email'], 'Leave ' . $status, 'Your leave request has been ' . $status . '.');
		}
		$notice = 'Leave ' . $status;
	}
}

// Data views
if (is_admin()) {
	$leaves = $db->query('SELECT l.*, u.first_name, u.last_name FROM leaves l JOIN users u ON u.id=l.employee_id ORDER BY l.id DESC')->fetchAll();
} else {
	$stmt = $db->prepare('SELECT * FROM leaves WHERE employee_id=? ORDER BY id DESC');
	$stmt->execute([(int)$user['id']]);
	$leaves = $stmt->fetchAll();
}
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Leaves</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<?php if (is_employee()): ?>
	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<form method="post" class="row g-3">
				<div class="col-md-4">
					<label class="form-label">Start date</label>
					<input type="date" name="start_date" class="form-control" required>
				</div>
				<div class="col-md-4">
					<label class="form-label">End date</label>
					<input type="date" name="end_date" class="form-control" required>
				</div>
				<div class="col-md-4">
					<label class="form-label">Reason</label>
					<input type="text" name="reason" class="form-control">
				</div>
				<div class="col-12">
					<button class="btn btn-primary">Apply Leave</button>
				</div>
			</form>
		</div>
	</div>
	<?php endif; ?>

	<div class="card shadow-sm">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr>
							<th>ID</th>
							<?php if (is_admin()): ?><th>Employee</th><?php endif; ?>
							<th>Start</th><th>End</th><th>Status</th><th>Reason</th><th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($leaves as $l): ?>
							<tr>
								<td><?php echo (int)$l['id']; ?></td>
								<?php if (is_admin()): ?><td><?php echo h(($l['first_name'] ?? '') . ' ' . ($l['last_name'] ?? '')); ?></td><?php endif; ?>
								<td><?php echo h($l['start_date']); ?></td>
								<td><?php echo h($l['end_date']); ?></td>
								<td><?php echo h($l['status']); ?></td>
								<td><?php echo h($l['reason'] ?? ''); ?></td>
								<td class="text-end">
									<?php if (is_admin() && $l['status'] === 'pending'): ?>
										<form method="post" class="d-inline">
											<input type="hidden" name="id" value="<?php echo (int)$l['id']; ?>">
											<button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
											<button name="action" value="reject" class="btn btn-sm btn-outline-danger">Reject</button>
										</form>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</main>
