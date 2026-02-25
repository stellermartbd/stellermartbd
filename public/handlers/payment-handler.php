<?php
/**
 * Project: Turjo Site - Secure Payment Intelligence (Updated)
 * Logic: Updates SINGLE order with TrxID and Method (One-to-Many System)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../core/db.php';

// ১. ভ্যালিডেশন চেক [cite: 2026-02-11]
if (isset($_POST['confirm_payment']) && isset($_SESSION['user_id'])) {
    
    $user_id  = $_SESSION['user_id'];
    $order_id = (int)$_POST['order_id']; 
    $method   = mysqli_real_escape_string($conn, $_POST['pay_method']);
    
    // ট্রানজেকশন আইডি চেক
    $trx_id   = isset($_POST['trx_id']) ? strtoupper(mysqli_real_escape_string($conn, $_POST['trx_id'])) : NULL;

    if (empty($trx_id)) {
        header("Location: ../payment.php?order_id=$order_id&error=TrxID_Required");
        exit();
    }

    // ২. পেমেন্ট স্ট্যাটাস নির্ধারণ [cite: 2026-02-11]
    $payment_status = ($method === 'Online') ? 'Paid_Full' : 'Advance_Shipping_Paid';

    /**
     * ৩. সিঙ্গেল অর্ডার আপডেট লজিক (New System)
     * এখন আর id >= $order_id লজিক লাগবে না, কারণ এক অর্ডারের ভেতরেই সব আইটেম আছে।
     */
    $sql = "UPDATE orders SET 
                payment_method = ?, 
                transaction_id = ?, 
                payment_status = ?, 
                status = 'Pending' 
            WHERE id = ? AND user_id = ?";
            
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: (s)method, (s)trx_id, (s)payment_status, (i)order_id, (i)user_id
    $stmt->bind_param("sssii", $method, $trx_id, $payment_status, $order_id, $user_id);

    if ($stmt->execute()) {
        // ৪. সফল হলে সাকসেস পেজে রিডাইরেক্ট [cite: 2026-02-11]
        header("Location: ../order-success.php?id=$order_id&status=Processing");
        exit();
    } else {
        echo "Intelligence Error: " . $stmt->error;
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>