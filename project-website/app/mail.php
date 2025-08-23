<?php
declare(strict_types=1);

function send_email(string $to, string $subject, string $body): bool {
	// Simple logger-based email fallback. In production, replace with SMTP or mail().
	$logLine = sprintf("[%s] TO:%s | SUBJECT:%s | BODY:%s\n", date('c'), $to, $subject, str_replace(["\r","\n"], ' ', $body));
	file_put_contents(DATA_PATH . '/mail.log', $logLine, FILE_APPEND);
	return true;
}

function broadcast_email(array $recipients, string $subject, string $body): void {
	foreach ($recipients as $email) {
		if (is_string($email) && $email !== '') {
			send_email($email, $subject, $body);
		}
	}
}

