<?php
/**
 * Project: Turjo Site - Order Processor (Final Fixed Version)
 * Logic: Supports both Cart Orders and Direct Buy Now Orders [cite: 2026-02-11, 2026-02-21]
 */
session_start();
require_once '../../core/db.php';

// ১. ভ্যালিডেশন: সাবমিট হয়েছে কিনা চেক [cite: 2026-02-11]
if (isset($_POST['place_order'])) {
    
    // ডাটা সোর্স ঠিক করা: কার্ট নাকি সরাসরি প্রোডাক্ট [cite: 2026-02-11]
    $order_items = [];
    
    if (isset($_POST['direct_product_id'])) {
        // সরাসরি একটি প্রোডাক্টের ক্ষেত্রে [cite: 2026-02-11]
        $p_id = (int)$_POST['direct_product_id'];
        $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
        $stmt->bind_param("i", $p_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        if ($res) {
            $order_items[] = [
                'name'  => $res['name'],
                'price' => $res['price'],
                'qty'   => 1
            ];
        }
    } elseif (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // কার্ট থেকে সব প্রোডাক্ট নেওয়া [cite: 2026-02-11, 2026-02-21]
        $order_items = $_SESSION['cart'];
    }

    // যদি কোনো আইটেম না পাওয়া যায় তবে ব্যাক করানো [cite: 2026-02-11]
    if (empty($order_items)) {
        header("Location: ../index.php");
        exit();
    }
    
    // ২. ইনপুট ডাটা নেওয়া ও সিকিউর করা [cite: 2026-02-21]
    $user_id  = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $name     = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $phone    = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $address  = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $division = mysqli_real_escape_string($conn, $_POST['division']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $note     = mysqli_real_escape_string($conn, $_POST['order_note']);

    // ৩. টাকার হিসাব [cite: 2026-02-11, 2026-02-21]
    $shipping = (float)$_POST['shipping_cost'];
    $discount = (float)$_POST['discount_amount'];
    $base_total = (float)$_POST['base_price'];
    $grand_total = ($base_total + $shipping) - $discount;

    // ৪. মেইন 'orders' টেবিলে ডাটা ইনসার্ট করা [cite: 2026-02-21]
    $sql_order = "INSERT INTO orders (
                    user_id, customer_name, customer_phone, delivery_address, 
                    division, district, total_amount, shipping_cost, 
                    discount_amount, order_note, status, order_date
                  ) VALUES (
                    '$user_id', '$name', '$phone', '$address', 
                    '$division', '$district', '$grand_total', '$shipping', 
                    '$discount', '$note', 'Pending', NOW()
                  )";

    if ($conn->query($sql_order)) {
        $new_order_id = $conn->insert_id;

        // ৫. সব প্রোডাক্ট 'order_items' টেবিলে লুপ করে ঢুকানো [cite: 2026-02-21]
        foreach ($order_items as $item) {
            $p_name = mysqli_real_escape_string($conn, $item['name']);
            $price  = $item['price'];
            $qty    = $item['qty'];

            $sql_item = "INSERT INTO order_items (order_id, product_name, price, quantity) 
                         VALUES ('$new_order_id', '$p_name', '$price', '$qty')";
            
            if(!$conn->query($sql_item)){
                die("Item Insert Error: " . $conn->error);
            }
        }

        // ৬. অর্ডার সফল! কার্ট খালি করা [cite: 2026-02-21]
        if (!isset($_POST['direct_product_id'])) {
            unset($_SESSION['cart']);
        }
        
        header("Location: ../payment.php?order_id=$new_order_id");
        exit();

    } else {
        die("Order Insert Error: " . $conn->error);
    }

} else {
    header("Location: ../index.php");
    exit();
}
?>