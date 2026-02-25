<?php
require_once '../core/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    // Image Upload Logic (Payment Screenshot)
    $screenshot = $_FILES['payment_ss']['name'];
    $temp_name = $_FILES['payment_ss']['tmp_name'];
    $target = "uploads/" . time() . "_" . basename($screenshot); // Duplicate name thika bachanur jonno time() jog kora hoyeche

    if (move_uploaded_file($temp_name, $target)) {
        $ss_name = basename($target);
        
        // Orders table-e data insert kora
        $sql = "INSERT INTO orders (product_id, customer_name, customer_phone, payment_screenshot, status) 
                VALUES ('$product_id', '$name', '$phone', '$ss_name', 'Pending')";
        
        if ($conn->query($sql)) {
            echo "<script>
                alert('Order Placed Successfully! We will contact you on WhatsApp.');
                window.location.href = 'index.php';
            </script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Failed to upload screenshot.";
    }
}
?>