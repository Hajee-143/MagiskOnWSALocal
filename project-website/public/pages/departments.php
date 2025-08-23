<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$name = trim($_POST['name'] ?? '');
		if ($name === '') {
			$error = 'Department name is required';
		} else {
			try {
				$stmt = $db->prepare('INSERT INTO departments (name) VALUES (?)');
				$stmt->execute([$name]);
				$notice = 'Department created';
			} catch (Throwable $e) {
				$error = 'Could not create department';
			}
		}
	} else if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		if ($id > 0) {
			$stmt = $db->prepare('DELETE FROM departments WHERE id = ?');
			$stmt->execute([$id]);
			$notice = 'Department deleted';
		}
	}
}

$departments = $db->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
?>

<main class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Departments</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<div class="card shadow-sm mb-4">
		<div class="card-body">
			<form method="post" class="row g-2 align-items-end">
				<input type="hidden" name="action" value="create">
				<div class="col-md-6">
					<label class="form-label">Name</label>
					<input type="text" name="name" class="form-control" required>
				</div>
				<div class="col-md-3">
					<button type="submit" class="btn btn-primary">Add Department</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card shadow-sm">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table align-middle">
					<thead>
						<tr><th style="width:60px;">ID</th><th>Name</th><th style="width:120px;"></th></tr>
					</thead>
					<tbody>
						<?php foreach ($departments as $d): ?>
							<tr>
								<td><?php echo (int)$d['id']; ?></td>
								<td><?php echo h($d['name']); ?></td>
								<td>
									<form method="post" class="d-inline">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>">
										<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this department?')">Delete</button>
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
