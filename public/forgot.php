<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../core/db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery | KENA KATA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --sky-blue: #083b66; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; }
        .reset-card { background: #ffffff; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(8, 59, 102, 0.1); border: 1px solid #e2e8f0; }
        .input-box { border: 2px solid #f1f5f9; transition: 0.3s; background: #f8fafc; }
        .input-box:focus { border-color: var(--sky-blue); background: #ffffff; outline: none; }
        .btn-verify { background-color: var(--sky-blue); transition: 0.3s; }
        .btn-verify:hover { background-color: #052a4a; transform: translateY(-1px); }
        * { font-style: normal !important; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-[440px]">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-[#083b66] uppercase tracking-tighter">KENA<span class="text-red-600"> KATA</span></h1>
        </div>

        <div class="reset-card p-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 uppercase tracking-tight">Identity Check</h2>
            <p class="text-xs text-gray-500 mb-8 font-medium">Verify your email to continue password reset</p>

            <?php if(isset($_GET['status']) && $_GET['status'] == 'sent'): ?>
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-[11px] font-bold uppercase">
                    <i class="fas fa-check-circle mr-2"></i> Security code sent successfully.
                </div>
            <?php endif; ?>

            <form action="forgot_process.php" method="POST" class="mb-10 pb-10 border-b border-gray-100">
                <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-3">Registered Email</label>
                <div class="flex flex-col gap-3">
                    <input type="email" name="email" required placeholder="name@domain.com" value="<?php echo $_GET['email'] ?? ''; ?>"
                           class="input-box w-full rounded-xl py-4 px-5 text-sm font-bold">
                    
                    <div class="flex justify-between items-center px-1">
                        <button type="submit" id="sendBtn" name="send_otp" class="text-[11px] font-black text-blue-800 hover:text-red-600 uppercase tracking-wider">
                            Send Code Now
                        </button>
                        <span id="timerDisplay" class="text-[11px] font-extrabold text-gray-400 hidden">Resend in <span id="countdown">30</span>s</span>
                    </div>
                </div>
            </form>

            <form action="verify_otp_process.php" method="POST" class="space-y-6">
                <input type="hidden" name="email" value="<?php echo $_GET['email'] ?? ''; ?>">
                <div>
                    <label class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-3">Enter OTP code here</label>
                    <input type="text" name="otp_code" maxlength="6" required placeholder="••••••" 
                           class="input-box w-full rounded-2xl py-5 text-center text-3xl font-black tracking-[0.6em]">
                </div>
                <button type="submit" class="btn-verify w-full py-5 rounded-2xl text-white font-black uppercase text-[12px] tracking-[0.2em] shadow-lg">
                    Verify 
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="login.php" class="text-[11px] font-bold text-gray-400 hover:text-[#083b66] uppercase">Return to Login</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'sent') {
                const sendBtn = document.getElementById('sendBtn');
                const timerDisplay = document.getElementById('timerDisplay');
                const countdown = document.getElementById('countdown');
                let seconds = 30;
                
                sendBtn.classList.add('hidden');
                timerDisplay.classList.remove('hidden');

                const interval = setInterval(() => {
                    seconds--;
                    countdown.textContent = seconds;
                    if (seconds <= 0) {
                        clearInterval(interval);
                        sendBtn.classList.remove('hidden');
                        timerDisplay.classList.add('hidden');
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>