# CampRent — Dokumentasi UI/UX Fase 2

## Tujuan
Fase 2 berfokus pada halaman publik, katalog perlengkapan camping, artikel, navigasi, responsivitas, interaksi antarmuka, dan pengelolaan konten artikel oleh Staff/Admin.

## Arah visual
- **Karakter:** outdoor, hangat, sederhana, dan mudah dibaca.
- **Warna utama:** hijau hutan untuk identitas dan navigasi; warna sage untuk aksen; krem/sand untuk latar bagian; putih untuk kartu konten.
- **Tipografi:** Manrope untuk judul dan DM Sans untuk teks isi.
- **Komponen:** navbar, hero, kartu perlengkapan, filter katalog, kartu artikel, detail artikel, form CMS, tabel artikel, alert, dan footer.

## Struktur halaman
1. **Beranda (`pages/home.php`)** — hero, ringkasan jumlah katalog/kategori/artikel, perlengkapan pilihan, penjelasan cara menjelajahi CampRent, artikel terbaru, dan CTA ke katalog.
2. **Katalog (`pages/equipment.php`)** — pencarian nama/deskripsi/spesifikasi, filter kategori, pengurutan nama/harga/terbaru, dan kartu informasi perlengkapan.
3. **Daftar artikel (`pages/articles.php`)** — pencarian artikel, kartu artikel terpublikasi, ringkasan, tanggal, dan penulis.
4. **Detail artikel (`pages/article.php`)** — judul, ringkasan, metadata penulis/tanggal, gambar sampul, isi artikel, dan tautan kembali.
5. **CMS artikel (`pages/manage-articles.php`)** — form tambah/edit, status draft/published, pencarian/filter, daftar artikel, dan tindakan hapus.

## Navigasi dan akses
- Beranda, Perlengkapan, dan Jurnal dapat diakses pengunjung.
- Profil tetap menggunakan halaman autentikasi yang disiapkan Fase 1.
- Kelola Artikel hanya ditampilkan kepada Staff/Admin dan wajib dilindungi `require_role(['staff', 'admin'])` di router serta halaman.
- Setiap form perubahan data memakai CSRF token.

## Responsivitas
- Desktop: katalog dan artikel menggunakan grid multi-kolom; CMS memakai dua kolom untuk form dan daftar.
- Tablet: grid disederhanakan dan panel CMS ditumpuk.
- Mobile: navigasi menjadi menu toggle, kartu menjadi satu kolom, filter/form ditumpuk, dan tabel berada dalam wadah horizontal-scroll.

## Aksesibilitas dasar
- Dokumen menggunakan `lang="id"` dan viewport responsif.
- Terdapat tautan skip-to-content.
- Tombol menu memiliki `aria-expanded` dan `aria-controls`.
- Input pencarian memiliki label untuk screen reader.
- Tautan fokus keyboard diberi outline.
- Gambar memiliki teks alternatif berdasarkan nama konten.
- Status pesan form memakai `role="status"`.

## Interaksi JavaScript
- Buka/tutup navigasi mobile, tutup menu melalui Escape, klik tautan, atau klik area di luar menu.
- Tutup notifikasi.
- Konfirmasi sebelum menghapus artikel.
- Penghitung karakter ringkasan artikel.

## Checklist pengujian
- [ ] Pengunjung dapat membuka beranda, katalog, daftar artikel, dan detail artikel.
- [ ] Pencarian katalog menemukan nama, deskripsi, dan spesifikasi alat.
- [ ] Filter kategori dan pengurutan katalog berjalan.
- [ ] Artikel draft tidak tampil pada halaman publik.
- [ ] Minimal tiga artikel published tampil.
- [ ] Detail artikel hanya menampilkan artikel berstatus published.
- [ ] User biasa tidak dapat membuka CMS.
- [ ] Staff/Admin dapat membuat, mengedit, menerbitkan, menyimpan draft, dan menghapus artikel.
- [ ] Form CMS menolak URL cover selain HTTP/HTTPS.
- [ ] Form POST tanpa CSRF token ditolak oleh dispatcher.
- [ ] Navigasi, katalog, artikel, dan CMS dapat digunakan pada desktop serta mobile.
