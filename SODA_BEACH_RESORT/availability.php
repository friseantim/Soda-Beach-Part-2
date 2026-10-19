<?php
require_once 'includes/auth.php';
header('Content-Type: application/json');
$date = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { echo '{}'; exit; }
$out = ['slots_left' => slots_left($date), 'cottages' => []];
foreach ($pdo->query('SELECT id FROM cottages') as $c) $out['cottages'][$c['id']] = cottage_left($c['id'], $date);
echo json_encode($out);
