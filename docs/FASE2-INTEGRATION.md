# Integrasi Fase 2 ke branch `fase-2-frontend`

Paket ini adalah **overlay Fase 2**, bukan salinan seluruh proyek. Salin folder sesuai struktur ke root repository agar file Fase 1 dan modul anggota lain tidak tertimpa.

## 1. File overlay

- `assets/css/style.css`
- `assets/js/app.js`
- `layout/header.php`
- `layout/footer.php`
- `pages/home.php`
- `pages/equipment.php`
- `pages/articles.php`
- `pages/article.php`
- `pages/manage-articles.php`
- `pages/not-found.php`
- `includes/actions/content.php`
- `database/phase2_content.sql`
- `tests/content_test.php`
- `docs/phase2-ui-ux.md`

## 2. Database

1. Pastikan database Fase 1 sudah dibuat dan tabel `users`, `categories`, serta `equipment` tersedia.
2. Import `database/phase2_content.sql` melalui phpMyAdmin setelah schema Fase 1.
3. Migration membuat tabel `articles` jika belum tersedia dan memasukkan tiga artikel contoh jika slug-nya belum ada.
4. Jangan mengimpor ulang `database/camprent.sql` ke database berisi data penting karena file schema awal dapat menghapus tabel saat proses instalasi.

## 3. Router `index.php`

Tambahkan halaman berikut ke daftar route publik:

```php
$publicPages = array_merge($publicPages, ['equipment', 'articles', 'article']);
```

Tambahkan CMS ke daftar route Staff/Admin:

```php
$staffPages[] = 'manage-articles';
```

Pastikan halaman CMS dilindungi:

```php
if ($page === 'manage-articles') {
    require_role(['staff', 'admin']);
}
```

Jika router menggunakan array `$allPages`, pastikan route baru ikut tergabung di dalamnya. Pertahankan route dan pemeriksaan akses Fase 1 yang sudah ada.

## 4. Dispatcher POST `includes/actions.php`

Dispatcher pusat tetap dipertahankan. Di dalam `switch ($action)`, tambahkan dua case berikut dan hapus implementasi `save_article`/`delete_article` lama jika ada agar tidak ada handler ganda:

```php
case 'save_article':
case 'delete_article':
    require_once __DIR__ . '/actions/content.php';
    handle_content_action($pdo, $action);
    break;
```

`require_post()` dan verifikasi CSRF harus tetap dijalankan di bagian awal dispatcher sebelum switch. Pertahankan blok `try/catch` dispatcher yang sudah ada.

Untuk kesalahan saat menyimpan artikel, atur fallback di dua blok `catch` dispatcher agar aksi artikel kembali ke CMS:

```php
$fallback = in_array($action, ['save_article', 'delete_article'], true)
    ? 'manage-articles'
    : (is_staff() ? 'staff-dashboard' : (is_logged_in() ? 'dashboard' : 'home'));
redirect($fallback);
```

Jangan mengubah validasi login/logout atau fungsi autentikasi Fase 1.

## 5. Header dan footer

File header/footer pada paket ini menampilkan menu Beranda, Perlengkapan, Jurnal, Profil (ketika login), dan Kelola Artikel (khusus Staff/Admin). Menu booking/dashboard tidak ditambahkan karena berada di luar cakupan Fase 2.

Pastikan `index.php` memuat `layout/header.php` sebelum halaman dan `layout/footer.php` sesudah halaman seperti struktur proyek saat ini.

## 6. Jalankan pengujian

Dari root repository:

```bash
php tests/content_test.php
php tests/domain_test.php
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
```

Tes integrasi halaman dan CRUD tetap perlu dijalankan dengan MySQL/MariaDB aktif di XAMPP.

## 7. Commit Fase 2

```bash
git add assets/css/style.css assets/js/app.js layout/header.php layout/footer.php pages/home.php pages/equipment.php pages/articles.php pages/article.php pages/manage-articles.php pages/not-found.php includes/actions/content.php database/phase2_content.sql tests/content_test.php docs/phase2-ui-ux.md docs/FASE2-INTEGRATION.md
git commit -m "feat(frontend): complete phase 2 public pages and article CMS"
git push origin fase-2-frontend
```

Jangan mengubah branch `main` atau menggabungkan pekerjaan ke branch anggota lain tanpa Pull Request dan persetujuan kelompok.
