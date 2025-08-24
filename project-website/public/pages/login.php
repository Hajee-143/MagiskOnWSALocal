<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');
	if ($username === '' || $password === '') {
		$error = 'Please enter username and password';
	} else if (attempt_login($db, $username, $password)) {
		redirect('/index.php?page=dashboard');
	} else {
		$error = 'Invalid credentials';
	}
}
?>

<main class="container py-5" style="max-width: 420px;">
	<h1 class="h3 mb-4 text-center">Sign in</h1>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?php echo h($error); ?></div>
	<?php endif; ?>
	<form method="post" class="card p-4 shadow-sm">
		<div class="mb-3">
			<label class="form-label">Username</label>
			<input type="text" name="username" class="form-control" required>
		</div>
		<div class="mb-3">
			<label class="form-label">Password</label>
			<input type="password" name="password" class="form-control" required>
		</div>
		<button type="submit" class="btn btn-primary w-100">Login</button>
		<p class="text-muted small mt-3">Default: admin / admin</p>
	</form>
</main>
