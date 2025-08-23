<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_login();

$user = current_user();

$error = '';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'update_profile') {
		$email = trim($_POST['email'] ?? '');
		$first = trim($_POST['first_name'] ?? '');
		$last = trim($_POST['last_name'] ?? '');
		$bank = trim($_POST['bank_account'] ?? '');
		$ifsc = trim($_POST['ifsc'] ?? '');
		$stmt = $db->prepare('UPDATE users SET email=?, first_name=?, last_name=?, bank_account=?, ifsc=? WHERE id=?');
		$stmt->execute([$email, $first, $last, $bank, $ifsc, (int)$user['id']]);
		$_SESSION['user'] = array_merge($user, ['username'=>$user['username']]);
		$notice = 'Profile updated';
	} else if ($action === 'change_password') {
		$old = $_POST['old_password'] ?? '';
		$new = $_POST['new_password'] ?? '';
		if ($old === '' || $new === '') {
			$error = 'Both old and new password are required';
		} else {
			$stmt = $db->prepare('SELECT password_hash FROM users WHERE id=?');
			$stmt->execute([(int)$user['id']]);
			$row = $stmt->fetch();
			if ($row && password_verify($old, $row['password_hash'])) {
				$upd = $db->prepare('UPDATE users SET password_hash=? WHERE id=?');
				$upd->execute([password_hash($new, PASSWORD_DEFAULT), (int)$user['id']]);
				$notice = 'Password changed';
			} else {
				$error = 'Old password incorrect';
			}
		}
	}
}

$stmt = $db->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([(int)$user['id']]);
$me = $stmt->fetch();
?>

<main class="container py-4" style="max-width: 760px;">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Profile</h1>
		<a href="/index.php?page=dashboard" class="btn btn-light btn-sm">Back</a>
	</div>

	<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo h($notice); ?></div><?php endif; ?>

	<div class="card shadow-sm mb-3">
		<div class="card-body">
			<h2 class="h6">Personal & Bank Details</h2>
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="update_profile">
				<div class="col-md-6">
					<label class="form-label">First name</label>
					<input class="form-control" name="first_name" value="<?php echo h($me['first_name'] ?? ''); ?>">
				</div>
				<div class="col-md-6">
					<label class="form-label">Last name</label>
					<input class="form-control" name="last_name" value="<?php echo h($me['last_name'] ?? ''); ?>">
				</div>
				<div class="col-md-6">
					<label class="form-label">Email</label>
					<input class="form-control" name="email" value="<?php echo h($me['email'] ?? ''); ?>">
				</div>
				<div class="col-md-6">
					<label class="form-label">Bank account</label>
					<input class="form-control" name="bank_account" value="<?php echo h($me['bank_account'] ?? ''); ?>">
				</div>
				<div class="col-md-6">
					<label class="form-label">IFSC</label>
					<input class="form-control" name="ifsc" value="<?php echo h($me['ifsc'] ?? ''); ?>">
				</div>
				<div class="col-12">
					<button class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card shadow-sm">
		<div class="card-body">
			<h2 class="h6">Change password</h2>
			<form method="post" class="row g-3">
				<input type="hidden" name="action" value="change_password">
				<div class="col-md-6">
					<label class="form-label">Old password</label>
					<input type="password" class="form-control" name="old_password" required>
				</div>
				<div class="col-md-6">
					<label class="form-label">New password</label>
					<input type="password" class="form-control" name="new_password" required>
				</div>
				<div class="col-12">
					<button class="btn btn-outline-primary">Change Password</button>
				</div>
			</form>
		</div>
	</div>
</main>
