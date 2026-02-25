<?php
// public/record-visit.php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/visit.php';

$input = file_get_contents('php://input');
if ($input) {
    $data = json_decode($input, true);
    $path = $data['path'] ?? '/';
    $ua = $data['ua'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = $data['device'] ?? (preg_match('/Mobi|Android/i', $ua) ? 'mobile' : 'desktop');
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    record_visit($path, $ua, $ip, $device);
}
http_response_code(204);