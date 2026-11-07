-- =====================================================================
-- CampRent - Rancangan Database (Modul Nabila / Anggota 4)
-- Dashboard Staff/Admin, Laporan, Kondisi Alat
-- Tanggal: 7 November 2026
--
-- BAGIAN A : tabel milik Nabila (final usulan)
-- BAGIAN B : tabel milik anggota lain (USULAN saja, supaya foreign key
--            bisa dibuat. Silakan disamakan dengan Hasna, Afifah, Aul)
-- BAGIAN C : view bantu
-- =====================================================================

CREATE DATABASE IF NOT EXISTS camprent
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE camprent;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- BAGIAN B : TABEL ANGGOTA LAIN (USULAN - harus disepakati bersama)
-- =====================================================================

-- [Hasna + Nabila] Akun user, staff, admin
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100)  NOT NULL,
  email         VARCHAR(150)  NOT NULL UNIQUE,
  phone         VARCHAR(20)   NULL,
  password_hash VARCHAR(255)  NOT NULL,            -- hasil password_hash()
  role          ENUM('user','staff','admin') NOT NULL DEFAULT 'user',
  is_active     TINYINT(1)    NOT NULL DEFAULT 1,  -- admin bisa menonaktifkan akun
  created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- [Afifah] Kategori & produk
