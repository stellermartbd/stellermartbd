<?php
// ১. সেশন এবং ডাটাবেস কানেকশন শুরু
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../core/db.php'; 

// ২. লগইন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ৩. ফর্ম সাবমিট হয়েছে কি না চেক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ইনপুট ক্লিন করা (Security)
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $update_success = false;

    // ৪. প্রোফাইল পিকচার হ্যান্ডলিং
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
        // ইমেজ সেভ করার ফোল্ডার (htdocs/uploads/)
        $target_dir = "../uploads/"; 
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = $_FILES['profile_img']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        // ফরম্যাট চেক করে ইউনিক ফাইল নাম তৈরি করা
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = "profile_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $target_file)) {
                // ডাটাবেসে ছবি আপডেট করা
                $img_stmt = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
                $img_stmt->bind_param("si", $new_file_name, $user_id);
                $img_stmt->execute();
                $update_success = true;
            }
        }
    }

    // ৫. নাম এবং ইমেইল আপডেট (Prepared Statement ব্যবহার করে)
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    
    if ($stmt->execute()) {
        $update_success = true;
    }

    // ৬. সাকসেস মেসেজ সেট করা এবং প্রোফাইল পেজে পাঠানো
    if ($update_success) {
        $_SESSION['success'] = "Profile details updated successfully!";
    }

    header("Location: profile.php");
    exit;
} else {
    header("Location: profile.php");
    exit;
}