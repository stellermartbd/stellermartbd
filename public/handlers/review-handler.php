<?php
// ১. এরর ট্র্যাকিং অন করা
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/../../core/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = intval($_POST['product_id']);
        
        // ফিক্স: যদি সেশনে নাম না থাকে তবে 'Guest User' হিসেবে সেভ হবে [cite: 2026-02-21]
        $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest User';
        
        $rating = intval($_POST['rating']);
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        $review_image = null;

        // ২. ইমেজ আপলোড লজিক
        if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] === 0) {
            $upload_dir = __DIR__ . "/../uploads/reviews/";
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $file_ext = strtolower(pathinfo($_FILES["review_image"]["name"], PATHINFO_EXTENSION));
            $new_file_name = time() . '_rev_' . uniqid() . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES["review_image"]["tmp_name"], $upload_dir . $new_file_name)) {
                $review_image = $new_file_name;
            }
        }

        // ৩. ডাটাবেসে সেভ করা [cite: 2026-02-21]
        $sql = "INSERT INTO product_reviews (product_id, user_name, rating, comment, review_image, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiss", $product_id, $user_name, $rating, $comment, $review_image);
        
        if ($stmt->execute()) {
            header("Location: ../../product-details.php?id=$product_id&status=review_success");
            exit;
        }
    } catch (Exception $e) {
        die("Error detail: " . $e->getMessage());
    }
}