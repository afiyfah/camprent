<?php
declare(strict_types=1);

function available_equipment_stock(PDO $pdo, int $equipmentId, string $startDate, string $endDate): int
{
    $stmt = $pdo->prepare("SELECT total_stock, unavailable_stock FROM equipment WHERE id = ? AND is_active = 1");
    $stmt->execute([$equipmentId]);
    $equipment = $stmt->fetch();
    if (!$equipment) return 0;

    $stmt = $pdo->prepare("SELECT quantity FROM bookings WHERE equipment_id = ? AND status IN ('pending','confirmed','picked_up') AND start_date <= ? AND end_date >= ?");
    $stmt->execute([$equipmentId, $endDate, $startDate]);
    $reserved = array_map('intval', array_column($stmt->fetchAll(), 'quantity'));
    return available_stock(max(0, (int) $equipment['total_stock'] - (int) $equipment['unavailable_stock']), $reserved);
}

function create_booking(PDO $pdo, array $input): array
{
    $days = rental_days($input['start_date'], $input['end_date']);
    $quantity = filter_var($input['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
    if (!$quantity) throw new InvalidArgumentException('Jumlah unit harus antara 1 sampai 99.');

    $pdo->beginTransaction();
    try {
        // Lock the equipment row so simultaneous requests cannot oversell the same stock.
        $stmt = $pdo->prepare('SELECT id, name, daily_rate, total_stock, unavailable_stock FROM equipment WHERE id = ? AND is_active = 1 FOR UPDATE');
        $stmt->execute([(int) $input['equipment_id']]);
        $equipment = $stmt->fetch();
        if (!$equipment) throw new RuntimeException('Alat tidak ditemukan atau sedang tidak tersedia.');

        $stmt = $pdo->prepare("SELECT quantity FROM bookings WHERE equipment_id = ? AND status IN ('pending','confirmed','picked_up') AND start_date <= ? AND end_date >= ?");
        $stmt->execute([$equipment['id'], $input['end_date'], $input['start_date']]);
        $reserved = array_map('intval', array_column($stmt->fetchAll(), 'quantity'));
        $remaining = available_stock(max(0, (int) $equipment['total_stock'] - (int) $equipment['unavailable_stock']), $reserved);
        if ($quantity > $remaining) {
            throw new RuntimeException('Stok tidak mencukupi untuk rentang tanggal tersebut. Tersedia ' . $remaining . ' unit.');
        }

        $unitPrice = (float) $equipment['daily_rate'];
        $totalPrice = $unitPrice * $quantity * $days;
        $bookingCode = 'CR-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $pdo->prepare("INSERT INTO bookings (booking_code, user_id, equipment_id, quantity, start_date, end_date, rental_days, unit_price, total_price, status, payment_status, contact_name, contact_email, contact_phone, customer_note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?, ?, ?, ?)");
        $stmt->execute([
            $bookingCode, (int) $input['user_id'], (int) $equipment['id'], $quantity,
            $input['start_date'], $input['end_date'], $days, $unitPrice, $totalPrice,
            $input['contact_name'], $input['contact_email'], $input['contact_phone'], $input['customer_note'] ?? null,
        ]);
        $bookingId = (int) $pdo->lastInsertId();
        $pdo->commit();
        return ['id' => $bookingId, 'booking_code' => $bookingCode, 'total_price' => $totalPrice, 'rental_days' => $days];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
