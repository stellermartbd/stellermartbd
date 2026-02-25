<?php
// admin/handlers/get-stats.php
require_once '../../core/db.php';
header('Content-Type: application/json');

$res = [
    'open_tickets' => $conn->query("SELECT id FROM support_tickets WHERE status = 'Open'")->num_rows,
    'refunds' => $conn->query("SELECT id FROM orders WHERE status = 'Refund_Pending'")->num_rows,
    'live_queries' => rand(5, 15) // Eikhane real chat logic thakle oita dibi
];
echo json_encode($res); // JSON data browser-e pathalo