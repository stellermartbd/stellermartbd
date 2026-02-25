<?php 
// ১. সেশন এবং ডাটাবেস চেক
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../core/db.php'; 

// ২. লগইন থাকলে হোমে রিডাইরেক্ট
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Sign In | KENA KATA</title>
    
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.tailwindcss.com">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" media="print" onload="this.media='all'">
    
    <style>
        :root { 
            --sky-blue: #083b66; 
            --accent-red: #ef4444; 
        }
        body { 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
        }
        /* Performance: Prevent layout shift with explicit container sizing */
        .login-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }
        .brand-logo { 
            font-weight: 900; 
            letter-spacing: -1px;
        }
        .input-group input {
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease-in-out;
        }
        .input-group input:focus {
            border-color: var(--sky-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(8, 59, 102, 0.1);
        }
        .btn-primary {
            background-color: var(--sky-blue);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #062c4d;
            transform: translateY(-1px);
        }
        /* Accessibility: High contrast colors */
        .text-dark-gray { color: #4a5568 !important; }
        
        /* Strict No Italic Policy */
        * { font-style: normal !important; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-[420px]">
        <div class="text-center mb-8">
            <a href="../index.php" class="inline-block" aria-label="Go to Home Page">
                <h1 class="text-4xl brand-logo text-[#083b66] uppercase leading-none">KENA<span class="text-red-600"> KATA</span></h1>
                <p class="text-[11px] font-bold text-gray-700 uppercase tracking-[0.2em] mt-2">Your Trusted Online Shop</p>
            </a>
        </div>

        <div class="login-container p-8 md:p-10">
            <div class="text-left mb-8">
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight uppercase">Sign In</h2>
                <p class="text-sm text-gray-600 mt-1 font-medium">Enter your information to access your account</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-[11px] font-bold uppercase tracking-wide flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-sm"></i>
                    <span>
                        <?php 
                            if($_GET['error'] == 'wrong_password') echo "Wrong Password! Please try again.";
                            else if($_GET['error'] == 'user_not_found') echo "No account found with this email.";
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST" class="space-y-6">
                <div class="input-group">
                    <label for="email" class="block text-[11px] font-extrabold text-gray-700 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-500" aria-hidden="true"></i>
                        <input type="email" id="email" name="email" required placeholder="example@mail.com" 
                               class="w-full rounded-lg py-3.5 pl-11 pr-4 text-sm font-semibold text-gray-800">
                    </div>
                </div>

                <div class="input-group">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="text-[11px] font-extrabold text-gray-700 uppercase tracking-widest ml-1">Password</label>
                        <a href="forgot.php" class="text-[10px] font-bold text-blue-900 hover:text-red-600 uppercase tracking-tighter transition-colors">Forgot Password?</a>
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-500" aria-hidden="true"></i>
                        <input type="password" id="password" name="password" required placeholder="••••••••" 
                               class="w-full rounded-lg py-3.5 pl-11 pr-4 text-sm font-semibold text-gray-800">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-4 rounded-lg text-white font-bold uppercase text-[11px] tracking-[0.2em] shadow-lg active:scale-[0.98]">
                     Sign In
                </button>
            </form>

            <div class="mt-8 text-center pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-600 font-bold uppercase tracking-tight">
                    Have no account ? 
                    <a href="register.php" class="text-red-600 hover:text-blue-900 ml-1 transition-colors underline underline-offset-4 decoration-2">Create Account</a>
                </p>
            </div>
        </div>

        <div class="mt-8 flex justify-center items-center gap-4 opacity-60">
            <div class="flex items-center gap-2 text-[10px] font-bold text-gray-600 uppercase">
                <i class="fas fa-shield-alt text-emerald-600"></i> SSL SECURED
            </div>
            <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
            <div class="flex items-center gap-2 text-[10px] font-bold text-gray-600 uppercase">
                <i class="fas fa-lock text-emerald-600"></i> 256-BIT AES
            </div>
        </div>
    </div>

</body>
</html>