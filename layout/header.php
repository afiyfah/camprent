<?php
declare(strict_types=1);

$page = (string) ($_GET['page'] ?? 'home');
$user = current_user();

$pageTitles = [
    'home' => 'Beranda',
    'login' => 'Masuk',
    'register' => 'Daftar',
    'dashboard' => 'Dashboard',
    'profile' => 'Profil',
    'my-bookings' => 'Reservasi Saya',
    'staff-dashboard' => 'Dashboard Staff',
    'equipment' => 'Perlengkapan',
    'articles' => 'Artikel',
];

$title = $pageTitles[$page] ?? 'CampRent';
$flashMessage = $flashMessage ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | CampRent</title>

    <style>
        * { box-sizing: border-box; }

        :root {
            --green: #294b37;
            --green-dark: #1e392a;
            --cream: #f5f3ec;
            --text: #26352b;
            --muted: #788078;
            --border: #e5e7df;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fafaf7;
            color: var(--text);
            line-height: 1.6;
        }

        a { color: inherit; text-decoration: none; }

        .container {
            width: min(1160px, 92%);
            margin: auto;
        }

        .site-header {
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .nav-wrap {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 25px;
            font-weight: 800;
            color: var(--green);
        }

        .brand > span:last-child {
            line-height: 1.1;
        }

        .brand > span:last-child > span {
            color: #a18a5c;
        }

        .brand small {
            display: block;
            font-size: 9px;
            letter-spacing: 2px;
            font-weight: 600;
        }

        .brand-mark svg {
            width: 40px;
            height: 40px;
        }

        .main-nav, .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14px;
        }

        .main-nav a:hover,
        .text-link:hover {
            color: #a18a5c;
        }

        .nav-user { font-size: 13px; }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 7px;
            padding: 12px 20px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            color: white;
            background: var(--green);
        }

        .btn-primary:hover { background: var(--green-dark); }

        .btn-block { width: 100%; }

        .alert {
            margin: 18px auto;
            padding: 13px 17px;
            border-radius: 7px;
            font-size: 14px;
        }

        .alert-success { background: #e6f3e8; color: #245c31; }
        .alert-error { background: #fbe9e7; color: #9b3028; }
        .alert-info { background: #e8f0f7; color: #315875; }
        .alert-warning { background: #fff3d8; color: #785b18; }

        /* Authentication */

        .auth-section {
            padding: 55px 0;
            min-height: 75vh;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0,0,0,.06);
        }

        .auth-aside {
            background: var(--green);
            color: white;
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 570px;
        }

        .brand-light { color: white; }

        .auth-aside h2 {
            font-size: 34px;
            line-height: 1.25;
        }

        .auth-aside p { color: #d7dfd2; }

        .auth-aside blockquote {
            margin: 35px 0 0;
            font-style: italic;
            color: #d7dfd2;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 11px;
            font-weight: bold;
        }

        .auth-form { padding: 55px 45px; }

        .auth-form h1 { font-size: 29px; margin-bottom: 5px; }
        .auth-form > p, .small, .form-hint { color: var(--muted); font-size: 13px; }

        .form-group { margin: 20px 0; }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dfe3db;
            border-radius: 7px;
            background: white;
            font-size: 14px;
            color: var(--text);
        }

        .form-control:focus {
            outline: 2px solid #a7b9a8;
            border-color: var(--green);
        }

        .auth-bottom {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
        }

        .auth-bottom a, .text-link {
            color: var(--green);
            font-weight: bold;
        }

        .divider {
            border-top: 1px solid var(--border);
            margin: 25px 0;
        }

        .help-note {
            font-size: 12px;
            color: var(--muted);
            line-height: 2;
        }

        /* Dashboard */

        .dashboard-shell { padding: 45px 0; }

        .dashboard-heading { margin-bottom: 30px; }

        .dashboard-heading h1 {
            font-size: 32px;
            margin: 5px 0;
        }

        .dashboard-heading p { color: var(--muted); }

        .dashboard-layout {
            display: grid;
            grid-template-columns: 245px 1fr;
            gap: 25px;
            align-items: start;
        }

        .dashboard-sidebar,
        .form-card,
        .panel-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 22px;
        }

        .sidebar-user {
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 15px;
        }

        .sidebar-user strong { display: block; }
        .sidebar-user small { color: var(--muted); }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-menu a {
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 13px;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #edf2eb;
            color: var(--green);
            font-weight: bold;
        }

        .sidebar-logout {
            margin-top: 20px;
            border-top: 1px solid var(--border);
            padding-top: 15px;
        }

        .sidebar-logout button {
            border: 0;
            background: none;
            color: #a33c35;
            cursor: pointer;
            font-size: 13px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 18px;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .data-table-wrap { overflow-x: auto; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th,
        .data-table td {
            padding: 13px 10px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .data-table th { background: #f7f8f4; }

        .status {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 11px;
        }

        .status-success { background: #e2f3e5; color: #28623a; }
        .status-warning { background: #fff1d6; color: #85621c; }
        .status-danger { background: #fbe6e3; color: #a23b32; }
        .status-neutral { background: #edf0ed; color: #59645c; }

        .site-footer {
            padding: 25px 0;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            border-top: 1px solid var(--border);
            background: white;
        }

        @media(max-width: 760px) {
            .nav-wrap { flex-wrap: wrap; padding: 15px 0; }
            .main-nav { flex-wrap: wrap; gap: 12px; }
            .auth-grid { grid-template-columns: 1fr; }
            .auth-aside { min-height: 300px; padding: 30px; }
            .auth-form { padding: 30px 25px; }
            .dashboard-layout { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .panel-head { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>

<body>

<header class="site-header">
    <div class="container nav-wrap">

        <a class="brand" href="<?= e(url('home')) ?>">
            <span class="brand-mark">
                <svg viewBox="0 0 40 40">
                    <path d="M4 31 18.5 8 36 31H4Z"
                        fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="m13 31 7-11 7 11"
                        fill="none" stroke="currentColor"
                        stroke-width="2.5"/>
                </svg>
            </span>
            <span>camp<span>rent</span>
                <small>GEAR UP. GET OUT.</small>
            </span>
        </a>

        <nav class="main-nav">
            <a href="<?= e(url('home')) ?>">Beranda</a>

            <?php if (!$user): ?>
                <a href="<?= e(url('equipment')) ?>">Perlengkapan</a>
                <a href="<?= e(url('articles')) ?>">Artikel</a>
                <a href="<?= e(url('track')) ?>">Lacak Booking</a>
            <?php elseif (($user['role'] ?? '') === 'user'): ?>
                <a href="<?= e(url('dashboard')) ?>">Dashboard</a>
                <a href="<?= e(url('my-bookings')) ?>">Reservasi Saya</a>
                <a href="<?= e(url('profile')) ?>">Profil</a>
            <?php else: ?>
                <a href="<?= e(url('staff-dashboard')) ?>">Dashboard</a>
                <a href="<?= e(url('manage-equipment')) ?>">Perlengkapan</a>
                <a href="<?= e(url('reports')) ?>">Laporan</a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">
            <?php if (!$user): ?>
                <a href="<?= e(url('login')) ?>">Masuk</a>
                <a class="btn btn-primary" href="<?= e(url('register')) ?>">Daftar</a>
            <?php else: ?>
                <span class="nav-user">
                    Halo, <?= e($user['name']) ?>
                </span>
            <?php endif; ?>
        </div>

    </div>
</header>

<?php if (is_array($flashMessage)): ?>
    <div class="container">
        <div class="alert alert-<?= e($flashMessage['type'] ?? 'info') ?>">
            <?= e($flashMessage['message'] ?? '') ?>
        </div>
    </div>
<?php endif; ?>
