<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_role('admin');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID','Employee','Date','Status','Latitude','Longitude']);
$stmt = $db->query('SELECT a.id, u.first_name, u.last_name, a.date, a.status, a.latitude, a.longitude FROM attendance a JOIN users u ON u.id=a.employee_id ORDER BY a.id DESC');
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
	fputcsv($out, $row);
}
fclose($out);
exit;
