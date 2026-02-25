<?php
/**
 * Prime Beast - Secured Product Handler (Fixed Version)
 * Logic: Product Entry + Multi-Image Gallery Integration [cite: 2026-02-11]
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../core/db.php';
require_once '../../core/functions.php'; 

// --- ১. প্রোডাক্ট ডিলিট করার লজিক (Security Locked) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!hasPermission($conn, 'product_manage', 'delete')) {
        logActivity($conn, 'UNAUTHORIZED_DELETE', "Blocked delete attempt on Product ID: " . $_GET['id'], 'danger');
        header("Location: ../products.php?error=Access+Denied");
        exit;
    }

    $id = (int)$_GET['id'];
    $sql = "DELETE FROM products WHERE id = $id";

    if ($conn->query($sql)) {
        header("Location: ../products.php?success=Product deleted!");
        exit;
    } else {
        header("Location: ../products.php?error=Failed to delete product!");
        exit;
    }
}

// --- ২. প্রোডাক্ট যোগ করার লজিক ---
if (isset($_POST['add_product'])) {

    if (!hasPermission($conn, 'product_manage', 'add')) {
        header("Location: ../products.php?error=Access+Denied");
        exit;
    }

    $category_id = (int)$_POST['category_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? "'".mysqli_real_escape_string($conn, $_POST['discount_price'])."'" : "NULL";
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = (int)$_POST['stock'];
    $is_digital = isset($_POST['is_digital']) ? (int)$_POST['is_digital'] : 0;
    $status = !empty($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'Live';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    // মেইন ইমেজ আপলোড পাথ [cite: 2026-02-11]
    $upload_dir = "../../public/uploads/";

    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        $target = $upload_dir . basename($image_name);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            
            // ৩. মূল প্রোডাক্ট ডাটাবেসে ইনসার্ট [cite: 2026-01-20]
            $sql = "INSERT INTO products (category_id, name, slug, sku, price, discount_price, description, stock, image, status, is_digital) 
                    VALUES ('$category_id', '$name', '$slug', '$sku', '$price', $discount_price, '$description', '$stock', '$image_name', '$status', '$is_digital')";
            
            if ($conn->query($sql)) {
                $product_id = $conn->insert_id; // নতুন তৈরি হওয়া প্রোডাক্টের আইডি [cite: 2026-01-20]

                // 🔥 ৪. গ্যালারি ইমেজ আপলোড লজিক (নতুন যোগ করা হয়েছে) [cite: 2026-02-11]
                if (!empty($_FILES['gallery_images']['name'][0])) {
                    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['gallery_images']['error'][$key] === 0) {
                            $g_image_name = time() . '_gal_' . $_FILES['gallery_images']['name'][$key];
                            $g_target = $upload_dir . $g_image_name;

                            if (move_uploaded_file($tmp_name, $g_target)) {
                                // গ্যালারি টেবিলে ডাটা সেভ [cite: 2026-01-20]
                                $conn->query("INSERT INTO product_images (product_id, image_url) VALUES ('$product_id', '$g_image_name')");
                            }
                        }
                    }
                }

                header("Location: ../products.php?success=Product and Gallery added successfully!");
                exit;
            } else {
                header("Location: ../add-product.php?error=DB Error: " . $conn->error);
                exit;
            }
        } else {
            header("Location: ../add-product.php?error=Main image upload failed!");
            exit;
        }
    } else {
        header("Location: ../add-product.php?error=Main image is required!");
        exit;
    }
}
?>