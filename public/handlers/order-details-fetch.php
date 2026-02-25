<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../core/db.php'; 

if (isset($_GET['order_id']) && isset($_SESSION['user_id'])) {
    $order_id = (int)$_GET['order_id'];
    $user_id = $_SESSION['user_id'];

    // মেইন অর্ডারের তথ্য [cite: 2026-01-20]
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        echo '<div class="space-y-6">';
        
        // শিপিং তথ্য [cite: 2026-02-11]
        echo '<div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">';
        echo '<h4 class="text-[10px] font-black text-slate-400 uppercase mb-3">Shipping To</h4>';
        echo '<p class="text-xs font-black text-slate-800 uppercase">' . htmlspecialchars($order['customer_name']) . '</p>';
        echo '<p class="text-[11px] text-slate-500 font-bold">' . htmlspecialchars($order['customer_phone']) . '</p>';
        echo '<p class="text-[11px] text-slate-500 font-bold mt-1">' . htmlspecialchars($order['delivery_address']) . ', ' . $order['district'] . '</p>';
        echo '</div>';

        // প্রোডাক্ট লিস্ট (order_items টেবিল থেকে) [cite: 2026-02-21]
        echo '<div class="space-y-3">';
        echo '<h4 class="text-[10px] font-black text-slate-400 uppercase">Products Ordered</h4>';
        
        $item_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $item_stmt->bind_param("i", $order_id);
        $item_stmt->execute();
        $items = $item_stmt->get_result();

        while($item = $items->fetch_assoc()) {
            echo '<div class="flex justify-between items-center p-4 bg-white border border-slate-100 rounded-2xl">';
            echo '<div>';
            echo '<p class="text-xs font-black text-slate-800 uppercase">' . htmlspecialchars($item['product_name']) . '</p>';
            echo '<p class="text-[10px] text-slate-400 font-bold uppercase">Qty: ' . $item['quantity'] . '</p>';
            echo '</div>';
            echo '<span class="text-xs font-black text-slate-900">৳' . number_format($item['price'] * $item['quantity']) . '</span>';
            echo '</div>';
        }
        echo '</div>';

        // টাকার হিসাব [cite: 2026-02-11]
        echo '<div class="pt-4 border-t border-dashed space-y-2">';
        echo '<div class="flex justify-between text-[11px] font-bold text-slate-400 uppercase"><span>Shipping</span><span>৳' . number_format($order['shipping_cost']) . '</span></div>';
        echo '<div class="flex justify-between text-[11px] font-bold text-slate-400 uppercase"><span>Discount</span><span>-৳' . number_format($order['discount_amount']) . '</span></div>';
        echo '<div class="flex justify-between text-sm font-black text-slate-900 uppercase pt-2"><span>Total Paid</span><span>৳' . number_format($order['total_amount']) . '</span></div>';
        echo '</div>';

        echo '</div>';
    } else {
        echo '<p class="text-center font-bold text-rose-500">Order Not Found!</p>';
    }
}
?>