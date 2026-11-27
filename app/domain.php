<?php
declare(strict_types=1);

/** Rental dates are inclusive: a booking from the 1st to the 3rd uses 3 rental days. */
function rental_days(string $startDate, string $endDate): int
{
    $start = DateTimeImmutable::createFromFormat('!Y-m-d', $startDate);
    $end = DateTimeImmutable::createFromFormat('!Y-m-d', $endDate);
    if (!$start || !$end || $start->format('Y-m-d') !== $startDate || $end->format('Y-m-d') !== $endDate || $end < $start) {
        throw new InvalidArgumentException('Rentang tanggal tidak valid.');
    }
    return (int) $start->diff($end)->days + 1;
}

function dates_overlap(string $startA, string $endA, string $startB, string $endB): bool
{
    return $startA <= $endB && $endA >= $startB;
}

/** @param array<int, int> $reservedQuantities */
function available_stock(int $totalStock, array $reservedQuantities): int
{
    return max(0, $totalStock - array_sum(array_map('intval', $reservedQuantities)));
}

function can_transition_booking(string $from, string $to): bool
{
    $allowed = [
        'pending' => ['confirmed', 'rejected', 'cancelled'],
        'confirmed' => ['picked_up', 'cancelled'],
        'picked_up' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'rejected' => [],
    ];
    return in_array($to, $allowed[$from] ?? [], true);
}

function can_request_cancellation(string $bookingStatus, ?string $existingRequestStatus): bool
{
    if (!in_array($bookingStatus, ['pending', 'confirmed'], true)) {
        return false;
    }
    return $existingRequestStatus !== 'pending' && $existingRequestStatus !== 'approved';
}

/** @param array<int, array{start_date:string,end_date:string,quantity:int|string}> $reservations */
function max_concurrent_reservations(array $reservations): int
{
    $events = [];
    foreach ($reservations as $reservation) {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $reservation['start_date']);
        $end = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $reservation['end_date']);
        $quantity = (int) $reservation['quantity'];
        if (!$start || !$end || $end < $start || $quantity < 1) {
            throw new InvalidArgumentException('Data reservasi untuk perhitungan stok tidak valid.');
        }
        $startKey = $start->format('Y-m-d');
        $endExclusiveKey = $end->modify('+1 day')->format('Y-m-d');
        $events[$startKey] = ($events[$startKey] ?? 0) + $quantity;
        $events[$endExclusiveKey] = ($events[$endExclusiveKey] ?? 0) - $quantity;
    }
    ksort($events);
    $current = 0;
    $peak = 0;
    foreach ($events as $change) {
        $current += $change;
        $peak = max($peak, $current);
    }
    return $peak;
}
