<?php
declare(strict_types=1);

$user = require_login();
$currentPage = (string) ($_GET['page'] ?? 'dashboard');
$role = $user['role'] ?? 'user';

if ($role === 'user') {
    $menu = [
        ['Dashboard', 'dashboard'],
        ['Reservasi Saya', 'my-bookings'],
        ['Profil Saya', 'profile'],
    ];
} else {
    $menu = [
        ['Dashboard Staff', 'staff-dashboard'],
        ['Kelola Perlengkapan', 'manage-equipment'],
        ['Kelola Kategori', 'manage-categories'],
        ['Kelola Artikel', 'manage-articles'],
        ['Laporan', 'reports'],
    ];

    if ($role === 'admin') {
        $menu[] = ['Kelola Pengguna', 'manage-users'];
        $menu[] = ['Audit Log', 'audit-logs'];
    }
}
?>

<aside class="dashboard-sidebar">

    <div class="sidebar-user">
        <strong><?= e($user['name']) ?></strong>
        <small><?= e($user['email']) ?></small>
        <small>Role: <?= e(ucfirst($role)) ?></small>
    </div>

    <nav class="sidebar-menu">
        <?php foreach ($menu as $item): ?>
            <a
                class="<?= e(active_page($currentPage, [$item[1]])) ?>"
                href="<?= e(url($item[1])) ?>"
            >
                <?= e($item[0]) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-logout">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="logout">
            <button type="submit">Keluar dari akun ↗</button>
        </form>
    </div>

</aside>
