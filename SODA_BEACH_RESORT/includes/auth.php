<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/layout.php';

function current_user() { return $_SESSION['user'] ?? null; }

function require_login() {
    if (!current_user()) { header('Location: ' . BASE_URL . '/login.php'); exit; }
}

function require_staff() {
    require_login();
    if (current_user()['role'] !== 'staff') { http_response_code(403); exit('Staff only.'); }
}

function login_user(array $row) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int)$row['id'], 'name' => $row['name'], 'role' => $row['role']];
}
