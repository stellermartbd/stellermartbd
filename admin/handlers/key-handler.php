<?php
session_start();
require_once '../../core/db.php';

if (isset($_POST['add_keys'])) {
    $product_id = (int)$_POST['product_id'];
    $keys_data = mysqli_real_escape_string($conn, $_POST['keys_data']);

    // ১. টেক্সট এরিয়া থেকে প্রতিটি লাইনকে আলাদা করা (Explode Logic)
    $keys_array = explode("\n", str_replace("\r", "", $_POST['keys_data']));
    
    $success_count = 0;
    $error_count = 0;

    foreach ($keys_array as $single_key) {
        $single_key = trim($single_key); // ফালতু স্পেস রিমুভ করা
        
        if (!empty($single_key)) {
            // ২. ডাটাবেসে ইনসার্ট করা
            $sql = "INSERT INTO product_keys (product_id, content, status) VALUES ('$product_id', '$single_key', 'Available')";
            
            if ($conn->query($sql)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }

    // ৩. রেজাল্ট মেসেজ সহ ফেরত পাঠানো
    if ($success_count > 0) {
        header("Location: ../manage-keys.php?success=$success_count keys added successfully! Errors: $error_count");
    } else {
        header("Location: ../manage-keys.php?error=Failed to add keys. DB Error: " . $conn->error);
    }
    exit;
} else {
    header("Location: ../manage-keys.php");
    exit;
}
?>