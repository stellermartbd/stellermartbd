<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../core/db.php'; 

// forgot.php থেকে আসা ইমেইল এবং ওটিপি চেক করা [cite: 2026-02-21]
$email = $_GET['email'] ?? ''; 
$otp = $_GET['otp'] ?? '';

// যদি সরাসরি কেউ এই পেজে আসার চেষ্টা করে তবে তাকে ফেরত পাঠানো
if (empty($email) || empty($otp)) {
    header("Location: forgot.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | KENA KATA</title>
    
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
        .reset-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        .brand-logo { font-weight: 900; letter-spacing: -1px; }
        .input-field {
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease-in-out;
        }
        .input-field:focus {
            border-color: var(--sky-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(8, 59, 102, 0.1);
        }
        .btn-update {
            background-color: var(--sky-blue);
            transition: all 0.2s ease;
        }
        .btn-update:hover {
            background-color: #062c4d;
            transform: translateY(-1px);
        }
        /* No italic override */
        * { font-style: normal !important; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-[420px]">
        <div class="text-center mb-8">
            <a href="../index.php" class="inline-block">
                <h1 class="text-4xl brand-logo text-[#083b66] uppercase leading-none">KENA<span class="text-red-600"> KATA</span></h1>
                <p class="text-[11px] font-bold text-gray-700 uppercase tracking-[0.2em] mt-2">Your Trusted Online Shop</p>
            </a>
        </div>

        <div class="reset-card p-8 md:p-10">
            <div class="text-left mb-8">
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight uppercase">New Password</h2>
                <p class="text-sm text-gray-600 mt-1 font-medium">Create a strong password for your account</p>
            </div>

            <form action="update_password.php" method="POST" class="space-y-6">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="otp_code" value="<?php echo htmlspecialchars($otp); ?>">

                <div>
                    <label for="password" class="block text-[11px] font-extrabold text-gray-700 uppercase tracking-widest mb-2 ml-1">New Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="password" name="password" required placeholder="••••••••" 
                               class="input-field w-full rounded-lg py-3.5 pl-11 pr-4 text-sm font-semibold text-gray-800">
                    </div>
                    <p class="text-[9px] text-gray-400 mt-2 font-bold uppercase tracking-tighter">Use at least 8 characters</p>
                </div>

                <button type="submit" class="btn-update w-full py-4 rounded-lg text-white font-bold uppercase text-[11px] tracking-[0.2em] shadow-lg active:scale-[0.98]">
                    Save New Password
                </button>
            </form>

            <div class="mt-8 text-center pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-600 font-bold uppercase">
                    Changed your mind? 
                    <a href="login.php" class="text-red-600 hover:text-blue-900 ml-1 transition-colors underline underline-offset-4 decoration-2">Back to Login</a>
                </p>
            </div>
        </div>

        <div class="mt-8 flex justify-center items-center gap-4 opacity-60">
            <div class="flex items-center gap-2 text-[10px] font-bold text-gray-600 uppercase">
                <i class="fas fa-shield-check text-emerald-600"></i> Secure Encryption
            </div>
        </div>
    </div>

</body>
</html>