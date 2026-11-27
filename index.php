<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post_string('action', 60);
    $authActions = ['login', 'register', 'logout', 'update_profile'];
    if (in_array($action, $authActions, true)) {
        require __DIR__ . '/includes/actions/auth.php';
    }

    $dispatcher = __DIR__ . '/includes/actions.php';
    if (is_file($dispatcher)) require $dispatcher;

    http_response_code(501);
    exit('Handler fitur ini belum tersedia atau belum diintegrasikan.');
}

$page = (string) ($_GET['page'] ?? 'home');
$publicPages = ['home', 'equipment', 'equipment-detail', 'articles', 'article', 'track', 'login', 'register'];
$userPages = ['dashboard', 'profile', 'my-bookings', 'booking-detail', 'request-cancel'];
$staffPages = ['staff-dashboard', 'manage-equipment', 'manage-categories', 'manage-articles', 'reports'];
$adminPages = ['manage-users', 'audit-logs'];
$allPages = array_merge($publicPages, $userPages, $staffPages, $adminPages);
if (!in_array($page, $allPages, true)) $page = 'not-found';

if (in_array($page, $userPages, true)) require_login();
if (in_array($page, $staffPages, true)) require_role(['staff', 'admin']);
if (in_array($page, $adminPages, true)) require_role(['admin']);
if (in_array($page, ['login', 'register'], true) && is_logged_in()) redirect(current_user()['role'] === 'user' ? 'dashboard' : 'staff-dashboard');

$flashMessage = pull_flash();
require __DIR__ . '/layout/header.php';
$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (is_file($pageFile)) require $pageFile;
else require __DIR__ . '/pages/not-found.php';
require __DIR__ . '/layout/footer.php';
