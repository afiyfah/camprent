<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $page = 'home', array $params = []): string
{
    return 'index.php?' . http_build_query(array_merge(['page' => $page], $params));
}

function redirect(string $page = 'home', array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';

    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa. Muat ulang halaman, lalu coba kembali.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): array
{
    $user = current_user();

    if (!$user) {
        flash('info', 'Silakan masuk untuk melanjutkan.');
        redirect('login');
    }

    return $user;
}

function require_role(array $roles): array
{
    $user = require_login();

    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Akses tidak diizinkan.');
    }

    return $user;
}

function is_staff(): bool
{
    return in_array(current_user()['role'] ?? '', ['staff', 'admin'], true);
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function money(float|int|string $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function date_id(?string $date, bool $withYear = true): string
{
    if (!$date) return '-';

    $timestamp = strtotime($date);

    return date($withYear ? 'd M Y' : 'd M', $timestamp);
}

function status_text(string $status): string
{
    return [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'picked_up' => 'Dipinjam',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'rejected' => 'Ditolak',
        'approved' => 'Disetujui',
        'paid_demo' => 'Pembayaran simulasi',
        'unpaid' => 'Belum dibayar',
        'refund_recorded' => 'Refund dicatat',
        'normal' => 'Baik',
        'damaged' => 'Rusak',
        'lost' => 'Hilang',
    ][$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function status_class(string $status): string
{
    return match ($status) {
        'confirmed', 'approved', 'completed', 'paid_demo', 'normal' => 'success',
        'pending', 'unpaid', 'picked_up' => 'warning',
        'cancelled', 'rejected', 'damaged', 'lost' => 'danger',
        default => 'neutral',
    };
}

function text_length(string $value): int
{
    $count = preg_match_all('/./us', $value, $matches);

    return $count === false ? strlen($value) : $count;
}

function text_substr(string $value, int $start, int $length): string
{
    $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

    if ($chars === false) {
        return substr($value, $start, $length);
    }

    return implode('', array_slice($chars, $start, $length));
}

function post_string(string $key, int $maxLength = 5000): string
{
    $value = trim((string) ($_POST[$key] ?? ''));

    return text_substr($value, 0, $maxLength);
}

function log_action(
    PDO $pdo,
    string $action,
    string $entityType,
    ?int $entityId,
    string $description
): void {
    $actorId = current_user()['id'] ?? null;
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs
        (actor_id, action, entity_type, entity_id, description, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $actorId,
        $action,
        $entityType,
        $entityId,
        $description,
        $ip
    ]);
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode tidak diizinkan.');
    }

    verify_csrf();
}

function active_page(string $page, array $matches): string
{
    return in_array($page, $matches, true) ? ' active' : '';
}
