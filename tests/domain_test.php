<?php
require_once __DIR__ . '/../app/domain.php';

function check(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "PASS: {$message}\n";
}

check(rental_days('2026-12-01', '2026-12-01') === 1, 'same-day rental counts as one day');
check(rental_days('2026-12-01', '2026-12-03') === 3, 'rental days are inclusive');
check(dates_overlap('2026-12-01', '2026-12-03', '2026-12-03', '2026-12-05') === true, 'overlapping boundary dates conflict');
check(dates_overlap('2026-12-01', '2026-12-02', '2026-12-03', '2026-12-05') === false, 'non-overlapping date ranges do not conflict');
check(available_stock(5, [2, 1]) === 2, 'available stock subtracts reserved quantities');
check(can_transition_booking('pending', 'confirmed') === true, 'pending booking can be confirmed');
check(can_transition_booking('confirmed', 'pending') === false, 'confirmed booking cannot return to pending');
check(can_request_cancellation('confirmed', null) === true, 'confirmed booking can request cancellation');
check(can_request_cancellation('cancelled', null) === false, 'cancelled booking cannot request cancellation');
check(can_request_cancellation('confirmed', 'pending') === false, 'duplicate pending cancellation is blocked');
check(max_concurrent_reservations([
    ['start_date' => '2026-12-01', 'end_date' => '2026-12-03', 'quantity' => 2],
    ['start_date' => '2026-12-03', 'end_date' => '2026-12-05', 'quantity' => 3],
    ['start_date' => '2026-12-06', 'end_date' => '2026-12-07', 'quantity' => 4],
]) === 5, 'peak stock reservation includes overlapping boundary days');
