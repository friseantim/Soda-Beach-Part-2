<?php
date_default_timezone_set('Asia/Manila');

define('DB_HOST', 'localhost');
define('DB_NAME', 'soda_beach_resort');
define('DB_USER', 'root');
define('DB_PASS', '');

// URL path of the project folder (works under XAMPP htdocs automatically)
$__root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$__doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
define('BASE_URL', rtrim(str_replace($__doc, '', $__root), '/'));

// PayPal Sandbox (developer.paypal.com > Apps & Credentials > Sandbox)
define('PAYPAL_CLIENT_ID', 'YOUR_SANDBOX_CLIENT_ID');
define('PAYPAL_SECRET',    'YOUR_SANDBOX_SECRET');
define('PAYPAL_API',       'https://api-m.sandbox.paypal.com');
define('PAYPAL_CURRENCY',  'PHP');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed. Check config/db.php');
}
