<?php
/**
 * Project: Turjo Site - Review Moderation System
 * Logic: Approve or Permanently Delete Reviews with Storage Cleanup [cite: 2026-02-21]
 */
require_once __DIR__ . '/../../core/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// অ্যাডমিন সিকিউরিটি চেক [cite: 2026-01-20]
if (!isset($_SESSION['admin_logged_in'])) { 
    header('Location: ../login.php'); 
    exit; 
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        // ১. রিভিউ অ্যাপ্রুভ করা [cite: 2026-02-21]
        $stmt = $conn->prepare("UPDATE product_reviews SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: ../reviews.php?status=approved");
        
    } elseif ($action === 'delete') {
        // ২. ডিলিট করার আগে ইমেজ ফাইল খুঁজে বের করা [cite: 2026-02-21]
        $img_stmt = $conn->prepare("SELECT review_image FROM product_reviews WHERE id = ?");
        $img_stmt->bind_param("i", $id);
        $img_stmt->execute();
        $res = $img_stmt->get_result()->fetch_assoc();

        if ($res && !empty($res['review_image'])) {
            $path = "../../public/uploads/reviews/" . $res['review_image'];
            if (file_exists($path)) { unlink($path); } // স্টোরেজ থেকে ডিলিট [cite: 2026-02-21]
        }

        // ডাটাবেস থেকে ডিলিট [cite: 2026-02-21]
        $del_stmt = $conn->prepare("DELETE FROM product_reviews WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        $del_stmt->execute();
        header("Location: ../reviews.php?status=deleted");
    }
    exit;
}

header("Location: ../reviews.php");
exit;