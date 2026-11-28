<?php
declare(strict_types=1);

/**
 * Convert an article title into a URL-safe slug.
 * Kept independent from the database so it can be covered by CLI tests.
 */
function camprent_content_slug(string $title): string
{
    $transliterated = function_exists('iconv')
        ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title)
        : $title;

    if ($transliterated === false || $transliterated === '') {
        $transliterated = $title;
    }

    $slug = preg_replace('/[^a-z0-9]+/i', '-', $transliterated);
    $slug = strtolower(trim((string) $slug, '-'));

    return $slug !== '' ? $slug : 'artikel';
}

/** Only public HTTP(S) URLs are accepted for article cover images. */
function camprent_content_is_http_url(string $url): bool
{
    if ($url === '') return true;
    if (filter_var($url, FILTER_VALIDATE_URL) === false) return false;

    $scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));
    return in_array($scheme, ['http', 'https'], true);
}

/**
 * Handle article CMS actions. The caller must already have validated the POST
 * request and CSRF token (the central actions dispatcher does this).
 */
function handle_content_action(PDO $pdo, string $action): never
{
    require_role(['staff', 'admin']);

    if ($action === 'save_article') {
        $id = max(0, (int) ($_POST['article_id'] ?? 0));
        $title = post_string('title', 180);
        $excerpt = post_string('excerpt', 350);
        $content = post_string('content', 20000);
        $coverImage = post_string('cover_image', 500);
        $status = post_string('status', 20);

        if ($title === '' || $excerpt === '' || $content === '') {
            throw new RuntimeException('Judul, ringkasan, dan isi artikel wajib diisi.');
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            throw new RuntimeException('Status artikel tidak valid.');
        }
        if (!camprent_content_is_http_url($coverImage)) {
            throw new RuntimeException('URL gambar harus menggunakan alamat HTTP atau HTTPS yang valid.');
        }

        $publishedAt = null;
        if ($id > 0) {
            $find = $pdo->prepare('SELECT id, published_at FROM articles WHERE id = ? LIMIT 1');
            $find->execute([$id]);
            $existing = $find->fetch();
            if (!$existing) {
                throw new RuntimeException('Artikel yang ingin diperbarui tidak ditemukan.');
            }

            if ($status === 'published') {
                $publishedAt = $existing['published_at'] ?: date('Y-m-d H:i:s');
            }

            $slug = camprent_content_slug($title) . '-' . $id;
            $update = $pdo->prepare(
                'UPDATE articles
                 SET title = ?, slug = ?, excerpt = ?, content = ?, cover_image = ?, status = ?, published_at = ?
                 WHERE id = ?'
            );
            $update->execute([$title, $slug, $excerpt, $content, $coverImage ?: null, $status, $publishedAt, $id]);
            log_action($pdo, 'article_updated', 'article', $id, 'Artikel ' . $title . ' diperbarui.');
        } else {
            if ($status === 'published') {
                $publishedAt = date('Y-m-d H:i:s');
            }

            $slug = camprent_content_slug($title) . '-' . bin2hex(random_bytes(4));
            $insert = $pdo->prepare(
                'INSERT INTO articles (author_id, title, slug, excerpt, content, cover_image, status, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $insert->execute([
                current_user()['id'], $title, $slug, $excerpt, $content,
                $coverImage ?: null, $status, $publishedAt,
            ]);
            $id = (int) $pdo->lastInsertId();
            log_action($pdo, 'article_created', 'article', $id, 'Artikel ' . $title . ' dibuat.');
        }

        flash('success', $status === 'published' ? 'Artikel berhasil disimpan dan dipublikasikan.' : 'Artikel berhasil disimpan sebagai draft.');
        redirect('manage-articles');
    }

    if ($action === 'delete_article') {
        $id = (int) ($_POST['article_id'] ?? 0);
        if ($id < 1) {
            throw new RuntimeException('Artikel yang akan dihapus tidak valid.');
        }

        $find = $pdo->prepare('SELECT title FROM articles WHERE id = ? LIMIT 1');
        $find->execute([$id]);
        $article = $find->fetch();
        if (!$article) {
            throw new RuntimeException('Artikel tidak ditemukan atau sudah dihapus.');
        }

        $delete = $pdo->prepare('DELETE FROM articles WHERE id = ?');
        $delete->execute([$id]);
        log_action($pdo, 'article_deleted', 'article', $id, 'Artikel ' . $article['title'] . ' dihapus.');
        flash('success', 'Artikel berhasil dihapus.');
        redirect('manage-articles');
    }

    throw new RuntimeException('Aksi pengelolaan artikel tidak dikenal.');
}
