<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/actions/content.php';

$tests = [
    'slug converts spaces to hyphens' => camprent_content_slug('Tips Memilih Tenda') === 'tips-memilih-tenda',
    'slug transliterates Indonesian accents' => camprent_content_slug('Café di Puncak') === 'cafe-di-puncak',
    'slug has safe fallback for symbols only' => camprent_content_slug('🔥⛺') === 'artikel',
    'accepts empty optional cover URL' => camprent_content_is_http_url(''),
    'accepts HTTPS cover URL' => camprent_content_is_http_url('https://example.com/cover.jpg'),
    'accepts HTTP cover URL' => camprent_content_is_http_url('http://example.com/cover.jpg'),
    'rejects javascript URL' => !camprent_content_is_http_url('javascript:alert(1)'),
    'rejects malformed cover URL' => !camprent_content_is_http_url('not-a-url'),
];

$failed = 0;
foreach ($tests as $label => $passed) {
    echo ($passed ? 'PASS' : 'FAIL') . ': ' . $label . PHP_EOL;
    if (!$passed) $failed++;
}

echo sprintf("%d tests, %d failed.%s", count($tests), $failed, PHP_EOL);
exit($failed === 0 ? 0 : 1);
