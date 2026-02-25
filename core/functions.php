<?php
/**
 * Prime Beast - Final Universal Functions (Neural 7.0)
 * Project: Turjo Site | Products Hub BD
 * Logic: Granular RBAC, God-Mode Protection & Neural Matrix Sync
 */

// ১. সেশন শুরু করা ও সিকিউরিটি গার্ড (Session Guard)
function checkSessionSecurity() {
    if (session_status() === PHP_SESSION_NONE) { 
        session_start(); 
    }

    $timeout_limit = 3600; // ১ ঘণ্টা সেশন টাইম

    if (isset($_SESSION['admin_logged_in']) && isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];

        if ($elapsed > $timeout_limit) {
            session_unset();
            session_destroy();
            
            // লগইন পেজ বাদে অন্য সব পেজ থেকে কিক আউট করা
            if (basename($_SERVER['PHP_SELF']) != 'login.php') {
                header("Location: login.php?reason=timeout");
                exit();
            }
        }
    }
    $_SESSION['last_activity'] = time();
}

// অটোমেটিক সেশন চেক রান করা (লগইন পেজ বাদে)
if (basename($_SERVER['PHP_SELF']) != 'login.php') {
    checkSessionSecurity();
}

/**
 * ২. Granular Permission Logic (The Neural Matrix)
 * @param mysqli $conn Database Connection
 * @param string $permission_slug (Format: 'module.action' e.g., 'product_manage.view')
 * Logic: Checks if the logged-in user has specific permission in their JSON matrix.
 */
function hasPermission($conn, $permission_slug) {
    // সেশন ভ্যালিডেশন
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }

    // 👑 SUPREME GOD MODE: turjo এবং turjo0424-এর জন্য সবকিছু অলওয়েজ ট্রু (Bypass)
    $supreme_admins = ['turjo', 'turjo0424', 'TURJO SARKER'];
    if (isset($_SESSION['admin_username']) && in_array($_SESSION['admin_username'], $supreme_admins)) {
        return true;
    }

    // এডমিনের রোল আইডি চেক
    $role_id = $_SESSION['admin_role_id'] ?? 0;
    if ($role_id == 0) return false;

    // Performance Optimization-er jonno static variable bebohar
    static $user_perms = null;
    if ($user_perms === null) {
        // Error handling: 'permissions' column না থাকলেও Fatal Error দিবে না
        $query = "SELECT permissions FROM roles WHERE id = $role_id LIMIT 1";
        $result = $conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            // PHP 8.1+ Deprecated Warning Fix: Null parameter handling
            $json_data = $row['permissions'] ?? '[]'; 
            $user_perms = json_decode($json_data, true);
            
            if (!is_array($user_perms)) {
                $user_perms = [];
            }
        } else {
            $user_perms = [];
        }
    }

    // নির্দিষ্ট পারমিশন লিস্টে আছে কিনা চেক
    return in_array($permission_slug, $user_perms);
}

/**
 * ৩. গেটওয়ে গার্ড: অ্যাকশন অথোরাইজেশন (Hard Guard)
 */
function authorizeAction($conn, $permission_slug) {
    if (!hasPermission($conn, $permission_slug)) {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            die(json_encode([
                'status' => 'error', 
                'message' => 'Matrix Restricted: Access Level Insufficient!'
            ]));
        } else {
            header("Location: dashboard.php?error=unauthorized_access");
            exit();
        }
    }
}

/**
 * ৪. হেল্পার: সুপার এডমিন চেক (Supreme Admin List)
 */
function isSuperAdmin() {
    $supreme_admins = ['turjo', 'turjo0424', 'TURJO SARKER'];
    return (isset($_SESSION['admin_username']) && in_array($_SESSION['admin_username'], $supreme_admins));
}

/**
 * ৫. অ্যাক্টিভিটি লগ করার ফাংশন
 */
function logActivity($conn, $action, $details, $status = 'info', $user_id = null) {
    $user = $user_id ?? ($_SESSION['admin_username'] ?? 'System');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, status) VALUES (?, ?, ?, ?, ?)");
    if($stmt) {
        $stmt->bind_param("sssss", $user, $action, $details, $ip, $status);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    return false;
}

/**
 * ৬. কারেন্সি ফরম্যাট (৳)
 */
function formatPrice($amount) {
    return "৳ " . number_format((float)$amount, 2);
}

/**
 * ৭. টাইম ফরম্যাট
 */
function timeAgo($timestamp) {
    return date('d M, h:i A', strtotime($timestamp));
}
?>