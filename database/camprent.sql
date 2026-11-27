-- CampRent | Native PHP + MySQL
-- Import this file using phpMyAdmin. It creates the database and demo data.
CREATE DATABASE IF NOT EXISTS camprent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE camprent;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS booking_returns;
DROP TABLE IF EXISTS cancellation_requests;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL DEFAULT '',
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','user') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE equipment (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    specifications TEXT NULL,
    image_url VARCHAR(500) NULL,
    daily_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_stock INT UNSIGNED NOT NULL DEFAULT 0,
    unavailable_stock INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipment_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_equipment_active (is_active),
    INDEX idx_equipment_category (category_id)
) ENGINE=InnoDB;

CREATE TABLE articles (
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
    CONSTRAINT fk_articles_author FOREIGN KEY (author_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_articles_status_date (status, published_at)
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(24) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    rental_days SMALLINT UNSIGNED NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    status ENUM('pending','confirmed','picked_up','completed','cancelled','rejected') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid','paid_demo','refund_recorded') NOT NULL DEFAULT 'unpaid',
    contact_name VARCHAR(120) NOT NULL,
    contact_email VARCHAR(160) NOT NULL,
    contact_phone VARCHAR(30) NOT NULL,
    customer_note TEXT NULL,
    staff_note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_bookings_date_range (start_date, end_date),
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_equipment_status (equipment_id, status)
) ENGINE=InnoDB;

CREATE TABLE cancellation_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    staff_id BIGINT UNSIGNED NULL,
    staff_note TEXT NULL,
    refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    deposit_note VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    CONSTRAINT fk_cancel_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cancel_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cancel_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_cancel_status (status)
) ENGINE=InnoDB;

CREATE TABLE booking_returns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    condition_status ENUM('normal','damaged','lost') NOT NULL DEFAULT 'normal',
    notes TEXT NULL,
    replacement_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    staff_id BIGINT UNSIGNED NULL,
    returned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_return_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_return_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_actor (actor_id)
) ENGINE=InnoDB;

-- Demo accounts. Passwords are hashed with PHP password_hash().
INSERT INTO users (id, name, email, phone, password_hash, role) VALUES
(1, 'CampRent Administrator', 'admin@demo.test', '081200000001', '$2y$12$mwmdOrqnD5GBYV790kMequGXIQ25q0mVznOMErS6AlxCTXp/QD4o6', 'admin'),
(2, 'CampRent Staff', 'staff@demo.test', '081200000002', '$2y$12$ncXTvS8g47y31sg5hMYpW.zhRuOz6ZsEcgfS9XNOlAQK7c5xf3HQu', 'staff'),
(3, 'Raka Pratama', 'user@demo.test', '081200000003', '$2y$12$a3vcLbNMmIDBUvF/b6x1i.eFk1cXqphHojPHmRlsq0pd9EW2WWDFq', 'user');

INSERT INTO categories (id, name, slug, description) VALUES
(1, 'Tenda & Shelter', 'tenda-shelter', 'Perlindungan utama untuk berkemah.'),
(2, 'Tidur & Kenyamanan', 'tidur-kenyamanan', 'Perlengkapan tidur agar istirahat lebih nyaman.'),
(3, 'Memasak & Peralatan', 'memasak-peralatan', 'Peralatan memasak dan kebutuhan makan di alam.'),
(4, 'Penerangan & Navigasi', 'penerangan-navigasi', 'Peralatan penerangan dan navigasi perjalanan.'),
(5, 'Paket Camping', 'paket-camping', 'Paket perlengkapan siap sewa untuk perjalanan praktis.');

