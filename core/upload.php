<?php
// core/upload.php - safe upload helper
function safe_filename($name) {
    $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '-', $name);
    $name = preg_replace('/-+/', '-', $name);
    return strtolower(trim($name, '-'));
}

function handle_image_upload($fileInputName, $targetDir = __DIR__ . '/../public/uploads') {
    if (empty($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$fileInputName];

    // validate size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) return null;

    // validate mime & extension
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) return null;

    $ext = $allowed[$mime];
    $orig = pathinfo($file['name'], PATHINFO_FILENAME);
    $safe = safe_filename($orig);
    $filename = $safe . '-' . bin2hex(random_bytes(6)) . '.' . $ext;

    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }
    return null;
}