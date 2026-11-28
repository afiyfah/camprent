<?php

$categoryId = max(0, (int) ($_GET['category'] ?? 0));
$search = trim((string) ($_GET['q'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'newest');
$sortOptions = [
    'newest' => 'e.id DESC',
    'name' => 'e.name ASC',
    'price_low' => 'e.daily_rate ASC, e.name ASC',
    'price_high' => 'e.daily_rate DESC, e.name ASC',
];
$orderBy = $sortOptions[$sort] ?? $sortOptions['newest'];

$categories = $pdo->query('SELECT id, name, description FROM categories ORDER BY name ASC')->fetchAll();
$sql = "SELECT e.id, e.name, e.description, e.specifications, e.image_url, e.daily_rate, e.total_stock,
               c.id AS category_id, c.name AS category_name
        FROM equipment e
        INNER JOIN categories c ON c.id = e.category_id
        WHERE e.is_active = 1";
$params = [];
if ($categoryId > 0) {
    $sql .= ' AND e.category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $sql .= ' AND (e.name LIKE ? OR e.description LIKE ? OR e.specifications LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}
$sql .= ' ORDER BY ' . $orderBy;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= e(url('home')) ?>">Beranda</a> / Perlengkapan</div>
        <span class="eyebrow">The gear library</span>
        <h1>Temukan gear yang pas.</h1>
        <p>Kenali perlengkapan untuk tidur nyaman, memasak hangat, dan menikmati waktu di alam terbuka.</p>
    </div>
</section>
<section class="content-section">
    <div class="container">
        <form class="catalog-toolbar" method="get" action="index.php">
            <input type="hidden" name="page" value="equipment">
            <div class="catalog-toolbar-actions">
                <div class="search-input">
                    <label class="sr-only" for="equipment-search">Cari perlengkapan</label>
                    <input class="form-control" id="equipment-search" type="search" name="q" value="<?= e($search) ?>" placeholder="Cari tenda, matras, kompor...">
                </div>
                <div class="form-group">
                    <label for="equipment-sort">Urutkan</label>
                    <select class="form-control" id="equipment-sort" name="sort">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nama A–Z</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga terendah</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga tertinggi</option>
                    </select>
                </div>
                <?php if ($categoryId > 0): ?><input type="hidden" name="category" value="<?= $categoryId ?>"><?php endif; ?>
                <button class="btn btn-primary" type="submit">Terapkan</button>
            </div>
        </form>

        <div class="filter-row">
            <a class="filter-pill <?= $categoryId === 0 ? 'active' : '' ?>" href="<?= e(url('equipment', array_filter(['q' => $search, 'sort' => $sort !== 'newest' ? $sort : null]))) ?>">Semua kategori</a>
            <?php foreach ($categories as $category): ?>
                <a class="filter-pill <?= $categoryId === (int) $category['id'] ? 'active' : '' ?>" href="<?= e(url('equipment', array_filter(['category' => $category['id'], 'q' => $search, 'sort' => $sort !== 'newest' ? $sort : null]))) ?>"><?= e($category['name']) ?></a>
            <?php endforeach; ?>
            <?php if ($categoryId || $search || $sort !== 'newest'): ?><a class="filter-pill" href="<?= e(url('equipment')) ?>">Reset filter ×</a><?php endif; ?>
        </div>

        <div class="catalog-results"><span><strong><?= count($items) ?></strong> perlengkapan ditemukan</span><?php if ($search !== ''): ?><span>Hasil pencarian untuk “<?= e($search) ?>”</span><?php endif; ?></div>

        <?php if (!$items): ?>
            <div class="empty-state"><div class="empty-icon">⌕</div><strong>Belum ada perlengkapan yang cocok.</strong><p>Coba kata kunci atau kategori lain.</p><a class="btn btn-outline btn-small" href="<?= e(url('equipment')) ?>">Lihat semua alat</a></div>
        <?php else: ?>
            <div class="equipment-grid">
                <?php foreach ($items as $item): ?>
                    <article class="equipment-card">
                        <div class="equipment-image catalog-card-image">
                            <?php if (!empty($item['image_url'])): ?><img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" loading="lazy"><?php else: ?><div class="image-fallback" aria-hidden="true">⌁</div><?php endif; ?>
                            <span class="card-tag"><?= e($item['category_name']) ?></span>
                        </div>
                        <div class="equipment-body catalog-card-copy">
                            <span class="category-label">Camp essentials</span>
                            <h3><?= e($item['name']) ?></h3>
                            <p><?= e($item['description']) ?></p>
                            <?php if (!empty($item['specifications'])): ?><div class="catalog-specs"><span><?= e($item['specifications']) ?></span></div><?php endif; ?>
                            <div class="equipment-foot"><div class="price"><?= money($item['daily_rate']) ?> <small>/ hari</small></div><span class="tiny muted">Stok tercatat: <?= (int) $item['total_stock'] ?></span></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
