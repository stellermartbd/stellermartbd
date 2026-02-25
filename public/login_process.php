<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. ডাটাবেস কানেকশন (htdocs/core/db.php)
require_once __DIR__ . '/../core/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ইনপুট সুরক্ষা
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // ২. ডাটাবেস থেকে ইউজার চেক
    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // ৩. পাসওয়ার্ড ভেরিফিকেশন (সবচেয়ে গুরুত্বপূর্ণ অংশ)
        // রেজিস্ট্রেশনে password_hash ব্যবহার করায় এখানে password_verify বাধ্যতামূলক
        if (password_verify($password, $user['password'])) { 
            
            // সেশনে ইউজারের তথ্য সেভ করা
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['image'] = $user['image']; // প্রোফাইল ইমেজের জন্য

            // ৪. রিডাইরেকশন (লগইন সফল হলে প্রোফাইল পেজে নিয়ে যাবে)
            header("Location: profile.php"); 
            exit;
        } else {
            // ভুল পাসওয়ার্ড হলে
            header("Location: login.php?error=wrong_password");
            exit;
        }
    } else {
        // ইউজার না পাওয়া গেলে
        header("Location: login.php?error=user_not_found");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>