<?php
/**
 * Project: Turjo Site - Secure Persistent Cart Logic (Fixed Price Update)
 * Logic: Fixed real-time price rendering and formatted response. [cite: 2026-02-11, 2026-02-21]
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// আউটপুট বাফারিং যাতে কোনো এক্সট্রা স্পেস এরর না দেয়
ob_start(); 
header('Content-Type: application/json');

// ১. সিকিউরিটি চেক: লগইন ছাড়া কার্টে এক্সেস নেই [cite: 2026-02-11]
if (!isset($_SESSION['user_id'])) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'অর্ডার করতে আগে আপনার অ্যাকাউন্টে লগইন করুন।'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? ''; 

    // --- ADD ACTION: ডাটাবেসে পণ্য যোগ করা [cite: 2026-02-11] ---
    if ($action == 'add') {
        $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
        $product_id = (int)$id;

        $check = $conn->query("SELECT id FROM cart WHERE user_id = $user_id AND product_id = $product_id");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE cart SET qty = qty + $qty WHERE user_id = $user_id AND product_id = $product_id");
        } else {
            $conn->query("INSERT INTO cart (user_id, product_id, qty) VALUES ($user_id, $product_id, $qty)");
        }
    }

    // --- REMOVE ACTION: ডাটাবেস থেকে পণ্য ডিলিট [cite: 2026-02-11] ---
    if ($action == 'remove') {
        $cart_id = (int)$id; 
        $conn->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    }

    // --- ডাটাবেস থেকে রিয়েল-টাইম ডাটা জেনারেট [cite: 2026-02-11] ---
    $cart_query = "SELECT c.id as cart_id, c.qty, p.name, p.price, p.image 
                   FROM cart c 
                   JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = $user_id";
    $result = $conn->query($cart_query);

    $total_items = 0; 
    $total_price = 0; 
    $cart_html = '';

    if ($result && $result->num_rows > 0) {
        while ($item = $result->fetch_assoc()) {
            $total_items += $item['qty'];
            $total_price += ($item['price'] * $item['qty']);
            
            $cart_html .= '
            <div class="flex gap-3 border-b pb-3 items-center" id="cart-row-'.$item['cart_id'].'">
                <img src="'.$base_url.'public/uploads/'.$item['image'].'" class="w-12 h-12 object-cover rounded border" alt="Product">
                <div class="flex-1 min-w-0">
                    <h4 class="text-[11px] font-bold text-gray-700 leading-tight uppercase truncate">'.$item['name'].'</h4>
                    <p class="text-[10px] text-gray-500 mt-1 font-bold uppercase">QTY: '.$item['qty'].'</p>
                    <p class="text-xs font-bold text-red-600">৳'.number_format($item['price'] * $item['qty']).'</p>
                </div>
                <button onclick="removeFromCart('.$item['cart_id'].')" class="w-8 h-8 flex-shrink-0 text-gray-300 hover:text-red-500 transition flex items-center justify-center">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>';
        }
    } else {
        $cart_html = '<div class="flex flex-col items-center justify-center h-full py-20 opacity-20"><i class="fas fa-shopping-basket text-5xl mb-2"></i><p class="text-xs font-bold uppercase">Cart is empty</p></div>';
    }

    if (ob_get_length()) ob_clean();
    
    // গুরুত্বপূর্ণ: total_price এ number_format বাদ দেওয়া হয়েছে যাতে NaN না আসে [cite: 2026-02-21]
    echo json_encode([
        'status' => 'success',
        'total_items' => (int)$total_items,
        'total_price' => (float)$total_price, 
        'cart_html' => $cart_html
    ]);
    exit;
}
?>