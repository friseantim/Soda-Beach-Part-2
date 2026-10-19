<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return '₱' . number_format((float)$n, 2); }

function setting($key, $default = null) {
    global $pdo;
    $s = $pdo->prepare('SELECT v FROM settings WHERE k = ?');
    $s->execute([$key]);
    $v = $s->fetchColumn();
    return $v === false ? $default : $v;
}

// A booking holds its slot if paid, or pending for less than 15 minutes.
const ACTIVE_SQL = "(status='paid' OR (status='pending' AND created_at > (NOW() - INTERVAL 15 MINUTE)))";

function visitors_booked($date, $excludeId = 0) {
    global $pdo;
    $s = $pdo->prepare('SELECT COALESCE(SUM(visitors),0) FROM bookings WHERE visit_date = ? AND id <> ? AND ' . ACTIVE_SQL);
    $s->execute([$date, $excludeId]);
    return (int)$s->fetchColumn();
}

function slots_left($date, $excludeId = 0) {
    return max(0, (int)setting('daily_capacity', 0) - visitors_booked($date, $excludeId));
}

function cottage_left($cottageId, $date, $excludeId = 0) {
    global $pdo;
    $q = $pdo->prepare('SELECT quantity FROM cottages WHERE id = ?');
    $q->execute([$cottageId]);
    $total = (int)$q->fetchColumn();
    $s = $pdo->prepare('SELECT COALESCE(SUM(cottage_qty),0) FROM bookings WHERE cottage_id = ? AND visit_date = ? AND id <> ? AND ' . ACTIVE_SQL);
    $s->execute([$cottageId, $date, $excludeId]);
    return max(0, $total - (int)$s->fetchColumn());
}

function status_badge($status) {
    return '<span class="badge ' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}
