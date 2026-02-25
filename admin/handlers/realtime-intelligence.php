<?php
/**
 * Prime Beast - Real-time Intelligence Handler
 * Logic: Fetching live user metrics for AJAX polling
 */
header('Content-Type: application/json');
require_once '../../core/db.php';

// ১. লাইভ ভিজিটর কাউন্ট (বেসিক ইউজার কাউন্ট থেকে)
$active_users_query = "SELECT COUNT(*) as total FROM users";
$active_res = $conn->query($active_users_query);
$active_users = ($active_res) ? $active_res->fetch_assoc()['total'] : 0;

// ২. রিয়েল-টাইম অর্ডার কাউন্ট (আজকের টোটাল)
$orders_query = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()";
$orders_res = $conn->query($orders_query);
$total_orders = ($orders_res) ? $orders_res->fetch_assoc()['total'] : 0;

// ৩. রিসেন্ট থ্রেট অ্যালার্ট (Failed Logins/Suspicious Activity)
// এখানে তোর activity_logs টেবিল থেকে লেটেস্ট এরর ডাটা আনা হচ্ছে
$threat_query = "SELECT COUNT(*) as alerts FROM activity_logs WHERE action_status = 'failed' AND created_at > NOW() - INTERVAL 10 MINUTE";
$threat_res = $conn->query($threat_query);
$threat_alerts = ($threat_res) ? $threat_res->fetch_assoc()['alerts'] : 0;

// ৪. ডাটা প্যাক করে রেসপন্স পাঠানো
$response = [
    'status' => 'success',
    'active_users' => (int)$active_users,
    'total_orders' => (int)$total_orders,
    'threat_alerts' => (int)$threat_alerts,
    'timestamp' => date('H:i:s')
];

echo json_encode($response);
exit;