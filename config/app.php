<?php
declare(strict_types=1);

// Untuk instalasi di root domain, gunakan string kosong: ''.
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: '/camprent');

function app_url(string $path = ''): string
{
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return ($base === '' ? '' : $base) . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $path;
}
