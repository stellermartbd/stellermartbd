<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../core/db.php'; 

require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // পাসওয়ার্ড হ্যাশ করা হচ্ছে নিরাপত্তার জন্য [cite: 2026-02-11]
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // ১. Email check (Duplicate prevent করার জন্য) [cite: 2026-01-20]
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Identity already exists!";
        header("Location: register.php");
        exit;
    }

    // ২. Data Insert করা (Prepared Statement ব্যবহার করে) [cite: 2026-01-20, 2026-02-11]
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    
    if ($stmt->execute()) {
        // নতুন ইউজারের আইডি ডাটাবেস থেকে নেওয়া [cite: 2026-02-21]
        $new_user_id = $conn->insert_id; 
        
        // অটো-লগইন সেশন সেটআপ [cite: 2026-02-21]
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;

        // ৩. Welcome Mail (PHPMailer - Customized for kenakata.com) [cite: 2026-02-21]
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'webemail369@gmail.com'; 
            $mail->Password   = 'mjavpiozsjdruxfz'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('webemail369@gmail.com', 'kenakata.com');
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to kenakata.com! Your Journey Starts Here 🛍️✨'; 
            
            // --- প্রিমিয়াম লেটার টেম্পলেট শুরু --- [cite: 2026-02-21]
            $mail->Body    = "
            <div style='font-family: \"Plus Jakarta Sans\", Arial, sans-serif; line-height: 1.6; color: #1a202c; max-width: 600px; margin: auto; border: 1px solid #edf2f7; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);'>
                <div style='background: linear-gradient(135deg, #083b66 0%, #111827 100%); padding: 40px 20px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 28px; letter-spacing: -0.5px;'>kenakata.com</h1>
                    <p style='color: #cbd5e0; font-size: 14px; margin-top: 10px;'>Your Premium Shopping Destination</p>
                </div>
                <div style='padding: 30px;'>
                    <h2 style='color: #2d3748; font-size: 20px; margin-top: 0;'>Hi $username,</h2>
                    <p style='font-size: 16px; color: #4a5568;'>Welcome to <strong>kenakata.com!</strong> 🛍️</p>
                    <p style='font-size: 15px; color: #4a5568;'>We are absolutely thrilled to have you as part of our community. Your account is now active, and a world of premium products and exclusive deals is waiting for you.</p>
                    <div style='background-color: #f7fafc; border-radius: 12px; padding: 20px; margin: 25px 0;'>
                        <h3 style='font-size: 14px; color: #2d3748; text-transform: uppercase; letter-spacing: 1px; margin-top: 0;'>What's Next for You?</h3>
                        <ul style='list-style-type: none; padding-left: 0; margin-bottom: 0;'>
                            <li style='margin-bottom: 12px; display: flex; align-items: center;'>
                                <span style='color: #38a169; margin-right: 10px;'>✔</span> 
                                <span style='font-size: 14px; color: #4a5568;'>Access your personalized dashboard</span>
                            </li>
                            <li style='margin-bottom: 12px; display: flex; align-items: center;'>
                                <span style='color: #38a169; margin-right: 10px;'>✔</span> 
                                <span style='font-size: 14px; color: #4a5568;'>Manage and track your orders in real-time</span>
                            </li>
                            <li style='margin-bottom: 12px; display: flex; align-items: center;'>
                                <span style='color: #38a169; margin-right: 10px;'>✔</span> 
                                <span style='font-size: 14px; color: #4a5568;'>Unlock exclusive member-only discounts</span>
                            </li>
                            <li style='display: flex; align-items: center;'>
                                <span style='color: #38a169; margin-right: 10px;'>✔</span> 
                                <span style='font-size: 14px; color: #4a5568;'>Fast and secure digital deliveries</span>
                            </li>
                        </ul>
                    </div>
                    <p style='font-size: 15px; color: #4a5568;'>If you have any questions or need immediate assistance, simply reply to this email. Our support team is always here to help you grow and make your shopping experience amazing! 💙</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://kenakata.com' style='background-color: #083b66; color: #ffffff; padding: 14px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; display: inline-block;'>Start Shopping Now</a>
                    </div>
                    <hr style='border: 0; border-top: 1px solid #edf2f7; margin: 30px 0;'>
                    <p style='font-size: 14px; color: #718096; margin-bottom: 0;'>
                        Best Regards,<br>
                        <strong>Turjo Sarker</strong><br>
                        Founder, kenakata.com
                    </p>
                </div>
                <div style='background-color: #f7fafc; padding: 20px; text-align: center; border-top: 1px solid #edf2f7;'>
                    <p style='font-size: 12px; color: #a0aec0; margin: 0;'>&copy; 2026 kenakata.com. All rights reserved.</p>
                </div>
            </div>";
            // --- প্রিমিয়াম লেটার টেম্পলেট শেষ --- [cite: 2026-02-21]

            $mail->send();
        } catch (Exception $e) {
            // ইমেইল না গেলেও রেজিস্ট্রেশন সম্পন্ন হবে [cite: 2026-02-21]
        }

        // ৪. সরাসরি প্রোফাইল পেজে রিডাইরেক্ট [cite: 2026-02-21]
        header("Location: profile.php");
        exit;
    } else {
        // ডাটাবেস এরর হ্যান্ডলিং [cite: 2026-02-11]
        $_SESSION['error'] = "Registration failed: " . $conn->error;
        header("Location: register.php");
        exit;
    }
}