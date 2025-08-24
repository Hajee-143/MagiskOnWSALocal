<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$start = trim($_POST['period_start'] ?? '');
		$end = trim($_POST['period_end'] ?? '');
		if ($start === '' || $end === '') {
			$error = 'Start and end dates are required';
		} else {
			$stmt = $db->prepare('INSERT INTO pay_periods (period_start, period_end, status) VALUES (?,?,"open")');
			$stmt->execute([$start, $end]);
			$notice = 'Pay period created';
		}
	} else if ($action === 'close') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $db->prepare('UPDATE pay_periods SET status = "closed" WHERE id = ?');
		$stmt->execute([$id]);
		$notice = 'Pay period closed';
	}
}

$periods = $db->query('SELECT * FROM pay_periods ORDER BY id DESC')->fetchAll();
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Pay Periods</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="create">
				<div class="col-md-4">
					<label class="form-label">Start date</label>
					<input type="date" name="period_start" class="form-control" required>
				</div>
				<div class="col-md-4">
					<label class="form-label">End date</label>
					<input type="date" name="period_end" class="form-control" required>
				</div>
				<div class="col-md-4 align-self-end">
					<button type="submit" class="btn btn-primary">Add Period</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card shadow-sm">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr><th>ID</th><th>Start</th><th>End</th><th>Status</th><th></th></tr>
					</thead>
					<tbody>
						<?php foreach ($periods as $p): ?>
							<tr>
								<td><?php echo (int)$p['id']; ?></td>
								<td><?php echo h($p['period_start']); ?></td>
								<td><?php echo h($p['period_end']); ?></td>
								<td><?php echo h($p['status']); ?></td>
								<td class="text-end">
									<?php if ($p['status'] === 'open'): ?>
										<form method="post" class="d-inline">
											<input type="hidden" name="action" value="close">
											<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
											<button class="btn btn-sm btn-outline-secondary">Close</button>
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
