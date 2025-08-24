<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$error = '';
$notice = '';
$departments = $db->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$first = trim($_POST['first_name'] ?? '');
	$last = trim($_POST['last_name'] ?? '');
	$code = trim($_POST['code'] ?? '');
	$dob = trim($_POST['dob'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$address = trim($_POST['address'] ?? '');
	$departmentId = (int)($_POST['department_id'] ?? 0);
	$photoPath = null;

	if ($code === '') {
		$code = strtoupper(substr($first,0,1) . substr($last,0,1)) . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
	}

	if (!empty($_FILES['photo']['name'] ?? '')) {
		$uploadDir = DATA_PATH . '/photos';
		if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
		$ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
		$fname = 'emp_' . time() . '_' . preg_replace('/[^a-zA-Z0-9]/','', $code) . '.' . ($ext ?: 'jpg');
		$target = $uploadDir . '/' . $fname;
		if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
			$photoPath = 'photos/' . $fname;
		}
	}

	if ($username === '' || $password === '' || $code === '' || $first === '' || $last === '') {
		$error = 'Code, name, username and password are required';
	} else {
		try {
			$stmt = $db->prepare('INSERT INTO users (username, password_hash, role, email, first_name, last_name, code, dob, phone, address, department_id, photo_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
			$stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), 'employee', $email, $first, $last, $code, $dob, $phone, $address, $departmentId ?: null, $photoPath]);
			$notice = 'Registered successfully. Your code: ' . $code;
		} catch (Throwable $e) {
			$error = 'Registration failed (username or code may exist)';
		}
	}
}
?>

<main class="container py-5" style="max-width: 760px;">
	<h1 class="h4 mb-4">Employee Registration</h1>
	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>
	<form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Employee Code</label>
				<input type="text" class="form-control" name="code" placeholder="e.g., IT1234">
			</div>
			<div class="col-md-4">
				<label class="form-label">First name</label>
				<input type="text" class="form-control" name="first_name" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">Last name</label>
				<input type="text" class="form-control" name="last_name" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">DOB</label>
				<input type="date" class="form-control" name="dob">
			</div>
			<div class="col-md-4">
				<label class="form-label">Email</label>
				<input type="email" class="form-control" name="email">
			</div>
			<div class="col-md-4">
				<label class="form-label">Phone</label>
				<input type="tel" class="form-control" name="phone" placeholder="10-digit">
			</div>
			<div class="col-md-8">
				<label class="form-label">Address</label>
				<input type="text" class="form-control" name="address">
			</div>
			<div class="col-md-4">
				<label class="form-label">Department</label>
				<select name="department_id" class="form-select">
					<option value="">-- Select --</option>
					<?php foreach ($departments as $d): ?>
						<option value="<?php echo (int)$d['id']; ?>"><?php echo h($d['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-6">
				<label class="form-label">Username</label>
				<input type="text" class="form-control" name="username" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Password</label>
				<input type="password" class="form-control" name="password" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Photo</label>
				<input type="file" class="form-control" name="photo" accept="image/*">
			</div>
		</div>
		<button type="submit" class="btn btn-primary mt-3">Register</button>
	</form>
</main>
