-- CampRent — Fase 2: tabel artikel dan tiga artikel awal.
-- Jalankan SETELAH database/camprent.sql dari Fase 1.
-- Aman dijalankan ulang: tabel tidak ditimpa dan artikel seed tidak diduplikasi.

CREATE TABLE IF NOT EXISTS articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    excerpt VARCHAR(350) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    cover_image VARCHAR(500) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_author
        FOREIGN KEY (author_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_articles_status_date (status, published_at)
) ENGINE=InnoDB;

INSERT INTO articles (author_id, title, slug, excerpt, content, cover_image, status, published_at)
SELECT NULL,
       'Tips Memilih Tenda untuk Pemula',
       'tips-memilih-tenda-untuk-pemula',
       'Kenali kapasitas, cuaca, bobot, dan jenis tenda sebelum menentukan pilihan pertama.',
       'Memilih tenda pertama tidak harus rumit. Mulailah dari jumlah orang yang akan tidur di dalam tenda. Kapasitas tenda biasanya menunjukkan jumlah orang dalam kondisi cukup rapat, jadi pertimbangkan memilih kapasitas satu tingkat lebih besar jika membawa banyak barang.\n\nPerhatikan juga lokasi dan cuaca. Untuk camping di area dataran tinggi, pilih tenda dengan lapisan pelindung hujan, jahitan yang rapi, serta ventilasi yang baik. Double layer membantu mengurangi kondensasi dan memberi perlindungan tambahan.\n\nJika perjalanan melibatkan trekking, bobot dan ukuran kemasan menjadi penting. Pastikan seluruh komponen tenda lengkap dan lakukan simulasi pemasangan sebelum berangkat.',
       'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1200&q=85',
       'published', CURRENT_TIMESTAMP
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM articles WHERE slug = 'tips-memilih-tenda-untuk-pemula');

INSERT INTO articles (author_id, title, slug, excerpt, content, cover_image, status, published_at)
SELECT NULL,
       'Checklist Alat Camping: Jangan Sampai Tertinggal',
       'checklist-alat-camping',
       'Daftar perlengkapan inti untuk membantu perjalanan camping lebih aman dan nyaman.',
       'Persiapan yang baik dimulai dari daftar perlengkapan. Kelompokkan barang berdasarkan fungsi agar proses pengecekan lebih mudah. Perlengkapan shelter mencakup tenda, pasak, flysheet, dan footprint.\n\nUntuk istirahat, siapkan sleeping bag, matras, dan pakaian hangat. Peralatan memasak dapat berupa kompor portable, nesting, alat makan, serta air minum yang cukup. Jangan lupakan lampu, baterai cadangan, kotak P3K, jas hujan, dan kantong sampah.\n\nSesuaikan isi tas dengan durasi, cuaca, dan karakter lokasi. Periksa kembali kondisi alat sebelum berangkat dan pastikan semua perlengkapan dikemas dengan aman.',
       'https://images.unsplash.com/photo-1504851149312-7a075b496cc7?auto=format&fit=crop&w=1200&q=85',
       'published', CURRENT_TIMESTAMP
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM articles WHERE slug = 'checklist-alat-camping');

INSERT INTO articles (author_id, title, slug, excerpt, content, cover_image, status, published_at)
SELECT NULL,
       'Camping Pertama: Etika Menjaga Alam',
       'etika-menjaga-alam-saat-camping',
       'Nikmati alam tanpa meninggalkan jejak. Kebiasaan kecil membuat area camping tetap lestari.',
       'Camping bukan hanya tentang mendirikan tenda dan menikmati pemandangan. Kita juga bertanggung jawab menjaga tempat yang dikunjungi. Gunakan jalur yang telah tersedia dan hindari merusak vegetasi di sekitar lokasi.\n\nBawa kembali seluruh sampah, termasuk sisa makanan dan kemasan kecil. Gunakan area api unggun yang diizinkan oleh pengelola dan pastikan api benar-benar padam sebelum meninggalkan lokasi. Hormati ketenangan pengunjung lain dengan menjaga volume suara, terutama pada malam hari.\n\nPrinsip sederhana yang bisa dipegang adalah meninggalkan lokasi dalam keadaan sama baiknya atau lebih baik daripada saat kita datang.',
       'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=1200&q=85',
       'published', CURRENT_TIMESTAMP
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM articles WHERE slug = 'etika-menjaga-alam-saat-camping');
