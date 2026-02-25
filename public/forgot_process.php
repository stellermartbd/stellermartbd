<?php
require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../core/db.php';

if (isset($_POST['send_otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $user_check = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($user_check->num_rows > 0) {
        $otp = rand(100000, 999999);
        $conn->query("DELETE FROM password_resets WHERE email = '$email'");
        $conn->query("INSERT INTO password_resets (email, otp_code) VALUES ('$email', '$otp')");

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'webemail369@gmail.com'; 
            $mail->Password   = 'mjavpiozsjdruxfz'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587; 
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('webemail369@gmail.com', 'kenakata.com');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Security Code - kenakata.com';

            $mail->Body = "
            <div style='font-family: sans-serif; color: #1e293b; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 15px; padding: 30px;'>
                <h1 style='color: #083b66; text-align: center; font-size: 28px; margin-bottom: 5px; font-weight: 900;'>KENA KATA</h1>
                <p style='text-align: center; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 2px;'>Trusted Online Marketplace</p>
                <hr style='border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;'>
                <div style='margin-bottom: 25px;'>
                    <p style='font-weight: bold; color: #083b66;'>Hello,</p>
                    <p>A request has been received to change your password. Use the security code below to verify your identity.</p>
                </div>
                <div style='background-color: #f8fafc; border: 2px dashed #083b66; border-radius: 10px; padding: 20px; text-align: center; margin: 25px 0;'>
                    <span style='font-size: 36px; font-weight: 900; color: #083b66; letter-spacing: 10px;'>$otp</span>
                </div>
                <div style='margin-bottom: 25px;'>
                    <p style='font-weight: bold; color: #083b66;'>হ্যালো,</p>
                    <p>আপনার অ্যাকাউন্ট থেকে পাসওয়ার্ড পরিবর্তনের একটি অনুরোধ পাওয়া গেছে। আপনার পরিচয় নিশ্চিত করতে উপরের সিকিউরিটি কোডটি ব্যবহার করুন।</p>
                </div>
                <p style='color: #991b1b; font-size: 12px; border-left: 4px solid #ef4444; padding-left: 10px;'>
                    <strong>Security Alert:</strong> If you did not request this, please ignore. <br>
                    <strong>সতর্কবার্তা:</strong> আপনি অনুরোধ না করলে এটি ইগনোর করুন।
                </p>
            </div>";

            $mail->send();
            header("Location: forgot.php?email=$email&status=sent");
            exit;
        } catch (Exception $e) { echo "Error: " . $mail->ErrorInfo; }
    } else { header("Location: forgot.php?error=not_found"); exit; }
}
?>