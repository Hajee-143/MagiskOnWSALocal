<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$error = '';
$notice = '';

if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$date = trim($_POST['date'] ?? '');
		$title = trim($_POST['title'] ?? '');
		$description = trim($_POST['description'] ?? '');
		if ($date === '' || $title === '') {
			$error = 'Date and title are required';
		} else {
			$stmt = $db->prepare('INSERT INTO holidays (date, title, description) VALUES (?,?,?)');
			$stmt->execute([$date, $title, $description]);
			$notice = 'Holiday added';
			// Notify employees
			$emps = $db->query("SELECT email FROM users WHERE role='employee' AND email IS NOT NULL")->fetchAll();
			$emails = array_values(array_filter(array_map(fn($r) => $r['email'] ?? '', $emps)));
			if ($emails) {
				broadcast_email($emails, 'New holiday announced', $title . ' on ' . $date);
			}
		}
	} else if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $db->prepare('DELETE FROM holidays WHERE id=?');
		$stmt->execute([$id]);
		$notice = 'Holiday deleted';
	}
}

$holidays = $db->query('SELECT * FROM holidays ORDER BY date DESC')->fetchAll();
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Holidays</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<?php if (is_admin()): ?>
	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="create">
				<div class="col-md-3">
					<label class="form-label">Date</label>
					<input type="date" name="date" class="form-control" required>
				</div>
				<div class="col-md-5">
					<label class="form-label">Title</label>
					<input type="text" name="title" class="form-control" required>
				</div>
				<div class="col-md-4">
					<label class="form-label">Description</label>
					<input type="text" name="description" class="form-control">
				</div>
				<div class="col-12">
					<button class="btn btn-primary">Add Holiday</button>
				</div>
			</form>
		</div>
	</div>
	<?php endif; ?>

	<div class="card shadow-sm">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table align-middle">
					<thead><tr><th>Date</th><th>Title</th><th>Description</th><th></th></tr></thead>
					<tbody>
						<?php foreach ($holidays as $h): ?>
							<tr>
								<td><?php echo h($h['date']); ?></td>
								<td><?php echo h($h['title']); ?></td>
								<td><?php echo h($h['description'] ?? ''); ?></td>
								<td class="text-end">
									<?php if (is_admin()): ?>
										<form method="post" class="d-inline">
											<input type="hidden" name="action" value="delete">
											<input type="hidden" name="id" value="<?php echo (int)$h['id']; ?>">
											<button class="btn btn-sm btn-outline-danger">Delete</button>
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
