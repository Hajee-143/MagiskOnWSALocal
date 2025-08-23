<?php
declare(strict_types=1);

function h(string $value): string {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $location): void {
	header('Location: ' . $location);
	exit;
}

