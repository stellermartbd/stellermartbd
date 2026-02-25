<?php
/**
 * Prime Admin - Enterprise Bulk Matrix Handler (V36.0)
 * Logic: Preview -> Verify -> Transactional Execution
 * Features: CSRF, Rate Limiting, Audit Logging, ACL, & Password Verification
 */
require_once '../../core/db.php';
require_once '../../core/functions.php';
session_start();

// ✅ Safe JSON Response Header
header('Content-Type: application/json');

/**
 * 1️⃣ CSRF Validation (Strict Security)
 */
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['status' => 'error', 'message' => 'Security Breach: Invalid CSRF Token!']));
}

/**
 * 2️⃣ Enterprise Rate Limiting (Throttle Protocol)
 */
$cooldown = 5; // Seconds
if (isset($_SESSION['last_bulk_time']) && (time() - $_SESSION['last_bulk_time'] < $cooldown)) {
    die(json_encode(['status' => 'error', 'message' => "Too many requests. Please wait {$cooldown} seconds."]));
}
$_SESSION['last_bulk_time'] = time();

$action = $_POST['action'] ?? '';
$preview = isset($_POST['preview']) && $_POST['preview'] === 'true';
$admin_id = $_SESSION['admin_id'] ?? 0;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Function to log actions into audit trail
function logBulkAction($conn, $admin_id, $action, $count, $status, $ip) {
    $stmt = $conn->prepare("INSERT INTO bulk_logs (admin_id, action_type, affected_count, status, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $admin_id, $action, $count, $status, $ip);
    return $stmt->execute();
}

switch($action) {
    
    // 📦 PRODUCT ACTIONS: Price Matrix Update
    case 'bulk_price_edit':
        if(!hasPermission($conn, 'bulk.product')) {
            die(json_encode(['status' => 'error', 'message' => 'Access Denied: bulk.product permission required.']));
        }

        if($preview) {
            $res = $conn->query("SELECT COUNT(*) as total FROM products WHERE status = 'Active'");
            $count = ($res && $row = $res->fetch_assoc()) ? $row['total'] : 0;
            echo json_encode(['status' => 'preview', 'count' => $count]);
            exit;
        }

        // 🛡️ Transactional Execution
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE products SET price = price * 1.10 WHERE status = 'Active'");
            $affected = $conn->affected_rows;
            
            logBulkAction($conn, $admin_id, $action, $affected, 'success', $ip_address);
            
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => "Matrix Updated: $affected products synchronized."]);
        } catch (Exception $e) {
            $conn->rollback();
            logBulkAction($conn, $admin_id, $action, 0, 'failed', $ip_address);
            echo json_encode(['status' => 'error', 'message' => 'Transaction Failed: ' . $e->getMessage()]);
        }
        break;

    // 👥 IDENTITY ACTIONS: Bulk Block
    case 'bulk_block_all':
        if(!hasPermission($conn, 'bulk.customer')) {
            die(json_encode(['status' => 'error', 'message' => 'Access Denied: bulk.customer permission required.']));
        }

        if($preview) {
            $res = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'Active' AND id != 1");
            $count = ($res && $row = $res->fetch_assoc()) ? $row['total'] : 0;
            echo json_encode(['status' => 'preview', 'count' => $count]);
            exit;
        }

        $conn->begin_transaction();
        try {
            $conn->query("UPDATE users SET status = 'Blocked' WHERE status = 'Active' AND id != 1");
            $affected = $conn->affected_rows;
            
            logBulkAction($conn, $admin_id, $action, $affected, 'success', $ip_address);
            
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => "$affected identities locked down."]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Operation Failed.']);
        }
        break;

    // 🔐 SECURITY ACTIONS: Force Logout (Requires Password)
    case 'force_logout_all':
        if(!hasPermission($conn, 'bulk.security')) {
            die(json_encode(['status' => 'error', 'message' => 'Access Denied: bulk.security permission required.']));
        }

        // Password Verification
        $admin_pass = $_POST['admin_password'] ?? '';
        $res = $conn->query("SELECT password FROM admins WHERE id = " . intval($admin_id));
        $admin_data = ($res) ? $res->fetch_assoc() : null;

        if (!$admin_data || !password_verify($admin_pass, $admin_data['password'])) {
            logBulkAction($conn, $admin_id, $action, 0, 'failed', $ip_address);
            die(json_encode(['status' => 'error', 'message' => 'Neural Verification Failed: Invalid Password!']));
        }

        $conn->query("UPDATE users SET session_token = NULL");
        $affected = $conn->affected_rows;
        
        logBulkAction($conn, $admin_id, $action, $affected, 'success', $ip_address);
        echo json_encode(['status' => 'success', 'message' => 'Security Protocol: All unit sessions terminated.']);
        break;

    // 📝 CONTENT ACTIONS: Review Approval
    case 'bulk_approve_reviews':
        if($preview) {
            $res = $conn->query("SELECT COUNT(*) as total FROM reviews WHERE status = 'Pending'");
            $count = ($res && $row = $res->fetch_assoc()) ? $row['total'] : 0;
            echo json_encode(['status' => 'preview', 'count' => $count]);
            exit;
        }
        $conn->query("UPDATE reviews SET status = 'Approved' WHERE status = 'Pending'");
        $affected = $conn->affected_rows;
        logBulkAction($conn, $admin_id, $action, $affected, 'success', $ip_address);
        echo json_encode(['status' => 'success', 'message' => "$affected reviews approved."]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown Matrix Command Sequence.']);
}