<?php
declare(strict_types=1);

function current_user(): ?array {
	return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
	return current_user() !== null;
}

function user_role(): ?string {
	$user = current_user();
	return $user['role'] ?? null;
}

function is_admin(): bool {
	return user_role() === 'admin';
}

function is_employee(): bool {
	return user_role() === 'employee';
}

function require_role(string $role): void {
	if (!is_logged_in() || user_role() !== $role) {
		header('Location: /index.php?page=login');
		exit;
	}
}

function require_login(): void {
	if (!is_logged_in()) {
		header('Location: /index.php?page=login');
		exit;
	}
}

function attempt_login(PDO $db, string $username, string $password): bool {
	// Allow login by username or employee code
	$stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR code = ?');
	$stmt->execute([$username, $username]);
	$user = $stmt->fetch();
	if (!$user) {
		return false;
	}
	if (!password_verify($password, $user['password_hash'])) {
		return false;
	}
	$_SESSION['user'] = [
		'id' => $user['id'],
		'username' => $user['username'],
		'role' => $user['role'],
	];
	return true;
}

function do_logout(): void {
	unset($_SESSION['user']);
	session_regenerate_id(true);
}

