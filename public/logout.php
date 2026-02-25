<?php
ob_start();
session_start();

// ১. সেশন পুরোপুরি ক্লিয়ার করা
$_SESSION = array();

// ২. সেশন কুকি ডিলিট করা
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ৩. সেশন ডেস্ট্রয়
session_destroy();

// ৪. এক ধাপ পেছনে (root-এ) ইনডেক্স ফাইলে পাঠানো
ob_end_clean();
header("Location: ../index.php?logout=success");
exit;