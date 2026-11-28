<?php
$slug = trim((string) ($_GET['slug'] ?? ''));
$stmt = $pdo->prepare(
    "SELECT a.*, u.name AS author_name
     FROM articles a
     LEFT JOIN users u ON u.id = a.author_id
     WHERE a.slug = ? AND a.status = 'published'
     LIMIT 1"
);
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Artikel tidak ditemukan';
    ?>
    <section class="not-found container">
        <span class="eyebrow">Camp journal</span>
        <h1>404</h1>
        <p>Maaf, artikel yang kamu cari tidak tersedia atau sudah tidak dipublikasikan.</p>
        <a class="btn btn-dark" href="<?= e(url('articles')) ?>">Kembali ke jurnal</a>
    </section>
    <?php
    return;
}

$paragraphs = preg_split('/\R\s*\R/', trim((string) $article['content'])) ?: [];
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= e(url('home')) ?>">Beranda</a> / <a href="<?= e(url('articles')) ?>">Jurnal</a> / Artikel</div>
        <span class="eyebrow">Camp journal</span>
    </div>
</section>
<section class="content-section">
    <article class="container article-detail">
        <h1><?= e($article['title']) ?></h1>
        <div class="article-detail-meta">
            <span>Dipublikasikan <?= e(date_id($article['published_at'])) ?></span>
            <?php if (!empty($article['author_name'])): ?><span>Ditulis oleh <?= e($article['author_name']) ?></span><?php endif; ?>
        </div>
        <p class="article-lead"><?= e($article['excerpt']) ?></p>
        <?php if (!empty($article['cover_image'])): ?>
            <div class="article-detail-image"><img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" fetchpriority="high"></div>
        <?php endif; ?>
        <div class="article-body">
            <?php foreach ($paragraphs as $paragraph): ?>
                <?php if (trim($paragraph) !== ''): ?><p><?= nl2br(e(trim($paragraph))) ?></p><?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="divider"></div>
        <a class="text-link" href="<?= e(url('articles')) ?>">← Kembali ke semua artikel</a>
    </article>
</section>
