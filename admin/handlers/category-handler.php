<?php
/**
 * Prime Beast - Category Processor (V6.0)
 * Logic: Neural Taxonomy Integration
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../core/db.php'; 

// 🛡️ CSRF & Security Check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../add-category.php?error=invalid_request");
    exit();
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: ../add-category.php?error=security_breach");
    exit();
}

// 🧠 Processing Data
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $parent_id = (int)$_POST['parent_id'];
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Validation
    if (empty($name) || empty($slug)) {
        header("Location: ../add-category.php?error=empty_fields");
        exit();
    }

    // ডাটাবেসে ইনসার্ট কুয়েরি (নিশ্চিত করুন আপনার টেবিলে 'slug' এবং 'status' কলাম আছে)
    $sql = "INSERT INTO categories (name, parent_id, slug, status) 
            VALUES ('$name', $parent_id, '$slug', '$status')";

    if ($conn->query($sql)) {
        // সফল হলে মেসেজসহ ফেরত পাঠানো
        header("Location: ../categories.php?success=category_deployed");
    } else {
        // এরর হলে এরর কোডসহ ফেরত পাঠানো
        header("Location: ../add-category.php?error=database_fail&msg=" . urlencode($conn->error));
    }
    exit();
}