INSERT INTO equipment (id, category_id, name, slug, description, specifications, image_url, daily_rate, total_stock, is_active) VALUES
(1, 1, 'Tenda Dome 4 Orang', 'tenda-dome-4-orang', 'Tenda dome untuk perjalanan bersama teman atau keluarga kecil. Material tahan cuaca ringan dan memiliki ruang vestibule untuk menyimpan alas kaki.', 'Kapasitas 4 orang · Double layer · Waterproof 3000 mm · Berat ±4,2 kg', 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1200&q=85', 65000, 12, 1),
(2, 1, 'Tenda Ultralight 2 Orang', 'tenda-ultralight-2-orang', 'Pilihan ringkas untuk hiking dan camping berdua. Bobot ringan dengan bentuk praktis untuk dibawa dalam perjalanan.', 'Kapasitas 2 orang · Single entrance · Berat ±2,1 kg', 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=1200&q=85', 55000, 8, 1),
(3, 2, 'Sleeping Bag Polar', 'sleeping-bag-polar', 'Sleeping bag hangat untuk menjaga kenyamanan istirahat di area camping dengan suhu sejuk.', 'Comfort temperature 8–15°C · Bahan polyester · Termasuk pouch', 'https://images.unsplash.com/photo-1504851149312-7a075b496cc7?auto=format&fit=crop&w=1200&q=85', 18000, 20, 1),
(4, 2, 'Matras Camping', 'matras-camping', 'Matras praktis sebagai alas tidur untuk membantu mengurangi kontak langsung dengan permukaan tanah.', 'Ukuran 180 × 50 cm · Foam · Mudah digulung', 'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=1200&q=85', 12000, 24, 1),
(5, 3, 'Kompor Portable Set', 'kompor-portable-set', 'Kompor portable untuk menyiapkan makanan dan minuman hangat selama kegiatan camping.', 'Kompor portable · Nesting 2 pcs · Tas penyimpanan', 'https://images.unsplash.com/photo-1532339142463-fd0a8979791a?auto=format&fit=crop&w=1200&q=85', 25000, 10, 1),
(6, 4, 'Lampu Tenda LED', 'lampu-tenda-led', 'Lampu LED ringan untuk penerangan area tenda saat malam hari.', 'Rechargeable · 3 mode cahaya · USB charging', 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=85', 15000, 15, 1),
(7, 5, 'Paket Camping Starter 2 Orang', 'paket-camping-starter-2-orang', 'Paket praktis untuk camping pertama bersama satu teman. Termasuk tenda 2 orang, dua sleeping bag, dua matras, dan lampu tenda.', 'Paket set · Kapasitas 2 orang · 1 unit paket dihitung sebagai satu set lengkap', 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=1200&q=85', 125000, 4, 1),
(8, 5, 'Paket Camping Family 4 Orang', 'paket-camping-family-4-orang', 'Paket keluarga untuk perjalanan santai. Satu set mencakup tenda 4 orang, empat sleeping bag, empat matras, kompor portable, dan lampu tenda.', 'Paket set · Kapasitas 4 orang · 1 unit paket dihitung sebagai satu set lengkap', 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1200&q=85', 220000, 3, 1);

INSERT INTO articles (id, author_id, title, slug, excerpt, content, cover_image, status, published_at) VALUES
(1, 1, 'Tips Memilih Tenda untuk Pemula', 'tips-memilih-tenda-untuk-pemula', 'Kenali kapasitas, cuaca, bobot, dan jenis tenda sebelum menentukan pilihan pertama.', 'Memilih tenda pertama tidak harus rumit. Mulailah dari jumlah orang yang akan tidur di dalam tenda. Kapasitas tenda biasanya menunjukkan jumlah orang dalam kondisi cukup rapat, jadi pertimbangkan memilih kapasitas satu tingkat lebih besar jika membawa banyak barang.\n\nPerhatikan juga lokasi dan cuaca. Untuk camping di area dataran tinggi, pilih tenda dengan lapisan pelindung hujan, jahitan yang rapi, serta ventilasi yang baik. Double layer membantu mengurangi kondensasi dan memberi perlindungan tambahan.\n\nJika perjalanan melibatkan trekking, bobot dan ukuran kemasan menjadi penting. Pastikan seluruh komponen tenda lengkap dan lakukan simulasi pemasangan sebelum berangkat.', 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1200&q=85', 'published', NOW()),
(2, 1, 'Checklist Alat Camping: Jangan Sampai Tertinggal', 'checklist-alat-camping', 'Daftar perlengkapan inti untuk membantu perjalanan camping lebih aman dan nyaman.', 'Persiapan yang baik dimulai dari daftar perlengkapan. Kelompokkan barang berdasarkan fungsi agar proses pengecekan lebih mudah. Perlengkapan shelter mencakup tenda, pasak, flysheet, dan footprint.\n\nUntuk istirahat, siapkan sleeping bag, matras, dan pakaian hangat. Peralatan memasak dapat berupa kompor portable, nesting, alat makan, serta air minum yang cukup. Jangan lupakan lampu, baterai cadangan, kotak P3K, jas hujan, dan kantong sampah.\n\nSesuaikan isi tas dengan durasi, cuaca, dan karakter lokasi. Periksa kembali kondisi alat sebelum berangkat dan pastikan semua perlengkapan dikemas dengan aman.', 'https://images.unsplash.com/photo-1504851149312-7a075b496cc7?auto=format&fit=crop&w=1200&q=85', 'published', NOW()),
(3, 1, 'Camping Pertama: Etika Menjaga Alam', 'etika-menjaga-alam-saat-camping', 'Nikmati alam tanpa meninggalkan jejak. Kebiasaan kecil membuat area camping tetap lestari.', 'Camping bukan hanya tentang mendirikan tenda dan menikmati pemandangan. Kita juga bertanggung jawab menjaga tempat yang dikunjungi. Gunakan jalur yang telah tersedia dan hindari merusak vegetasi di sekitar lokasi.\n\nBawa kembali seluruh sampah, termasuk sisa makanan dan kemasan kecil. Gunakan area api unggun yang diizinkan oleh pengelola dan pastikan api benar-benar padam sebelum meninggalkan lokasi. Hormati ketenangan pengunjung lain dengan menjaga volume suara, terutama pada malam hari.\n\nPrinsip sederhana yang bisa dipegang adalah meninggalkan lokasi dalam keadaan sama baiknya atau lebih baik daripada saat kita datang.', 'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=1200&q=85', 'published', NOW());

-- Sample activity for the demo dashboard and user history.
INSERT INTO bookings (id, booking_code, user_id, equipment_id, quantity, start_date, end_date, rental_days, unit_price, total_price, status, payment_status, contact_name, contact_email, contact_phone, customer_note) VALUES
(1, 'CR-DEMO-001', 3, 1, 1, '2026-11-20', '2026-11-22', 3, 65000, 195000, 'confirmed', 'paid_demo', 'Raka Pratama', 'user@demo.test', '081200000003', 'Demo booking yang sudah dikonfirmasi.'),
(2, 'CR-DEMO-002', 3, 5, 1, '2026-11-25', '2026-11-25', 1, 25000, 25000, 'pending', 'unpaid', 'Raka Pratama', 'user@demo.test', '081200000003', 'Demo booking menunggu pembayaran simulasi.');

INSERT INTO cancellation_requests (id, booking_id, user_id, reason, status) VALUES
(1, 2, 3, 'Ada perubahan jadwal perjalanan sehingga reservasi kompor portable perlu dibatalkan.', 'pending');

INSERT INTO audit_logs (actor_id, action, entity_type, entity_id, description, ip_address) VALUES
(2, 'demo_seed', 'booking', 1, 'Data demo awal dibuat untuk memperlihatkan alur dashboard operasional.', '127.0.0.1');
