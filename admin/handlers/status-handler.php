<?php
/**
 * Project: Turjo Site - Admin Status Intelligence
 * File Path: htdocs/admin/handlers/status-handler.php
 * Logic: Auto-update Order Status to 'Completed' and Payment Status to 'Paid'
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. ফিক্সড কোর ফাইল কানেকশন
require_once '../../core/db.php'; 

// ১. GET Request Handle (কনফার্ম বাটনের জন্য)
if (isset($_GET['id']) && isset($_GET['status'])) {
    $order_id = (int)$_GET['id'];
    $incoming_status = mysqli_real_escape_string($conn, $_GET['status']);
    
    // যদি আপনি কনফার্ম বা Success বাটনে ক্লিক করেন
    if ($incoming_status == 'Success') {
        $order_update_status = 'Completed';
        $payment_update_status = 'Paid';
    } else {
        $order_update_status = $incoming_status;
        $payment_update_status = 'Unpaid'; // ক্যান্সেল করলে আনপেইড থাকবে
    }

    // ডাটাবেস আপডেট: একসাথে order_status এবং payment_status পরিবর্তন
    $sql = "UPDATE orders SET 
            order_status = '$order_update_status', 
            payment_status = '$payment_update_status' 
            WHERE id = $order_id";

    if ($conn->query($sql)) {
        // কাস্টমারের ডাটা আনা (WhatsApp নোটিফিকেশনের জন্য)
        $res = $conn->query("SELECT customer_name, customer_phone, total_price, shipping_cost, discount_amount FROM orders WHERE id = $order_id");
        
        if ($res && $res->num_rows > 0) {
            $order_data = $res->fetch_assoc();
            $name = $order_data['customer_name'];
            $phone = $order_data['customer_phone'];
            
            // সঠিক রেভিনিউ ক্যালকুলেশন
            $actual_total = ($order_data['total_price'] + $order_data['shipping_cost']) - $order_data['discount_amount'];
            $total = number_format($actual_total, 2); 

            $msg = ($order_update_status == 'Completed') 
                ? "Hello $name, Your Order #$order_id (Total: ৳$total) has been CONFIRMED & PAID! 🚀" 
                : "Hello $name, Your Order #$order_id status is: $order_update_status";

            $wa_link = "https://wa.me/88$phone?text=" . urlencode($msg);

            echo "<script>
                    alert('Order #$order_id is now $order_update_status and Payment is $payment_update_status!');
                    window.open('$wa_link', '_blank');
                    window.location.href = '../orders.php';
                  </script>";
            exit();
        }
    }
}
?>