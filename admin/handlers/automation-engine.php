<?php
/**
 * Prime Beast - Order Automation Logic
 * Logic: Auto-Fetch Key -> Deliver to User -> Update Order Status
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. ফাইল পাথ ভেরিফিকেশন
require_once '../../core/db.php';
require_once '../../core/functions.php';

/**
 * 🚀 অটোমেশন ট্রিগার চেক
 * এই হ্যান্ডলারটি admin/order-automation.php থেকে কল হবে
 */
if (isset($_POST['trigger_automation']) || isset($_GET['cron'])) {

    // ২. পেইড কিন্তু ডেলিভারি বাকি এমন অর্ডারগুলো খোঁজা
    $query = "SELECT o.id, o.user_id, o.product_id, p.name as product_name 
              FROM orders o 
              JOIN products p ON o.product_id = p.id 
              WHERE o.status = 'Paid' 
              AND o.delivery_status = 'Pending' 
              AND p.is_digital = 1";
    
    $pending_orders = $conn->query($query);

    if ($pending_orders->num_rows > 0) {
        $processed = 0;

        while ($order = $pending_orders->fetch_assoc()) {
            $order_id = $order['id'];
            $product_id = $order['product_id'];

            // ৩. ডিজিটাল ওয়্যারহাউস থেকে এভেইলএবল কি (Key) খোঁজা
            $key_query = "SELECT id, content FROM product_keys 
                          WHERE product_id = '$product_id' 
                          AND status = 'Available' 
                          LIMIT 1";
            $key_result = $conn->query($key_query);

            if ($key_result->num_rows > 0) {
                $key_data = $key_result->fetch_assoc();
                $key_id = $key_data['id'];
                $delivery_content = $key_data['content'];

                // ৪. ট্রানজেকশন শুরু (Atomic Update)
                $conn->begin_transaction();

                try {
                    // ক. অর্ডার আপডেট (ডেলিভারি কনটেন্ট সহ)
                    $update_order = $conn->prepare("UPDATE orders SET delivery_status = 'Delivered', status = 'Completed', delivery_details = ? WHERE id = ?");
                    $update_order->bind_param("si", $delivery_content, $order_id);
                    $update_order->execute();

                    // খ. কি (Key) স্ট্যাটাস 'Sold' করা
                    $update_key = $conn->query("UPDATE product_keys SET status = 'Sold' WHERE id = '$key_id'");

                    // গ. অ্যাক্টিভিটি লগ জেনারেট করা
                    logActivity($conn, 'AUTO_DELIVERY', "Delivered key for Order #$order_id (Product ID: $product_id)", 'success', 'System_Bot');

                    $conn->commit();
                    $processed++;

                } catch (Exception $e) {
                    $conn->rollback();
                    logActivity($conn, 'AUTO_ERR', "Failed to process Order #$order_id: " . $e->getMessage(), 'danger', 'System_Bot');
                }
            } else {
                // স্টক আউট হলে লগ করা
                logActivity($conn, 'STOCK_OUT', "Order #$order_id failed: No keys available for Product #$product_id", 'danger', 'System_Bot');
            }
        }

        header("Location: ../order-automation.php?success=$processed+Orders+Processed+Successfully");
    } else {
        header("Location: ../order-automation.php?info=No+Pending+Paid+Orders+Found");
    }
    exit();
} else {
    header("Location: ../order-automation.php");
    exit();
}