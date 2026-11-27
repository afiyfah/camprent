<?php
$user = require_login();
?>

<section class="dashboard-shell">
    <div class="container">

        <div class="dashboard-heading">
            <span class="eyebrow">Member Area</span>
            <h1>Dashboard saya.</h1>
            <p>Selamat datang di CampRent. Kelola akun dan persiapan petualanganmu di sini.</p>
        </div>

        <div class="dashboard-layout">

            <?php require __DIR__ . '/../layout/dashboard_sidebar.php'; ?>

            <div class="dashboard-main">

                <div class="panel-card">
                    <span class="eyebrow">Welcome to CampRent</span>

                    <h2>Halo, <?= e($user['name']) ?>!</h2>

                    <p>
                        Akun kamu berhasil masuk ke sistem CampRent.
                        Sekarang kamu dapat mengelola informasi profil
                        dan melanjutkan proses reservasi perlengkapan camping.
                    </p>

                    <div class="divider"></div>

                    <h3>Informasi Akun</h3>

                    <p><strong>Nama:</strong> <?= e($user['name']) ?></p>
                    <p><strong>Email:</strong> <?= e($user['email']) ?></p>
                    <p><strong>Nomor Telepon:</strong> <?= e($user['phone'] ?? '-') ?></p>
                    <p><strong>Status:</strong> Aktif</p>

                    <a class="btn btn-primary"
                       href="<?= e(url('profile')) ?>">
                        Kelola Profil ↗
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>
