<?php
/**
 * Prime Admin - Anti-Flash Cinematic Header
 * Project: Turjo Site | Logic: Session-Based 2s Fade-In & 15-Min Auto Logout
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php';

// ১. সিকিউরিটি চেক
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 🔥 ২. ১৫ মিনিট অটো লগআউট লজিক (৯০০ সেকেন্ড)
$timeout_duration = 900; 
if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    if ($elapsed_time >= $timeout_duration) {
        // সেশন শেষ, বের করে দাও
        session_unset();
        session_destroy();
        header("Location: login.php?reason=timeout");
        exit;
    }
}
// প্রতিবার পেজ লোড হলে অ্যাক্টিভিটি টাইম আপডেট হবে
$_SESSION['last_activity'] = time();

// গ্লোবাল ডাটা
$low_stock_check = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 5");
$low_stock_count = $low_stock_check ? $low_stock_check->fetch_assoc()['total'] : 0;

/**
 * 🔥 ইন্টেলিজেন্ট এনিমেশন লজিক
 */
$is_first_entry = false;
if (isset($_SESSION['show_entrance_anim'])) {
    $is_first_entry = true;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark" style="background-color: #0d0915;"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Prime Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', 
            theme: {
                extend: {
                    colors: {
                        theme: { dark: '#0d0915', card: '#161021', border: '#251d33' }
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }

        <?php if($is_first_entry): ?>
        #beast-global-mask {
            position: fixed; inset: 0; z-index: 99999;
            background: #0d0915; opacity: 1;
            transition: opacity 1.5s ease-in-out, visibility 1.5s;
        }
        body:not(.is-ready) { overflow: hidden; }
        body.is-ready #beast-global-mask { opacity: 0; visibility: hidden; }
        <?php endif; ?>
        
        .sidebar { background: #0d0915; color: white; border-right: 1px solid #251d33; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-link.active { 
            background: linear-gradient(90deg, #e11d48 0%, #be123c 100%); 
            color: white !important;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
        }
        .nav-link:hover:not(.active) { background: rgba(255,255,255,0.05); color: #fff; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
        main { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark text-gray-800 dark:text-gray-200">

    <?php if($is_first_entry): ?>
    <div id="beast-global-mask"></div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.add('is-ready');
            }, 100); 
        });
    </script>
    <?php endif; ?>