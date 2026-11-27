<?php
declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: 'camprent';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';
$dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    $debug = getenv('APP_DEBUG') === 'true';
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>CampRent · Koneksi database</title><body style="font-family:Arial,sans-serif;padding:40px;background:#f4f1e9;color:#23352b"><h1>CampRent belum terhubung ke database</h1><p>Pastikan MySQL aktif dan database <b>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</b> sudah di-import melalui phpMyAdmin.</p>';
    if ($debug) echo '<pre>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '</body></html>';
    exit;
}
