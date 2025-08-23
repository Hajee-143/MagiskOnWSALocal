<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

$allowedPages = ['home', 'about', 'contact'];
$rawPage = $_GET['page'] ?? 'home';
$page = strtolower(preg_replace('/[^a-z]/', '', (string)$rawPage));
if ($page === '') {
	$page = 'home';
}
if (!in_array($page, $allowedPages, true)) {
	$page = '404';
}

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';

$pagePath = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pagePath)) {
	$pagePath = __DIR__ . '/pages/404.php';
}
require $pagePath;

require __DIR__ . '/partials/footer.php';

