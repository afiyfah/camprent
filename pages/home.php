<?php

$equipment = $pdo->query(
    "SELECT e.id, e.name, e.description, e.specifications, e.image_url, e.daily_rate, e.total_stock,
            c.name AS category_name
     FROM equipment e
     INNER JOIN categories c ON c.id = e.category_id
     WHERE e.is_active = 1
     ORDER BY e.id DESC
     LIMIT 6"
)->fetchAll();

$articles = $pdo->query(
    "SELECT id, title, slug, excerpt, cover_image, published_at
     FROM articles
     WHERE status = 'published'
     ORDER BY published_at DESC, id DESC
     LIMIT 3"
)->fetchAll();

$equipmentCount = (int) $pdo->query('SELECT COUNT(*) FROM equipment WHERE is_active = 1')->fetchColumn();
$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$articleCount = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
?>
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-copy">
            <span class="eyebrow">Your next outdoor story starts here</span>
            <h1>Petualangan dimulai dari <em>persiapan yang tepat.</em></h1>
            <p>Kenali perlengkapan camping, bandingkan kebutuhan perjalanan, dan temukan tips sederhana sebelum berangkat bersama CampRent.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url('equipment')) ?>">Jelajahi katalog <span>↗</span></a>
                <a class="btn btn-outline" href="<?= e(url('articles')) ?>">Baca jurnal camping</a>
            </div>
            <div class="hero-meta">
                <div><strong><?= $equipmentCount ?></strong><span>Jenis perlengkapan</span></div>
                <div><strong><?= $categoryCount ?></strong><span>Kategori alat</span></div>
                <div><strong><?= $articleCount ?></strong><span>Artikel panduan</span></div>
            </div>
        </div>
        <div class="hero-stamp"><div><b>GO</b>FIND YOUR<br>WILD SIDE</div></div>
    </div>
</section>

<div class="container trust-row">
    <span><i></i> Katalog perlengkapan</span>
    <span><i></i> Informasi alat yang jelas</span>
    <span><i></i> Inspirasi kegiatan outdoor</span>
    <span><i></i> Panduan untuk pemula</span>
</div>

<section class="section" id="featured-equipment">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Gear up</span><h2>Perlengkapan untuk cerita berikutnya.</h2></div>
            <div><p>Dari tenda sampai lampu malam, kenali perlengkapan yang bisa mendukung perjalananmu.</p><a class="text-link" href="<?= e(url('equipment')) ?>">Lihat semua perlengkapan ↗</a></div>
        </div>
        <?php if ($equipment): ?>
            <div class="equipment-grid">
                <?php foreach ($equipment as $item): ?>
                    <article class="equipment-card">
                        <div class="equipment-image catalog-card-image">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="image-fallback" aria-hidden="true">⌁</div>
                            <?php endif; ?>
                            <span class="card-tag"><?= e($item['category_name']) ?></span>
                        </div>
                        <div class="equipment-body catalog-card-copy">
                            <span class="category-label">Camp essentials</span>
                            <h3><?= e($item['name']) ?></h3>
                            <p><?= e($item['description']) ?></p>
                            <div class="equipment-foot">
                                <div class="price"><?= money($item['daily_rate']) ?> <small>/ hari</small></div>
                                <a class="arrow-link" href="<?= e(url('equipment', ['q' => $item['name']])) ?>" aria-label="Temukan <?= e($item['name']) ?> di katalog">↗</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><strong>Katalog sedang disiapkan.</strong><p>Perlengkapan akan ditampilkan setelah data katalog tersedia.</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Explore CampRent</span><h2>Semua informasi penting, lebih mudah ditemukan.</h2></div>
            <p>Mulai dari mengenal alat sampai membaca panduan singkat untuk kegiatan outdoor.</p>
        </div>
        <div class="feature-list how-steps">
            <article class="panel-card"><div class="feature-icon" style="background:var(--sage);color:var(--forest)">01</div><h3 style="font-size:18px">Jelajahi katalog</h3><p class="small">Telusuri kategori, spesifikasi, dan informasi perlengkapan yang tersedia.</p></article>
            <article class="panel-card"><div class="feature-icon" style="background:var(--sage);color:var(--forest)">02</div><h3 style="font-size:18px">Kenali kebutuhanmu</h3><p class="small">Bandingkan perlengkapan berdasarkan jenis kegiatan dan jumlah peserta.</p></article>
            <article class="panel-card"><div class="feature-icon" style="background:var(--sage);color:var(--forest)">03</div><h3 style="font-size:18px">Baca panduan</h3><p class="small">Gunakan artikel CampRent sebagai bekal agar persiapan camping lebih matang.</p></article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="feature-band">
            <div><span class="eyebrow" style="color:#d7dfd2">Why CampRent</span><h2>Persiapan rapi.<br>Petualangan berarti.</h2><p>Temukan informasi perlengkapan dan wawasan praktis dalam satu tempat.</p><a class="btn btn-primary btn-small" href="<?= e(url('equipment')) ?>">Lihat katalog ↗</a></div>
            <div class="feature-list">
                <div class="feature-item"><div class="feature-icon">▦</div><strong>Katalog terstruktur</strong><span>Perlengkapan dikelompokkan berdasarkan kategori agar mudah dijelajahi.</span></div>
                <div class="feature-item"><div class="feature-icon">◎</div><strong>Informasi jelas</strong><span>Kenali deskripsi, spesifikasi, dan tarif harian setiap perlengkapan.</span></div>
                <div class="feature-item"><div class="feature-icon">♧</div><strong>Konten bermanfaat</strong><span>Baca tips camping, checklist, dan etika menjaga alam.</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">Field notes</span><h2>Bekal kecil sebelum berangkat.</h2></div><a class="text-link" href="<?= e(url('articles')) ?>">Buka semua jurnal ↗</a></div>
        <?php if ($articles): ?>
            <div class="article-grid">
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <a class="article-image" href="<?= e(url('article', ['slug' => $article['slug']])) ?>">
                            <?php if (!empty($article['cover_image'])): ?><img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy"><?php else: ?><div class="image-fallback" aria-hidden="true">✦</div><?php endif; ?>
                        </a>
                        <div class="article-content"><span class="article-meta">Camp journal · <?= e(date_id($article['published_at'], false)) ?></span><h3><a href="<?= e(url('article', ['slug' => $article['slug']])) ?>"><?= e($article['title']) ?></a></h3><p><?= e($article['excerpt']) ?></p><a class="text-link" href="<?= e(url('article', ['slug' => $article['slug']])) ?>">Baca artikel ↗</a></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><strong>Artikel segera hadir.</strong><p>Tim CampRent sedang menyiapkan panduan camping untukmu.</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container"><div class="cta-panel"><div><span class="eyebrow">Make room for outside</span><h2>Akhir pekanmu punya banyak kemungkinan.</h2><p>Mulai dari mengenal perlengkapan yang tepat untuk rencana perjalananmu.</p></div><a class="btn btn-dark" href="<?= e(url('equipment')) ?>">Jelajahi perlengkapan ↗</a></div></div>
</section>
