
-- =====================================================
-- CampRent
-- Rancangan Database Bagian Aul
-- Modul: Reservasi, Booking, dan Pembayaran User
-- =====================================================

-- 1. TABEL BOOKINGS
-- Menyimpan informasi pemesanan alat camping.

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT UNSIGNED NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(150) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM(
        'pending',
        'confirmed',
        'picked_up',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_bookings_date_range (start_date, end_date),
    INDEX idx_bookings_status (status),

    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2. TABEL BOOKING_DETAILS
-- Menyimpan rincian barang yang dipesan.

CREATE TABLE IF NOT EXISTS booking_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price_per_day DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,

    INDEX idx_booking_details_booking (booking_id),
    INDEX idx_booking_details_product (product_id),

    CONSTRAINT fk_booking_details_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_booking_details_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 3. TABEL PAYMENTS
-- Menyimpan informasi pembayaran pemesanan.

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method VARCHAR(50) NOT NULL DEFAULT 'dummy',
    status ENUM(
        'unpaid',
        'paid',
        'refunded'
    ) NOT NULL DEFAULT 'unpaid',
    paid_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_payments_booking (booking_id),
    INDEX idx_payments_status (status),

    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
