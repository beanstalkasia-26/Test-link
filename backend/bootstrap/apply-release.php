<?php

use Illuminate\Contracts\Console\Kernel;

$base = dirname(__DIR__);
$release = trim((string) file_get_contents($base.'/release.txt'));
$marker = $base.'/storage/app/deployed-release';

if (! is_file($base.'/.env') && is_file($base.'/.env.live')) {
    copy($base.'/.env.live', $base.'/.env');
}

foreach (['storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $dir) {
    if (! is_dir($base.'/'.$dir)) {
        mkdir($base.'/'.$dir, 0775, true);
    }
}

$updatingPage = static function (int $status, string $message): never {
    http_response_code($status);
    header('Retry-After: 10');
    header('Cache-Control: no-store');
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">',
    $status === 503 ? '<meta http-equiv="refresh" content="10">' : '',
    '<title>NAMTECH</title><body style="font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:90vh;color:#2b1a5a">',
    '<p style="max-width:32rem;text-align:center">', htmlspecialchars($message), '</p>';
    exit;
};

$lock = fopen($base.'/storage/app/deploy.lock', 'c');
if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
    $updatingPage(503, 'The website is being updated. This page reloads by itself in a few seconds.');
}

if (@file_get_contents($marker) !== $release) {
    ignore_user_abort(true);
    set_time_limit(0);
    $log = static fn (string $line) => file_put_contents($base.'/storage/logs/deploy.log', '['.date('Y-m-d H:i:s').'] '.$line.PHP_EOL, FILE_APPEND);

    try {
        $log("Applying release {$release}");
        foreach (glob($base.'/bootstrap/cache/*.php') ?: [] as $cached) {
            unlink($cached);
        }

        require $base.'/vendor/autoload.php';
        $app = require $base.'/bootstrap/app.php';
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();

        $sql = dirname($base).'/database/namtech.sql';
        if (is_file($sql)) {
            $pdo = $app['db']->connection()->getPdo();
            $statement = '';
            $count = 0;
            foreach (new SplFileObject($sql) as $line) {
                if ($statement === '' && (trim($line) === '' || str_starts_with($line, '--'))) {
                    continue;
                }
                $statement .= $line;
                if (str_ends_with(rtrim($line), ';')) {
                    $pdo->exec($statement);
                    $statement = '';
                    $count++;
                }
            }
            $log("Database loaded ({$count} statements)");
        }

        $kernel->call('migrate', ['--force' => true]);
        if (! file_exists($base.'/public/storage')) {
            $kernel->call('storage:link');
        }
        $kernel->call('optimize:clear');
        $kernel->call('optimize');

        file_put_contents($marker, $release);
        $log('Release applied');
    } catch (Throwable $e) {
        $log('FAILED: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
        flock($lock, LOCK_UN);
        $settings = is_file($base.'/.env') ? (string) file_get_contents($base.'/.env') : '';
        $updatingPage(500, preg_match('/^DB_DATABASE=fill_in|^DB_PASSWORD=\s*$/m', $settings)
            ? 'Almost there: in cPanel File Manager, open public_html/backend/.env, fill in APP_URL, DB_DATABASE, DB_USERNAME and DB_PASSWORD (cPanel → MySQL Databases), save, and reload this page.'
            : 'The update could not be applied. Details are in backend/storage/logs/deploy.log. Fix the cause (often the database details in backend/.env) and reload this page.');
    }
}

flock($lock, LOCK_UN);

header('Location: '.($_SERVER['REQUEST_URI'] ?? '/'), true, 302);
header('Cache-Control: no-store');
exit;
