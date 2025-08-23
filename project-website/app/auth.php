<?php
declare(strict_types=1);

function current_user(): ?array {
	return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
	return current_user() !== null;
}

function require_login(): void {
	if (!is_logged_in()) {
		header('Location: /index.php?page=login');
		exit;
	}
}

function attempt_login(PDO $db, string $username, string $password): bool {
	$stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
	$stmt->execute([$username]);
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

