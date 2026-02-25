<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// ডাটাবেস কানেকশন প্রয়োজন হতে পারে যদি আইডি দিয়ে ডাটা ফেচ করতে হয়
require_once 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $msg = "";

    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $id = $_POST['id'];
        $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;

        // প্রোডাক্টের তথ্য ডাটাবেস থেকে নেওয়া (নিরাপত্তার জন্য)
        $stmt = $conn->prepare("SELECT name, price, image FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if($product = $res->fetch_assoc()) {
            $name = $product['name'];
            $price = $product['price'];
            $image = $product['image'];

            $found = false;
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $id) {
                    $_SESSION['cart'][$key]['qty'] += $qty;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $id,
                    'name' => $name,
                    'price' => $price,
                    'image' => $image,
                    'qty' => $qty
                ];
            }
            $msg = 'Product added to cart!';
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == 'remove') {
        $id = $_POST['id'];
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $id) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        $msg = 'Item removed';
    }

    $total_items = 0;
    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_items += $item['qty'];
        $total_price += ($item['price'] * $item['qty']);
    }

    // HTML জেনারেট করা
    $cart_html = '';
    if (count($_SESSION['cart']) > 0) {
        foreach ($_SESSION['cart'] as $item) {
            $item_total = $item['price'] * $item['qty'];
            $cart_html .= '<div class="flex items-center justify-between p-3 border-b border-gray-100">';
            $cart_html .= '<div class="flex items-center gap-3">';
            $cart_html .= '<img src="public/uploads/'.$item['image'].'" class="w-12 h-12 object-contain">';
            $cart_html .= '<div><h4 class="text-xs font-bold truncate w-32">'.$item['name'].'</h4>';
            $cart_html .= '<p class="text-[10px] text-gray-400">'.$item['qty'].' x ৳'.number_format($item['price']).'</p></div></div>';
            $cart_html .= '<button onclick="removeFromCart('.$item['id'].')" class="text-red-500"><i class="fas fa-trash-alt"></i></button></div>';
        }
    } else {
        $cart_html = '<p class="text-center py-10 text-gray-400">Cart is empty</p>';
    }

    echo json_encode([
        'status' => 'success',
        'message' => $msg,
        'total_items' => (int)$total_items,
        'total_price' => (float)$total_price, // float নিশ্চিত করবে যে এটি নম্বর হিসেবে যাবে
        'cart_html' => $cart_html
    ]);
    exit;
}