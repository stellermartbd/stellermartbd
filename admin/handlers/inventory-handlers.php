<?php
session_start();
// ১. ডাটাবেস পাথ চেক (আপনার ফোল্ডার স্ট্রাকচার অনুযায়ী পাথ ঠিক করা হয়েছে)
require_once __DIR__ . '/../../core/db.php';

// এডমিন লগইন চেক (সিকিউরিটির জন্য)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    // ২. ইনপুট স্যানিটাইজ করা
    $product_id = intval($_POST['product_id']);
    $new_stock = $_POST['new_stock'];

    // ৩. ভ্যালিডেশন চেক
    if ($new_stock !== "") {
        $new_stock = intval($new_stock);
        
        // ৪. প্রিপেয়ার্ড স্টেটমেন্ট ব্যবহার করে সিকিউর আপডেট (SQL Injection থেকে বাঁচতে)
        $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_stock, $product_id);

        if ($stmt->execute()) {
            // ৫. সাকসেস মেসেজসহ রিডাইরেক্ট
            header("Location: ../inventory.php?success=Stock updated successfully!");
            exit;
        } else {
            header("Location: ../inventory.php?error=Database update failed!");
            exit;
        }
    } else {
        header("Location: ../inventory.php?error=Please enter a valid quantity!");
        exit;
    }
} else {
    // সরাসরি ফাইল এক্সেস করলে রিডাইরেক্ট
    header("Location: ../inventory.php");
    exit;
}
?>