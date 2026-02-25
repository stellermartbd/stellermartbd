<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'core/db.php';
$base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// হোম পেজ
echo '<url><loc>'.$base_url.'</loc><priority>1.0</priority></url>';

// প্রোডাক্ট পেজগুলো
$res = $conn->query("SELECT id FROM products WHERE status = 'Live'");
while($row = $res->fetch_assoc()) {
    echo '<url><loc>'.$base_url.'product-details.php?id='.$row['id'].'</loc><priority>0.8</priority></url>';
}

echo '</urlset>';
?>