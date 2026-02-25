<?php
/**
 * Prime Admin - Enterprise Coupon Handler (V40.0)
 * Logic: Targeting, Usage Restrictions, & Security
 * Project: Turjo Site | Products Hub BD
 */
require_once '../../core/db.php';
require_once '../../core/functions.php';
session_start();

header('Content-Type: application/json');

/**
 * 1️⃣ CSRF & Security Shield
 */
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['status' => 'error', 'message' => 'Security Protocol: Invalid CSRF Token!']));
}

$action = $_POST['action'] ?? '';
$admin_id = $_SESSION['admin_id'] ?? 0;

switch($action) {
    
    // 🎟️ 2️⃣ Create Coupon (Advanced System)
    case 'create_coupon':
        if(!hasPermission($conn, 'coupon.manage')) {
            die(json_encode(['status' => 'error', 'message' => 'Access Denied: Permission Missing.']));
        }

        $code = mysqli_real_escape_string($conn, strtoupper($_POST['code']));
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $value = floatval($_POST['value']);
        $expiry = mysqli_real_escape_string($conn, $_POST['expiry']);

        /**
         * ✅ Enterprise Logic: Add Hidden Defaults for Pro Control
         */
        $min_order = $_POST['min_order'] ?? 0;
        $usage_limit = $_POST['usage_limit'] ?? 0; // 0 = Unlimited
        $target_type = $_POST['target_type'] ?? 'all';

        // Check if code exists
        $check = $conn->query("SELECT id FROM coupons WHERE code = '$code'");
        if($check->num_rows > 0) {
            die(json_encode(['status' => 'error', 'message' => 'Coupon Code Already Exists!']));
        }

        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, end_date, min_order_amount, total_usage_limit, target_type, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsdiss", $code, $type, $value, $expiry, $min_order, $usage_limit, $target_type, $admin_id);

        if($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Coupon Matrix Deployed Successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database Sync Failed.']);
        }
        break;

    // 🗑️ 5️⃣ Delete / Terminate Coupon Node
    case 'delete_coupon':
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Coupon Node Terminated.']);
        }
        break;

    // 🔄 Bulk Status Update (Enable/Disable)
    case 'toggle_status':
        $id = intval($_POST['id']);
        $new_status = mysqli_real_escape_string($conn, $_POST['status']);
        $conn->query("UPDATE coupons SET status = '$new_status' WHERE id = $id");
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown Protocol.']);
}