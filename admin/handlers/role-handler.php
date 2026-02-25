<?php
/**
 * Prime Beast - Tactical Role & Personnel Handler (Supreme 7.0)
 * Logic: Fixed Purge Protocol & Automated JSON Matrix
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../core/db.php'; 

/**
 * 🛰️ ১. নতুন পার্সোনেল ডিপ্লয়মেন্ট
 */
if (isset($_POST['deploy_admin_with_role'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_name = $conn->real_escape_string($_POST['role_name']);
    
    // নতুন মেনুগুলো অটোমেটিক এখানে JSON হিসেবে চলে আসবে
    $permissions = isset($_POST['permissions']) ? json_encode($_POST['permissions']) : json_encode([]);

    $role_sql = "INSERT INTO roles (name, permissions) VALUES ('$role_name', '$permissions')";
    
    if($conn->query($role_sql)) {
        $role_id = $conn->insert_id;
        $admin_sql = "INSERT INTO admins (username, password, role_id, status) VALUES ('$username', '$password', $role_id, 'Active')";
        
        if($conn->query($admin_sql)) {
            header("Location: ../admins.php?success=deployed");
            exit;
        } else {
            die("Admin Neural Link Error: " . $conn->error);
        }
    } else {
        die("Role Matrix Error: " . $conn->error);
    }
}

/**
 * ⚡ ২. এক্সেস ম্যাট্রিক্স আপডেট
 */
if (isset($_POST['update_admin_role'])) {
    $admin_id = intval($_POST['admin_id']);
    $username = $conn->real_escape_string($_POST['username']);
    $role_name = $conn->real_escape_string($_POST['role_name']);
    $permissions = isset($_POST['permissions']) ? json_encode($_POST['permissions']) : json_encode([]);

    $check_admin = $conn->query("SELECT username, role_id FROM admins WHERE id = $admin_id")->fetch_assoc();
    
    if(!$check_admin || $check_admin['username'] === 'TURJO SARKER') {
        header("Location: ../admins.php?error=supreme_lock_active");
        exit;
    }

    $role_id = $check_admin['role_id'];

    $sql_admin = "UPDATE admins SET username = '$username'";
    if(!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql_admin .= ", password = '$password'";
    }
    $sql_admin .= " WHERE id = $admin_id";
    $conn->query($sql_admin);

    $conn->query("UPDATE roles SET name = '$role_name', permissions = '$permissions' WHERE id = $role_id");

    header("Location: ../admins.php?success=matrix_updated");
    exit;
}

/**
 * 💀 ৩. সিকিউরিটি পার্জ (DELETE)
 * ফিক্সড লজিক: আগে চেক করা হচ্ছে রোল আইডি ভ্যালিড কি না
 */
if (isset($_GET['action']) && $_GET['action'] == 'delete_staff') {
    $id = intval($_GET['id']);
    
    $check = $conn->query("SELECT username, role_id FROM admins WHERE id = $id")->fetch_assoc();
    
    if(!$check || $check['username'] === 'TURJO SARKER') {
        header("Location: ../admins.php?error=supreme_lock_active");
        exit;
    }

    $role_id = $check['role_id'];

    // অ্যাডমিন ডিলিট করার আগে রোল ডিলিট করা হচ্ছে
    if($role_id) {
        $conn->query("DELETE FROM roles WHERE id = $role_id");
    }
    
    if($conn->query("DELETE FROM admins WHERE id = $id")) {
        // ডিলিট সফল হওয়ার পর ক্যাশ ক্লিয়ার করতে রিডাইরেক্ট
        header("Location: ../admins.php?success=personnel_purged");
    } else {
        header("Location: ../admins.php?error=purge_failed");
    }
    exit;
}

/**
 * 📡 ৪. এজেক্স পারমিশন ফেচ
 */
if (isset($_GET['get_perms'])) {
    $role_id = intval($_GET['role_id']);
    $res = $conn->query("SELECT permissions FROM roles WHERE id = $role_id");
    
    if($res && $res->num_rows > 0) {
        $data = $res->fetch_assoc();
        echo $data['permissions']; 
    } else {
        echo json_encode([]);
    }
    exit;
}