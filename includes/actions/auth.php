<?php
declare(strict_types=1);

require_post();
$action = post_string('action', 60);

try {
    switch ($action) {
        case 'login': {
            $email = strtolower(post_string('email', 160));
            $password = (string) ($_POST['password'] ?? '');
            $stmt = $pdo->prepare('SELECT id, name, email, phone, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user || !(int) $user['is_active'] || !password_verify($password, $user['password_hash'])) {
                throw new RuntimeException('Email atau password tidak sesuai.');
            }
            session_regenerate_id(true);
            unset($user['password_hash'], $user['is_active']);
            $_SESSION['user'] = $user;
            flash('success', 'Selamat datang kembali, ' . $user['name'] . '.');
            redirect($user['role'] === 'user' ? 'dashboard' : 'staff-dashboard');
        }

        case 'register': {
            $name = post_string('name', 120);
            $email = strtolower(post_string('email', 160));
            $phone = post_string('phone', 30);
            $password = (string) ($_POST['password'] ?? '');
            $confirm = (string) ($_POST['password_confirmation'] ?? '');
            if (text_length($name) < 2) throw new RuntimeException('Nama minimal 2 karakter.');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Format email tidak valid.');
            if (text_length($password) < 8) throw new RuntimeException('Password minimal 8 karakter.');
            if ($password !== $confirm) throw new RuntimeException('Konfirmasi password tidak sama.');
            $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, \'user\')');
            $stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
            $id = (int) $pdo->lastInsertId();
            $_SESSION['user'] = ['id' => $id, 'name' => $name, 'email' => $email, 'phone' => $phone, 'role' => 'user'];
            session_regenerate_id(true);
            flash('success', 'Akun berhasil dibuat. Selamat datang di CampRent!');
            redirect('dashboard');
        }

        case 'logout': {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
            }
            session_destroy();
            redirect('home');
        }

        case 'update_profile': {
            $user = require_login();
            $name = post_string('name', 120);
            $email = strtolower(post_string('email', 160));
            $phone = post_string('phone', 30);
            if (text_length($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Nama atau email tidak valid.');
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
            $stmt->execute([$name, $email, $phone, $user['id']]);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            flash('success', 'Profil berhasil diperbarui.');
            redirect('profile');
        }

        default:
            throw new RuntimeException('Aksi autentikasi tidak dikenal.');
    }
} catch (PDOException $exception) {
    $message = $exception->getCode() === '23000'
        ? 'Email sudah digunakan atau data memiliki relasi yang tidak dapat diproses.'
        : 'Terjadi kesalahan database. Periksa data dan coba kembali.';
    if (getenv('APP_DEBUG') === 'true') $message .= ' ' . $exception->getMessage();
    flash('error', $message);
    redirect(is_logged_in() ? 'profile' : 'login');
} catch (Throwable $exception) {
    flash('error', $exception->getMessage());
    redirect(is_logged_in() ? 'profile' : 'login');
}
