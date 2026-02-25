<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. ডাটাবেস পাথ ঠিক করা হয়েছে
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
    <title>Create Account | KENA KATA</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f3f4f6; 
            color: #1f2937;
        }

        .login-card { 
            background: white; 
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .brand-logo {
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }

        .brand-blue { color: #004274; }
        .brand-red { color: #e11d48; }

        .input-field {
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
            background: #fff;
        }

        .input-field:focus-within {
            border-color: #004274;
            box-shadow: 0 0 0 3px rgba(0, 66, 116, 0.1);
        }

        .btn-primary {
            background-color: #004274;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #00335a;
        }

        .label-text {
            font-size: 11px;
            font-weight: 800;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">

    <div class="mb-8 text-center">
        <div class="brand-logo">
            <span class="brand-blue">KENA</span> <span class="brand-red">KATA</span>
        </div>
        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Your Trusted Online Shop</p>
    </div>

    <div class="w-full max-w-[450px] login-card p-10">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">SIGN UP</h1>
            <p class="text-sm text-gray-500 mt-1">Enter your information to create an account</p>
        </div>

        <form id="regForm" action="register-process.php" method="POST" class="space-y-5">
            <div class="space-y-1.5">
                <label for="username" class="label-text">Full Name</label>
                <div class="relative input-field rounded-lg overflow-hidden flex items-center">
                    <i class="fas fa-user ml-4 text-gray-400 text-sm"></i>
                    <input type="text" id="username" name="username" required placeholder="Your Name" 
                           class="w-full bg-transparent py-3.5 px-4 outline-none text-sm text-gray-700">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="email" class="label-text">Email Address</label>
                <div class="relative input-field rounded-lg overflow-hidden flex items-center">
                    <i class="fas fa-envelope ml-4 text-gray-400 text-sm"></i>
                    <input type="email" id="email" name="email" required placeholder="example@mail.com" 
                           class="w-full bg-transparent py-3.5 px-4 outline-none text-sm text-gray-700">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password" class="label-text">Password</label>
                <div class="relative input-field rounded-lg overflow-hidden flex items-center">
                    <i class="fas fa-lock ml-4 text-gray-400 text-sm"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••" 
                           class="w-full bg-transparent py-3.5 px-4 outline-none text-sm text-gray-700">
                </div>
            </div>

            <button type="submit" id="submitBtn" class="w-full btn-primary py-3.5 rounded-lg text-xs font-bold uppercase tracking-widest text-white mt-2 shadow-md">
                Create Account
            </button>
        </form>

        <div class="mt-8 text-center pt-6 border-t border-gray-100">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-tight">
                Already have an account? 
                <a href="login.php" class="text-red-600 hover:underline ml-1">Sign In</a>
            </p>
        </div>
    </div>

    <div class="mt-8 flex items-center space-x-4 opacity-50">
        <div class="flex items-center text-[10px] font-bold uppercase tracking-tighter">
            <i class="fas fa-shield-alt mr-1 text-green-600"></i> SSL Secured
        </div>
        <div class="text-gray-300">|</div>
        <div class="flex items-center text-[10px] font-bold uppercase tracking-tighter">
            <i class="fas fa-lock mr-1 text-gray-600"></i> 256-bit AES
        </div>
    </div>

    <script>
        const regForm = document.getElementById('regForm');
        const submitBtn = document.getElementById('submitBtn');

        regForm.onsubmit = function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> PROCESSING...';
        };
    </script>

</body>
</html>