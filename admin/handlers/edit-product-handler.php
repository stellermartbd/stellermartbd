<?php
session_start();
// ১. ডাটাবেস কানেকশন
require_once __DIR__ . '/../../core/db.php'; 

// Error reporting on kora jate 500 Error er bodole asol vul dekha jay
error_reporting(E_ALL);
ini_set('display_errors', 1);

// এডমিন লগইন চেক
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_POST['id'])) {
    // ২. ডাটা রিসিভ ও স্যানিটাইজ করা
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['title']); // Form field 'title' theke asche
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : 0;
    $stock = (int)$_POST['stock'];
    
    // 🔥 নতুন: HTML/CSS Prompt ডাটা রিসিভ করা
    $custom_style = mysqli_real_escape_string($conn, $_POST['custom_style']);

    // ৩. বর্তমান ইমেজ চেক করা (যদি নতুন ইমেজ না দেয়া হয়)
    $current_product = $conn->query("SELECT image FROM products WHERE id = $id")->fetch_assoc();
    $image_name = $current_product['image'];

    // নতুন ইমেজ আপলোড লজিক
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $new_image = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
        $target = "../../public/uploads/" . $new_image;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // পুরাতন ইমেজ ডিলিট করা (ঐচ্ছিক)
            if (file_exists("../../public/uploads/" . $image_name) && !empty($image_name)) {
                unlink("../../public/uploads/" . $image_name);
            }
            $image_name = $new_image;
        }
    }

    // ৪. ডাটাবেস আপডেট কুয়েরি
    // SQL fomatting fiks kora hoyeche jate single quote error na ashe
    $sql = "UPDATE products SET 
            name = '$name', 
            slug = '$slug', 
            description = '$description', 
            price = '$price', 
            discount_price = '$discount_price', 
            stock = '$stock', 
            image = '$image_name', 
            custom_style = '$custom_style' 
            WHERE id = $id";

    if ($conn->query($sql)) {
        // সফলভাবে আপডেট হলে রিডাইরেক্ট
        header("Location: ../products.php?success=Product updated successfully!");
        exit;
    } else {
        // যদি এরর হয় তবে তা প্রিন্ট করবে
        die("<h3>Database Update Failed!</h3>" . $conn->error);
    }
} else {
    header("Location: ../products.php");
    exit;
}
?>