<?php
// core/config.php
// Loads .env (simple) and provides APP_URL and asset() helper that works in subfolders.

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"");
        if (!getenv($key)) putenv("$key=$val");
    }
}

// If APP_URL present in .env use it; otherwise auto-detect from current request (works for XAMPP subfolder)
$rawAppUrl = getenv('APP_URL') ?: '';

if ($rawAppUrl) {
    $rawAppUrl = rtrim($rawAppUrl, '/');
    define('APP_URL', $rawAppUrl);
} else {
    // Auto-detect
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $dir = rtrim(dirname($script), '/\\');
    // If dir is just "/" make it empty for nicer URL
    $basePath = ($dir === '/' || $dir === '\\') ? '' : $dir;
    define('APP_URL', $scheme . '://' . $host . $basePath);
}

define('SITE_NAME', getenv('SITE_NAME') ?: 'My Shop');
define('STORAGE_PATH', realpath(__DIR__ . '/../storage') ?: __DIR__ . '/../storage');

/**
 * asset(path)
 * Return a full URL for assets and page links that works when project is in a subfolder.
 * Example: asset('assets/css/style.css') -> http://localhost/my-ecommerce-website/public/assets/css/style.css
 */
function asset($path = '') {
    $path = ltrim($path, '/');
    return rtrim(APP_URL, '/') . '/' . $path;
}