<?php
/**
 * Prime Beast - Live Data Stream Engine
 */
header('Content-Type: application/json');
require_once '../../core/db.php';

// Real-time counts fetching
$open_tickets = $conn->query("SELECT COUNT(id) as total FROM support_tickets WHERE status = 'Open'")->fetch_assoc()['total'];
$refund_reqs = $conn->query("SELECT COUNT(id) as total FROM orders WHERE status = 'Refund_Pending'")->fetch_assoc()['total'];

// System Hardware Stats (Simulated Real-time)
$cpu_load = rand(20, 45); 
$ram_usage = rand(35, 55);
$db_latency = rand(5, 25);

echo json_encode([
    'tickets' => $open_tickets,
    'refunds' => $refund_reqs,
    'cpu' => $cpu_load,
    'ram' => $ram_usage,
    'latency' => $db_latency
]);