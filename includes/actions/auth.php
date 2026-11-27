<?php
declare(strict_types=1);

require_post();

/**
 * Membaca input POST dengan aman.
 * Menolak input berbentuk array dan membatasi panjang teks.
 */
$readPostText = static function (string $key, int $maxLength): string {
    $value = $_POST[$key] ?? '';

    if (!is_string($value)) {
        return '';
    }

    return text_substr(trim($value), 0, $maxLength);
};

$action = $readPostText('action', 60);

/**
 * Pengguna yang sudah login tidak boleh mengakses
 * proses login atau registrasi kembali.
 */
if (is_logged_in() && in_array($action, ['login', 'register'], true)) {
    redirect(
        current_user()['role'] === 'user'
            ? 'dashboard'
            : 'staff-dashboard'
    );
}

try {
    switch ($action) {

        // =====================================================
        // LOGIN
        // =====================================================
        case 'login': {

            $email = strtolower($readPostText('email', 160));

            $passwordInput = $_POST['password'] ?? '';
            $password = is_string($passwordInput) ? $passwordInput : '';

            $stmt = $pdo->prepare(
                'SELECT id, name, email, phone, password_hash, role, is_active
                 FROM users
                 WHERE email = ?
                 LIMIT 1'
            );

            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (
                !$user ||
                (int) $user['is_active'] !== 1 ||
                !password_verify($password, $user['password_hash'])
            ) {
                throw new RuntimeException(
                    'Email atau password tidak sesuai.'
                );
            }

            // Mencegah session fixation.
            session_regenerate_id(true);

            unset($user['password_hash'], $user['is_active']);

            $_SESSION['user'] = $user;

            flash(
                'success',
                'Selamat datang kembali, ' . $user['name'] . '.'
            );

            redirect(
                $user['role'] === 'user'
                    ? 'dashboard'
                    : 'staff-dashboard'
            );
        }

        // =====================================================
        // REGISTER
        // =====================================================
        case 'register': {

            $name = $readPostText('name', 120);
            $email = strtolower($readPostText('email', 160));
            $phone = $readPostText('phone', 30);

            $passwordInput = $_POST['password'] ?? '';
            $confirmInput = $_POST['password_confirmation'] ?? '';

            $password = is_string($passwordInput)
                ? $passwordInput
                : '';

            $confirm = is_string($confirmInput)
                ? $confirmInput
                : '';

            if (text_length($name) < 2) {
                throw new RuntimeException(
                    'Nama minimal 2 karakter.'
                );
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    'Format email tidak valid.'
                );
            }

            if (text_length($password) < 8) {
                throw new RuntimeException(
                    'Password minimal 8 karakter.'
                );
            }

            if (strlen($password) > 4096) {
                throw new RuntimeException(
                    'Password terlalu panjang.'
                );
            }

            if ($password !== $confirm) {
                throw new RuntimeException(
                    'Konfirmasi password tidak sama.'
                );
            }

            // Role pengguna baru selalu user.
            $stmt = $pdo->prepare(
                "INSERT INTO users
                    (name, email, phone, password_hash, role)
                 VALUES (?, ?, ?, ?, 'user')"
            );

            $stmt->execute([
                $name,
                $email,
                $phone,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            $id = (int) $pdo->lastInsertId();

            // Regenerasi session sebelum menetapkan identitas user.
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => 'user'
            ];

            flash(
                'success',
                'Akun berhasil dibuat. Selamat datang di CampRent!'
            );

            redirect('dashboard');
        }

        // =====================================================
        // LOGOUT
        // =====================================================
        case 'logout': {

            // Hapus seluruh data session.
            $_SESSION = [];

            // Hapus cookie session dari browser.
            if (ini_get('session.use_cookies')) {

                $params = session_get_cookie_params();

                setcookie(session_name(), '', [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax'
                ]);
            }

            session_destroy();

            redirect('home');
        }

        // =====================================================
        // UPDATE PROFILE
        // =====================================================
        case 'update_profile': {

            $user = require_login();

            $name = $readPostText('name', 120);
            $email = strtolower($readPostText('email', 160));
            $phone = $readPostText('phone', 30);

            if (text_length($name) < 2) {
                throw new RuntimeException(
                    'Nama minimal 2 karakter.'
                );
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    'Format email tidak valid.'
                );
            }

            $stmt = $pdo->prepare(
                'UPDATE users
                 SET name = ?, email = ?, phone = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $name,
                $email,
                $phone,
                $user['id']
            ]);

            // Sinkronisasi data session.
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;

            flash(
                'success',
                'Profil berhasil diperbarui.'
            );

            redirect('profile');
        }

        // =====================================================
        // UNKNOWN ACTION
        // =====================================================
        default:

            throw new RuntimeException(
                'Aksi autentikasi tidak dikenal.'
            );
    }

} catch (PDOException $exception) {

    // Penanganan error database.
    $message = $exception->getCode() === '23000'
        ? 'Email sudah digunakan atau data memiliki relasi yang tidak dapat diproses.'
        : 'Terjadi kesalahan database. Periksa data dan coba kembali.';

    if (getenv('APP_DEBUG') === 'true') {
        $message .= ' ' . $exception->getMessage();
    }

    flash('error', $message);

    redirect(
        is_logged_in() ? 'profile' : 'login'
    );

} catch (RuntimeException $exception) {

    // Penanganan validasi dan kesalahan yang diketahui.
    flash('error', $exception->getMessage());

    redirect(
        is_logged_in() ? 'profile' : 'login'
    );

} catch (Throwable $exception) {

    // Kesalahan tak terduga dicatat ke error log server.
    error_log($exception->__toString());

    $message = 'Terjadi kesalahan sistem. Silakan coba kembali.';

    if (getenv('APP_DEBUG') === 'true') {
        $message .= ' ' . $exception->getMessage();
    }

    flash('error', $message);

    redirect(
        is_logged_in() ? 'profile' : 'login'
    );
}
