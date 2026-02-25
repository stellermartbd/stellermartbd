<?php
/**
 * Prime Admin - Secure Logout Handler
 * Project: Turjo Site | Logic: Session Termination
 */
session_start();

// 🧹 Shob session data clear kora
session_unset();
session_destroy();

// 🚀 Login page-e pathano
header("Location: login.php?status=success");
exit;
?>