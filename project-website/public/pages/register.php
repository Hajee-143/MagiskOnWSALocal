<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$error = '';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$first = trim($_POST['first_name'] ?? '');
	$last = trim($_POST['last_name'] ?? '');
	$code = strtoupper(substr($first,0,1) . substr($last,0,1)) . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
	if ($username === '' || $password === '') {
		$error = 'Username and password are required';
	} else {
		try {
			$stmt = $db->prepare('INSERT INTO users (username, password_hash, role, email, first_name, last_name, code) VALUES (?,?,?,?,?,?,?)');
			$stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), 'employee', $email, $first, $last, $code]);
			$notice = 'Registered successfully. Your code: ' . $code;
		} catch (Throwable $e) {
			$error = 'Registration failed (username may exist)';
		}
	}
}
?>

<main class="container py-5" style="max-width: 560px;">
	<h1 class="h4 mb-4">Employee Registration</h1>
	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>
	<form method="post" class="card p-4 shadow-sm">
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">First name</label>
				<input type="text" class="form-control" name="first_name">
			</div>
			<div class="col-md-6">
				<label class="form-label">Last name</label>
				<input type="text" class="form-control" name="last_name">
			</div>
			<div class="col-md-12">
				<label class="form-label">Email</label>
				<input type="email" class="form-control" name="email">
			</div>
			<div class="col-md-6">
				<label class="form-label">Username</label>
				<input type="text" class="form-control" name="username" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Password</label>
				<input type="password" class="form-control" name="password" required>
			</div>
		</div>
		<button type="submit" class="btn btn-primary mt-3">Register</button>
		<p class="text-muted small mt-2 mb-0">Professional fields only. Complete personal/bank details in Profile after login.</p>
	</form>
</main>
