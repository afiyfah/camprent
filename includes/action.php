
<?php
declare(strict_types=1);

require_post();

require_once __DIR__ . '/actions/content.php';

$action = post_string('action', 60);

try {
    handle_content_action($pdo, $action);

} catch (PDOException $exception) {
    error_log($exception->__toString());

    flash(
        'error',
        'Terjadi kesalahan database. Periksa tabel artikel dan coba kembali.'
    );

    redirect('manage-articles');

} catch (RuntimeException $exception) {
    flash('error', $exception->getMessage());

    redirect('manage-articles');

} catch (Throwable $exception) {
    error_log($exception->__toString());

    flash(
        'error',
        'Terjadi kesalahan sistem saat mengelola artikel.'
    );

    redirect('manage-articles');
}
