<?php
// Jalankan SEKALI dari terminal:  php seed_demo_accounts.php
// Membuat 3 akun demo dengan password yang di-hash (sesuai ketentuan dokumen).
// Sesuaikan kredensial database dengan config/database.php milik tim.

$pdo = new PDO(
    'mysql:host=localhost;dbname=camprent;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$accounts = [
    ['Admin Demo', 'admin@demo.test', 'Admin#2026', 'admin'],
    ['Staff Demo', 'staff@demo.test', 'Staff#2026', 'staff'],
    ['User Demo',  'user@demo.test',  'User#2026',  'user'],
];

$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role)
     VALUES (:name, :email, :hash, :role)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)'
);

foreach ($accounts as [$name, $email, $plain, $role]) {
    $stmt->execute([
        ':name'  => $name,
        ':email' => $email,
        ':hash'  => password_hash($plain, PASSWORD_DEFAULT),
        ':role'  => $role,
    ]);
    echo "OK: $email ($role)\n";
}