CREATE TABLE IF NOT EXISTS categories (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name        VARCHAR(150) NOT NULL,
  description TEXT NULL,
  image       VARCHAR(255) NULL,
  stock_total INT UNSIGNED NOT NULL DEFAULT 0,      -- total fisik, BUKAN stok tersedia
  daily_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

-- [Aul] Booking
CREATE TABLE IF NOT EXISTS bookings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_code  VARCHAR(20)  NOT NULL UNIQUE,
  user_id       INT UNSIGNED NULL,                  -- NULL jika pengunjung tanpa akun
  contact_name  VARCHAR(100) NOT NULL,
  contact_email VARCHAR(150) NOT NULL,
  contact_phone VARCHAR(20)  NOT NULL,
  start_date    DATE NOT NULL,
  end_date      DATE NOT NULL,
  total_price   DECIMAL(12,2) NOT NULL DEFAULT 0,
  -- USULAN status (sepakati dengan Aul):
  -- pending   = baru dibuat, belum dibayar/dikonfirmasi
  -- confirmed = sudah dikonfirmasi, menunggu pengambilan
  -- picked_up = alat sudah diambil penyewa
  -- completed = alat sudah dikembalikan & diperiksa
  -- cancelled = dibatalkan (TIDAK lagi menghitung stok)
  status        ENUM('pending','confirmed','picked_up','completed','cancelled')
                NOT NULL DEFAULT 'pending',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_booking_dates (start_date, end_date),
  INDEX idx_booking_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS booking_details (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id     INT UNSIGNED NOT NULL,
  product_id     INT UNSIGNED NOT NULL,
  quantity       INT UNSIGNED NOT NULL,
  price_per_day  DECIMAL(12,2) NOT NULL,             -- snapshot tarif saat booking
  subtotal       DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL,
  amount     DECIMAL(12,2) NOT NULL,
  method     VARCHAR(50) NOT NULL DEFAULT 'dummy',
  status     ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  paid_at    DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- BAGIAN A : TABEL MILIK NABILA
-- =====================================================================

-- ---------------------------------------------------------------------
-- cancellations  (Aul membuat baris awal 'pending', Nabila memprosesnya)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cancellations (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id    INT UNSIGNED NOT NULL,
  requested_by  INT UNSIGNED NULL,                  -- user pengaju (NULL jika tamu)
  reason        TEXT NOT NULL,
  status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  requested_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- diisi saat staff memproses
  processed_by  INT UNSIGNED NULL,                  -- staff/admin yang memutuskan
  processed_at  DATETIME NULL,
  staff_note    TEXT NULL,                          -- alasan approve/reject

  -- catatan refund/deposit (administratif saja, tanpa transfer sungguhan)
  refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  refund_status ENUM('none','to_refund','refunded') NOT NULL DEFAULT 'none',
  refund_note   VARCHAR(255) NULL,

  FOREIGN KEY (booking_id)   REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (requested_by) REFERENCES users(id)    ON DELETE SET NULL,
  FOREIGN KEY (processed_by) REFERENCES users(id)    ON DELETE SET NULL,
  INDEX idx_cancel_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- handover_records : header checklist pengambilan / pengembalian
-- (disepakati dengan Aul karena kartu pengambilan ada di sisi user)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS handover_records (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id  INT UNSIGNED NOT NULL,
  type        ENUM('pickup','return') NOT NULL,
  handled_by  INT UNSIGNED NULL,                    -- staff yang memeriksa
  handled_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes       TEXT NULL,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (handled_by) REFERENCES users(id)    ON DELETE SET NULL,
  INDEX idx_handover_booking (booking_id, type)
) ENGINE=InnoDB;

-- Tambahan (tidak ada di dokumen, tapi dibutuhkan): detail per alat.
-- Satu alat dalam satu booking bisa sebagian baik, sebagian rusak.
CREATE TABLE IF NOT EXISTS handover_items (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  handover_id       INT UNSIGNED NOT NULL,
  booking_detail_id INT UNSIGNED NOT NULL,
  product_id        INT UNSIGNED NOT NULL,
  item_condition    ENUM('baik','rusak','hilang') NOT NULL DEFAULT 'baik',
  quantity          INT UNSIGNED NOT NULL,
  note              VARCHAR(255) NULL,
  FOREIGN KEY (handover_id)       REFERENCES handover_records(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_detail_id) REFERENCES booking_details(id)  ON DELETE CASCADE,
  FOREIGN KEY (product_id)        REFERENCES products(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- damage_reports : kerusakan / kehilangan + biaya penggantian
-- Staff melapor, ADMIN menetapkan biaya (usulan, perlu disepakati).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS damage_reports (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id        INT UNSIGNED NOT NULL,          -- wajib terhubung ke booking
  booking_detail_id INT UNSIGNED NULL,
  product_id        INT UNSIGNED NOT NULL,
  handover_id       INT UNSIGNED NULL,              -- pemeriksaan yang menemukan masalah
  issue_type        ENUM('rusak','hilang') NOT NULL,
  quantity          INT UNSIGNED NOT NULL DEFAULT 1,
  description       TEXT NOT NULL,                  -- keterangan / bukti tertulis
  evidence_file     VARCHAR(255) NULL,              -- path foto bukti (opsional)

  -- biaya penggantian (diisi admin; NULL = belum ditetapkan)
  replacement_fee   DECIMAL(12,2) NULL,
  liability_status  ENUM('not_set','pending','paid','waived') NOT NULL DEFAULT 'not_set',
  -- not_set = biaya belum ditetapkan | pending = penyewa wajib mengganti
  -- paid = sudah diganti | waived = dibebaskan admin

  -- status stok: selama 'unavailable', alat TIDAK boleh disewakan
  stock_status      ENUM('unavailable','resolved') NOT NULL DEFAULT 'unavailable',
  resolution_note   TEXT NULL,
  resolved_at       DATETIME NULL,

  reported_by       INT UNSIGNED NULL,              -- staff pelapor
  decided_by        INT UNSIGNED NULL,              -- admin penetap biaya
  decided_at        DATETIME NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (booking_id)        REFERENCES bookings(id),
  FOREIGN KEY (booking_detail_id) REFERENCES booking_details(id) ON DELETE SET NULL,
  FOREIGN KEY (product_id)        REFERENCES products(id),
  FOREIGN KEY (handover_id)       REFERENCES handover_records(id) ON DELETE SET NULL,
  FOREIGN KEY (reported_by)       REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (decided_by)        REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_damage_stock (product_id, stock_status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- staff_logs : audit log tindakan penting staff/admin
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS staff_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NULL,                    -- pelaku
  action      VARCHAR(60)  NOT NULL,                -- contoh: cancellation.approve
  entity_type VARCHAR(40)  NOT NULL,                -- contoh: booking, product, user
  entity_id   INT UNSIGNED NULL,
  description VARCHAR(255) NULL,
  ip_address  VARCHAR(45)  NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_log_user (user_id),
  INDEX idx_log_entity (entity_type, entity_id),
  INDEX idx_log_created (created_at)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- BAGIAN C : VIEW BANTU
-- =====================================================================

-- Jumlah unit per produk yang TIDAK boleh disewakan karena rusak/hilang
-- dan belum diselesaikan. Dipakai Aul saat cek stok:
--   stok_layak_sewa = products.stock_total - COALESCE(qty_unavailable, 0)
--                     - (unit yang sudah dibooking pada tanggal bertumpuk)
CREATE OR REPLACE VIEW v_product_unavailable AS
SELECT product_id, SUM(quantity) AS qty_unavailable
FROM damage_reports
WHERE stock_status = 'unavailable'
GROUP BY product_id;

-- Ringkasan cepat untuk daftar pembatalan pending (halaman staff)
CREATE OR REPLACE VIEW v_pending_cancellations AS
SELECT c.id AS cancellation_id, c.reason, c.requested_at,
       b.id AS booking_id, b.booking_code, b.contact_name,
       b.start_date, b.end_date, b.total_price, b.status AS booking_status
FROM cancellations c
JOIN bookings b ON b.id = c.booking_id
WHERE c.status = 'pending'
ORDER BY c.requested_at ASC;
