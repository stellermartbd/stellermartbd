<?php
/**
 * Prime Beast - Marketing Intelligence Handler
 * Project: Turjo Site
 * Logic: Secure Offer Injection with CSRF & Logging
 */

// ১. এরর হ্যান্ডলিং ও সেশন চেক
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// ২. কোর ফাইল ইনক্লুড
require_once '../../core/db.php';
require_once '../../core/functions.php'; 
require_once '../../core/csrf.php'; // CSRF Validation logic thaka dorkar

// ৩. রিকোয়েস্ট চেক ও সিকিউরিটি ফিল্টার
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_offer'])) {
    
    // CSRF টোকেন ভ্যালিডেশন
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        header("Location: ../marketing-intel.php?error=Security+Token+Mismatch");
        exit();
    }

    // ডাটা স্যানিটাইজেশন ও টাইপ কাস্টিং
    $user_id     = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $coupon_id   = filter_input(INPUT_POST, 'coupon_id', FILTER_VALIDATE_INT);
    $admin_actor = $_SESSION['admin_username'] ?? 'Turjo_Admin'; // Turjo Site context

    // ভ্যালিডেশন চেক
    if (!$user_id || !$coupon_id) {
        header("Location: ../marketing-intel.php?error=Invalid+Selection+Detected");
        exit();
    }

    // ৪. ডুপ্লিকেট অফার চেক (Prepared Statement)
    $check_sql = "SELECT id FROM user_offers WHERE user_id = ? AND coupon_id = ? LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $coupon_id);
    $check_stmt->execute();
    $exists = $check_stmt->get_result();

    if ($exists->num_rows > 0) {
        // ডুপ্লিকেট চেষ্টার লগ
        if (function_exists('logActivity')) {
            logActivity($conn, 'MARKETING_ALERT', "Duplicate offer attempt for User ID: $user_id", 'info', $admin_actor);
        }
        header("Location: ../marketing-intel.php?error=Offer+Already+Assigned");
        exit();
    }
    $check_stmt->close();

    // ৫. অফার ইনজেকশন (Database Update)
    $sql = "INSERT INTO user_offers (user_id, coupon_id, assigned_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $coupon_id);

    if ($stmt->execute()) {
        // সাকসেস লগ
        if (function_exists('logActivity')) {
            logActivity($conn, 'MARKETING_SUCCESS', "Exclusive Coupon ID $coupon_id assigned to User $user_id", 'success', $admin_actor);
        }
        
        header("Location: ../marketing-intel.php?success=Exclusive+Offer+Injected+Successfully");
    } else {
        header("Location: ../marketing-intel.php?error=System+Execution+Fault");
    }
    
    $stmt->close();
    exit();

} else {
    // সরাসরি এক্সেস ব্লক
    header("Location: ../marketing-intel.php");
    exit();
}