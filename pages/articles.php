<?php
$search = trim((string) ($_GET['q'] ?? ''));

$sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.cover_image, a.published_at,
               u.name AS author_name
        FROM articles a
        LEFT JOIN users u ON u.id = a.author_id
        WHERE a.status = 'published'";
$params = [];
if ($search !== '') {
    $sql .= ' AND (a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}
$sql .= ' ORDER BY a.published_at DESC, a.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= e(url('home')) ?>">Beranda</a> / Jurnal</div>
        <span class="eyebrow">Camp journal</span>
        <h1>Catatan dari luar ruang.</h1>
        <p>Tips, checklist, dan inspirasi kecil supaya perjalanan camping terasa lebih siap dan bertanggung jawab.</p>
    </div>
</section>
<section class="content-section">
    <div class="container">
        <form class="article-search" method="get" action="index.php">
            <input type="hidden" name="page" value="articles">
            <label class="sr-only" for="article-search">Cari artikel</label>
            <input class="form-control" id="article-search" type="search" name="q" value="<?= e($search) ?>" placeholder="Cari tips, checklist, atau topik camping...">
            <button class="btn btn-primary" type="submit">Cari artikel</button>
        </form>
        <div class="catalog-results"><span><strong><?= count($articles) ?></strong> artikel ditemukan</span><?php if ($search !== ''): ?><a class="text-link" href="<?= e(url('articles')) ?>">Hapus pencarian ×</a><?php endif; ?></div>
        <?php if (!$articles): ?>
            <div class="empty-state"><div class="empty-icon">✦</div><strong><?= $search === '' ? 'Belum ada artikel.' : 'Artikel tidak ditemukan.' ?></strong><p><?= $search === '' ? 'Artikel akan segera hadir.' : 'Coba kata kunci lain untuk menemukan artikel yang kamu cari.' ?></p></div>
        <?php else: ?>
            <div class="article-grid">
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <a class="article-image" href="<?= e(url('article', ['slug' => $article['slug']])) ?>" aria-label="Baca <?= e($article['title']) ?>">
                            <?php if (!empty($article['cover_image'])): ?><img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy"><?php else: ?><div class="image-fallback" aria-hidden="true">✦</div><?php endif; ?>
                        </a>
                        <div class="article-content">
                            <span class="article-meta"><?= e(date_id($article['published_at'], false)) ?><?php if (!empty($article['author_name'])): ?> · <?= e($article['author_name']) ?><?php endif; ?></span>
                            <h3 class="article-title"><a href="<?= e(url('article', ['slug' => $article['slug']])) ?>"><?= e($article['title']) ?></a></h3>
                            <p><?= e($article['excerpt']) ?></p>
                            <a class="text-link" href="<?= e(url('article', ['slug' => $article['slug']])) ?>">Baca selengkapnya ↗</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
