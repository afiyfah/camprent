<?php
require_role(['staff', 'admin']);

$editId = max(0, (int) ($_GET['edit'] ?? 0));
$edit = null;
if ($editId > 0) {
    $editStmt = $pdo->prepare('SELECT * FROM articles WHERE id = ? LIMIT 1');
    $editStmt->execute([$editId]);
    $edit = $editStmt->fetch() ?: null;
}

$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? 'all');
$allowedStatuses = ['all', 'published', 'draft'];
if (!in_array($statusFilter, $allowedStatuses, true)) $statusFilter = 'all';

$conditions = [];
$params = [];
if ($search !== '') {
    $conditions[] = '(a.title LIKE ? OR a.excerpt LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term);
}
if ($statusFilter !== 'all') {
    $conditions[] = 'a.status = ?';
    $params[] = $statusFilter;
}
$listSql = "SELECT a.*, u.name AS author_name FROM articles a LEFT JOIN users u ON u.id = a.author_id";
if ($conditions) $listSql .= ' WHERE ' . implode(' AND ', $conditions);
$listSql .= ' ORDER BY a.updated_at DESC, a.created_at DESC, a.id DESC';
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($params);
$articles = $listStmt->fetchAll();

$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS drafts
     FROM articles"
)->fetch() ?: ['total' => 0, 'published' => 0, 'drafts' => 0];
?>
<section class="dashboard-shell">
    <div class="container">
        <div class="dashboard-heading cms-intro">
            <div><span class="eyebrow">Camp journal CMS</span><h1>Kelola artikel.</h1><p>Buat, perbarui, simpan sebagai draft, atau publikasikan konten CampRent.</p></div>
            <a class="btn btn-outline btn-small" href="<?= e(url('articles')) ?>" target="_blank" rel="noopener">Lihat jurnal publik ↗</a>
        </div>
        <div class="dashboard-main">
                <div class="cms-stat-grid">
                    <div class="cms-stat"><span>Total artikel</span><strong><?= (int) $stats['total'] ?></strong></div>
                    <div class="cms-stat"><span>Dipublikasikan</span><strong><?= (int) $stats['published'] ?></strong></div>
                    <div class="cms-stat"><span>Draft</span><strong><?= (int) $stats['drafts'] ?></strong></div>
                </div>

                <div class="management-grid">
                    <section class="form-card management-form cms-form">
                        <h2 style="font-size:19px"><?= $edit ? 'Edit artikel' : 'Tulis artikel baru' ?></h2>
                        <p class="small muted">Isi konten dengan bahasa yang jelas dan mudah dipahami pembaca.</p>
                        <form method="post" action="index.php?page=manage-articles">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="save_article">
                            <input type="hidden" name="article_id" value="<?= (int) ($edit['id'] ?? 0) ?>">
                            <div class="form-group">
                                <label for="article-title">Judul artikel</label>
                                <input class="form-control" id="article-title" name="title" maxlength="180" value="<?= e($edit['title'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="article-excerpt">Ringkasan</label>
                                <textarea class="form-control" id="article-excerpt" name="excerpt" rows="3" maxlength="350" data-count-target="excerpt-count" required><?= e($edit['excerpt'] ?? '') ?></textarea>
                                <span class="field-help"><span id="excerpt-count">0/350</span> karakter. Ringkasan tampil pada kartu artikel.</span>
                            </div>
                            <div class="form-group">
                                <label for="article-content">Isi artikel</label>
                                <textarea class="form-control" id="article-content" name="content" rows="10" maxlength="20000" required><?= e($edit['content'] ?? '') ?></textarea>
                                <span class="field-help">Pisahkan paragraf dengan satu baris kosong. HTML tidak akan dieksekusi.</span>
                            </div>
                            <div class="form-group">
                                <label for="article-cover">URL gambar sampul (opsional)</label>
                                <input class="form-control" id="article-cover" type="url" name="cover_image" maxlength="500" value="<?= e($edit['cover_image'] ?? '') ?>" placeholder="https://contoh.com/gambar.jpg">
                                <span class="field-help">Gunakan URL gambar yang dapat diakses publik dengan protokol HTTP atau HTTPS.</span>
                            </div>
                            <div class="form-group">
                                <label for="article-status">Status artikel</label>
                                <select class="form-control" id="article-status" name="status">
                                    <option value="draft" <?= ($edit['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft — belum tampil ke publik</option>
                                    <option value="published" <?= ($edit['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published — tampil ke publik</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-primary" type="submit"><?= $edit ? 'Simpan perubahan ↗' : 'Simpan artikel ↗' ?></button>
                                <?php if ($edit): ?><a class="btn btn-outline" href="<?= e(url('manage-articles')) ?>">Batal edit</a><?php endif; ?>
                            </div>
                        </form>
                    </section>

                    <section class="panel-card">
                        <div class="panel-head"><h2>Daftar artikel</h2><span class="role-pill"><?= count($articles) ?> konten</span></div>
                        <form class="cms-filter" method="get" action="index.php">
                            <input type="hidden" name="page" value="manage-articles">
                            <label class="sr-only" for="cms-search">Cari artikel</label>
                            <input class="form-control" id="cms-search" type="search" name="q" value="<?= e($search) ?>" placeholder="Cari judul atau ringkasan">
                            <label class="sr-only" for="cms-status">Filter status</label>
                            <select class="form-control" id="cms-status" name="status">
                                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua status</option>
                                <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                            </select>
                            <button class="btn btn-outline btn-small" type="submit">Filter</button>
                        </form>
                        <?php if ($articles): ?>
                            <div class="data-table-wrap">
                                <table class="data-table">
                                    <thead><tr><th>Artikel</th><th>Penulis</th><th>Status</th><th>Diperbarui</th><th>Tindakan</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($articles as $article): ?>
                                        <tr>
                                            <td class="cms-table-title"><strong><?= e($article['title']) ?></strong><span><?= e($article['excerpt']) ?></span></td>
                                            <td><?= e($article['author_name'] ?: 'Tim CampRent') ?></td>
                                            <td><span class="status status-<?= $article['status'] === 'published' ? 'success' : 'neutral' ?>"><?= e($article['status'] === 'published' ? 'Published' : 'Draft') ?></span></td>
                                            <td><?= e(date_id($article['updated_at'] ?: $article['created_at'])) ?></td>
                                            <td>
                                                <div class="cms-table-actions">
                                                    <a class="text-link" href="<?= e(url('manage-articles', ['edit' => $article['id']])) ?>">Edit</a>
                                                    <?php if ($article['status'] === 'published'): ?><a class="text-link" href="<?= e(url('article', ['slug' => $article['slug']])) ?>" target="_blank" rel="noopener">Lihat</a><?php endif; ?>
                                                    <form method="post" action="index.php?page=manage-articles" data-confirm="Hapus artikel ini secara permanen?">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="delete_article">
                                                        <input type="hidden" name="article_id" value="<?= (int) $article['id'] ?>">
                                                        <button class="btn btn-ghost danger-text" type="submit">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="cms-empty"><strong>Belum ada artikel yang cocok.</strong><p class="small">Buat artikel baru atau ubah filter pencarian.</p></div>
                        <?php endif; ?>
                    </section>
                </div>
        </div>
    </div>
</section>
