<?php
/**
 * Project: Turjo Site - Gallery Image Handler
 * Features: Secure File Unlink & Database Deletion [cite: 2026-02-11]
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../core/db.php';
require_once '../../core/functions.php';

header('Content-Type: application/json');

// ১. সিকিউরিটি চেক: শুধু অ্যাডমিনই ডিলিট করতে পারবে [cite: 2026-02-11]
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

// ২. ডিলিট লজিক [cite: 2026-02-11]
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // আগে ছবির নাম ফেচ করা যাতে সার্ভার থেকে ডিলিট করা যায় [cite: 2026-02-11]
    $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $file_path = "../../public/uploads/" . $res['image_url'];

        // ৩. ডাটাবেস থেকে ডিলিট [cite: 2026-01-20]
        $del_stmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        
        if ($del_stmt->execute()) {
            // ৪. সার্ভার থেকে ফাইল মুছে ফেলা (যদি ফাইল থাকে) [cite: 2026-02-11]
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);