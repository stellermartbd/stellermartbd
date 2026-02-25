<?php
require_once __DIR__ . '/../core/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $otp_code = mysqli_real_escape_string($conn, $_POST['otp_code']);
    // পাসওয়ার্ড হ্যাশ করা হচ্ছে নিরাপত্তার জন্য [cite: 2026-02-21]
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    // ডাটাবেসে ওটিপি চেক করা [cite: 2026-01-20]
    $check = $conn->query("SELECT * FROM password_resets WHERE email = '$email' AND otp_code = '$otp_code'");

    if ($check->num_rows > 0) {
        // ১. পাসওয়ার্ড আপডেট [cite: 2026-02-21]
        $conn->query("UPDATE users SET password = '$new_password' WHERE email = '$email'");
        
        // ২. কাজ শেষ হওয়ার পর ওটিপি ডিলিট করা [cite: 2026-01-20]
        $conn->query("DELETE FROM password_resets WHERE email = '$email'");
        $success = true;
    } else {
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Status | KENA KATA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-[400px] bg-white p-10 rounded-2xl shadow-xl text-center border border-gray-100">
        
        <?php if(isset($success) && $success): ?>
            <div class="mb-6">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-800 uppercase leading-none">Password Updated</h2>
                <p class="text-sm text-gray-500 mt-3 font-medium">Your account is now secure. Redirecting to login...</p>
            </div>
            <script>
                // ৩ সেকেন্ড পর অটোমেটিক লগইন পেজে পাঠাবে
                setTimeout(function(){
                    window.location.href = 'login.php';
                }, 2000);
            </script>
        <?php else: ?>
            <div class="mb-6">
                <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-800 uppercase leading-none">Invalid Code</h2>
                <p class="text-sm text-gray-500 mt-3 font-medium">The OTP you entered is incorrect or expired.</p>
            </div>
            <a href="forgot.php" class="inline-block bg-[#083b66] text-white px-8 py-3 rounded-xl font-bold uppercase text-xs tracking-widest hover:bg-black transition-all">Try Again</a>
        <?php endif; ?>

    </div>
</body>
</html>