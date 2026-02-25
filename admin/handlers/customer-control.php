<?php
/**
 * Prime Admin - Identity Control Terminal (Handler)
 * Project: Turjo Site | Products Hub BD
 * Logic: Neural Status Sync (Ban/Unban Protocol) via AJAX
 */

// ১. কোর ফাইল লোড (Nishchit korun path thik ache)
require_once '../../core/db.php';
require_once '../../core/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/**
 * 🔥 Neural Security Guard
 * Admin-er 'customers.edit' permission ache kina check korche.
 */
if (!hasPermission($conn, 'customers.edit')) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Matrix Breach: Unauthorized Protocol Attempt!'
    ]);
    exit;
}

// ২. ডাটা রিসিভ এবং স্যানিটাইজেশন
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['action'])) {
    
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action']; // 'Blocked' or 'Active'

    // সুপ্রীম এডমিন প্রোটেকশন (Optional: User ID 1 ke ban kora jabe na)
    if($user_id === 1) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Critical Error: Cannot lockdown a Supreme Identity!'
        ]);
        exit;
    }

    // ৩. ডাটাবেস আপডেট প্রটোকল
    // Prepared Statement bebohar kora hoyeche SQL Injection prothirodhe.
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $user_id);

    if ($stmt->execute()) {
        // Success Matrix Response
        $statusMsg = ($action === 'Blocked') ? "Identity #$user_id has been locked down." : "Identity #$user_id access has been restored.";
        
        echo json_encode([
            'status' => 'success', 
            'message' => $msg ?? $statusMsg 
        ]);
    } else {
        // Database Error Logic
        echo json_encode([
            'status' => 'error', 
            'message' => 'Critical Error: Database Matrix Sync Failed!'
        ]);
    }

    $stmt->close();
    $conn->close();

} else {
    // Invalid Request Protocol
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid Access Protocol: Method Not Allowed.'
    ]);
}
?>