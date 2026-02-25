<?php
require_once __DIR__ . '/../core/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $otp_code = mysqli_real_escape_string($conn, $_POST['otp_code']);

    $check = $conn->query("SELECT * FROM password_resets WHERE email = '$email' AND otp_code = '$otp_code'");

    if ($check->num_rows > 0) {
        header("Location: reset_password.php?email=" . urlencode($email) . "&otp=" . urlencode($otp_code));
        exit;
    } else {
        header("Location: forgot.php?email=" . urlencode($email) . "&error=invalid_otp");
        exit;
    }
}
?>