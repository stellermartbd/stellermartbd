<?php
/**
 * Prime Beast - Realtime Notification Hub
 */
require_once '../../core/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. Unread count fetch
$unread_res = $conn->query("SELECT COUNT(id) as total FROM notifications WHERE is_read = 0");
$unread_count = $unread_res->fetch_assoc()['total'];

// ২. Last 5 notifications for dropdown
$recent_res = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
$alerts = [];
while($row = $recent_res->fetch_assoc()) {
    $alerts[] = $row;
}

echo json_encode(['count' => $unread_count, 'alerts' => $alerts]);