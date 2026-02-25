<?php
/**
 * Prime Beast - Neural Real-time Intelligence Handler
 * Project: Turjo Site | Products Hub BD
 */
require_once __DIR__ . '/../../core/db.php'; 

// 🛡️ JSON Header
header('Content-Type: application/json');

$stats = [
    'live_products'    => 0,
    'total_orders'     => 0,
    'total_revenue'    => 0,
    'monthly_revenue'  => 0,
    'avg_order_value'  => 0,
    'conversion_rate'  => 12.5, // Logic onujayi dynamic kora jay
    'pending_orders'   => 0
];

/**
 * 1️⃣ Total Products Matrix
 */
$res_p = $conn->query("SELECT COUNT(*) as total FROM products WHERE status = 'Active'");
if($res_p) $stats['live_products'] = (int)$res_p->fetch_assoc()['total'];

/**
 * 2️⃣ Order Statistics (Total & Pending)
 */
$res_o = $conn->query("SELECT 
    COUNT(*) as total, 
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending 
    FROM orders");
if($res_o) {
    $row_o = $res_o->fetch_assoc();
    $stats['total_orders'] = (int)$row_o['total'];
    $stats['pending_orders'] = (int)$row_o['pending'];
}

/**
 * 3️⃣ Revenue Matrix (Lifetime & Current Month)
 * Note: Status 'Completed' ba 'Success' onujayi revenue check korbe.
 */
$res_r = $conn->query("SELECT 
    SUM(CASE WHEN status = 'Completed' THEN total_amount ELSE 0 END) as lifetime,
    SUM(CASE WHEN status = 'Completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) THEN total_amount ELSE 0 END) as monthly
    FROM orders");

if($res_r) {
    $row_r = $res_r->fetch_assoc();
    $stats['total_revenue'] = (float)($row_r['lifetime'] ?? 0);
    $stats['monthly_revenue'] = (float)($row_r['monthly'] ?? 0);
}

/**
 * 4️⃣ Intelligence Calculations
 */
// Calculate Average Order Value (Mean Matrix)
if($stats['total_orders'] > 0) {
    $stats['avg_order_value'] = round($stats['total_revenue'] / $stats['total_orders'], 2);
}

// Dynamic Conversion Node (Sample Logic: Orders / Visitors)
// Ekhane visitor track thakle dynamic kora jabe
$stats['conversion_rate'] = 12.5; 

echo json_encode($stats);
exit();