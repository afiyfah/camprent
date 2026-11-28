<?php
$currentPage = $page ?? 'home';
$viewer = current_user();
$pageMeta = [
    'home' => ['Beranda', 'Jelajahi katalog perlengkapan camping CampRent dan temukan panduan untuk mempersiapkan petualanganmu.'],
    'equipment' => ['Katalog Perlengkapan', 'Jelajahi katalog alat camping CampRent berdasarkan kategori, nama, spesifikasi, dan tarif harian.'],
    'articles' => ['Jurnal Camping', 'Baca tips, checklist, dan inspirasi kegiatan camping dari jurnal CampRent.'],
    'article' => ['Artikel Camping', 'Panduan dan inspirasi kegiatan camping dari jurnal CampRent.'],
    'manage-articles' => ['Kelola Artikel', 'Halaman pengelolaan artikel CampRent untuk Staff dan Admin.'],
];
[$pageTitle, $pageDescription] = $pageMeta[$currentPage] ?? [
    ucwords(str_replace('-', ' ', $currentPage)),
    'CampRent menyediakan katalog perlengkapan camping dan panduan untuk membantu persiapan petualanganmu.',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#172b23">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?> · CampRent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.0.0">
</head>
<body>
<a class="skip-link" href="#main-content">Lewati ke konten</a>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= e(url('home')) ?>" aria-label="CampRent beranda">
            <span class="brand-mark"><svg viewBox="0 0 40 40" aria-hidden="true"><path d="M4 31 18.5 8 36 31H4Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><path d="m13 31 7-11 7 11M18.5 8v7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><path d="M5 34h30" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></span>
            <span>camp<span>rent</span><small>GEAR UP. GET OUT.</small></span>
        </a>
        <button class="mobile-menu-toggle" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle><span></span><span></span></button>
        <nav class="main-nav" id="primary-navigation" data-menu aria-label="Navigasi utama">
            <a class="<?= e(trim(active_page($currentPage, ['home']))) ?>" href="<?= e(url('home')) ?>" <?= $currentPage === 'home' ? 'aria-current="page"' : '' ?>>Beranda</a>
            <a class="<?= e(trim(active_page($currentPage, ['equipment']))) ?>" href="<?= e(url('equipment')) ?>" <?= $currentPage === 'equipment' ? 'aria-current="page"' : '' ?>>Perlengkapan</a>
            <a class="<?= e(trim(active_page($currentPage, ['articles', 'article']))) ?>" href="<?= e(url('articles')) ?>" <?= in_array($currentPage, ['articles', 'article'], true) ? 'aria-current="page"' : '' ?>>Jurnal</a>
            <?php if ($viewer): ?>
                <a class="<?= e(trim(active_page($currentPage, ['profile']))) ?>" href="<?= e(url('profile')) ?>">Profil</a>
                <?php if (is_staff()): ?>
                    <a class="nav-dashboard <?= e(trim(active_page($currentPage, ['manage-articles']))) ?>" href="<?= e(url('manage-articles')) ?>">Kelola Artikel</a>
                <?php endif; ?>
                <form method="post" action="index.php" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="logout">
                    <button class="nav-login" type="submit">Keluar</button>
                </form>
            <?php else: ?>
                <a class="nav-login" href="<?= e(url('login')) ?>">Masuk</a>
                <a class="btn btn-small btn-primary" href="<?= e(url('register')) ?>">Daftar <span>↗</span></a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main id="main-content">
<?php if (!empty($flashMessage)): ?>
    <div class="container flash-wrap">
        <div class="alert alert-<?= e($flashMessage['type'] ?? 'info') ?>" role="status">
            <span><?= e($flashMessage['message'] ?? '') ?></span>
            <button type="button" aria-label="Tutup pesan" data-dismiss>×</button>
        </div>
    </div>
<?php endif; ?>
