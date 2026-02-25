<?php
/**
 * Prime Beast - Neural Login Handler (Supreme 7.0)
 * Project: Turjo Site | Products Hub BD
 * Logic: God-Mode Protection, Session Injection & RBAC Initialization
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // ১. ডাটাবেস থেকে এডমিন এবং তার রোল খুঁজে বের করা
    $query = "SELECT a.*, r.name as role_name FROM admins a 
              LEFT JOIN roles r ON a.role_id = r.id 
              WHERE a.username = '$username' LIMIT 1";
    
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // ২. পাসওয়ার্ড ভেরিফিকেশন (Bypass logic for Supreme Admin)
        if (password_verify($password, $admin['password'])) {
            
            // ৩. Neural Session Injection
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role_id'] = $admin['role_id']; 
            $_SESSION['admin_role_name'] = $admin['role_name'] ?? 'RESTRICTED';
            $_SESSION['last_activity'] = time();

            // 👑 SUPREME ADMIN BYPASS CHECK (TURJO SARKER & turjo0424)
            // এটি নিশ্চিত করে যে এই আইডিগুলো ড্যাশবোর্ডের সব ফাংশনালিটি ডিফল্টভাবে পাবে।
            if ($admin['username'] === 'TURJO SARKER' || $admin['username'] === 'turjo0424') {
                $_SESSION['is_god_mode'] = true;
                logActivity($conn, "Supreme Login", "Supreme Admin {$admin['username']} has entered the matrix.");
            } else {
                $_SESSION['is_god_mode'] = false;
                logActivity($conn, "Login Success", "Admin {$admin['username']} logged in.");
            }

            header("Location: ../dashboard.php");
            exit();
        } else {
            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: ../login.php?error=user_not_found");
        exit();
    }
}