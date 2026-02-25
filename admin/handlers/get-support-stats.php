<?php
/**
 * Prime Beast - Live Support Stats Engine
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../core/db.php';

header('Content-Type: application/json');

$response = [
    'open' => 0,
    'refunds' => 0,
    'queries' => 0,
    'waiting' => 0
];

try {
    // ১. ওপেন টিকেট কাউন্ট
    $res1 = $conn->query("SELECT COUNT(id) as total FROM support_tickets WHERE status = 'Open'");
    if($res1) $response['open'] = $res1->fetch_assoc()['total'];

    // ২. রিফান্ড পেন্ডিং কাউন্ট
    $res2 = $conn->query("SELECT COUNT(id) as total FROM orders WHERE status = 'Refund_Pending'");
    if($res2) $response['refunds'] = $res2->fetch_assoc()['total'];

    // ৩. লাইভ কোয়েরি (ধরা যাক লাস্ট ৫ মিনিটে কয়জন একটিভ)
    $response['queries'] = rand(3, 8); // আপাতত ডামি, ডাটাবেস থাকলে কুয়েরি করবি

    // ৪. চ্যাট ওয়েটিং
    $response['waiting'] = rand(1, 5); 

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}