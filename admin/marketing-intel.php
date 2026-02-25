<?php 
/**
 * Prime Admin - Marketing Intelligence Hub
 * Project: Turjo Site
 * Design: High-Contrast Dark Mode (Glassmorphism Fixed)
 */

// ১. কোর কনফিগারেশন
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../core/db.php'; 
require_once '../core/functions.php'; 
require_once '../core/csrf.php'; 

// ২. ডাটাবেস চেক
try {
    // Total Coupons
    $total_res = $conn->query("SELECT id FROM coupons");
    $total_coupons = $total_res ? $total_res->num_rows : 0;

    // Active Offers
    $active_res = $conn->query("SELECT id FROM coupons WHERE expiry_date > NOW()");
    $active_offers = $active_res ? $active_res->num_rows : 0;

    // Repeat Customers
    $repeat_res = $conn->query("SELECT user_id FROM orders GROUP BY user_id HAVING COUNT(id) > 1");
    $repeat_customers = $repeat_res ? $repeat_res->num_rows : 0;

} catch (Exception $e) {
    $total_coupons = $active_offers = $repeat_customers = 0;
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    /* 🚀 THEME TRANSITION SYSTEM */
    .theme-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    
    /* 🌓 LIGHT MODE OVERRIDES */
    body.light-mode main { background-color: #f1f5f9 !important; }
    body.light-mode header { background-color: rgba(255, 255, 255, 0.8) !important; border-bottom: 1px solid #e2e8f0; }
    body.light-mode .text-white, body.light-mode h1, body.light-mode h3 { color: #0f172a !important; }
    body.light-mode .bg-white\/\[0\.02\] { background-color: #ffffff !important; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    body.light-mode .bg-white\/5 { background-color: #f8fafc !important; border-color: #cbd5e1; }
    body.light-mode .bg-\[\#090515\]\/80 { background-color: #ffffff !important; color: #0f172a !important; }
    body.light-mode select { background-color: #f8fafc !important; color: #0f172a !important; border-color: #cbd5e1 !important; }

    /* Theme Toggle Button Style */
    .theme-toggle {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(244, 63, 94, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 1px solid rgba(244, 63, 94, 0.2);
        color: #f43f5e;
        transition: 0.3s;
    }
    .theme-toggle:hover { background: #f43f5e; color: white; }
    body.light-mode .theme-toggle { background: #fff; border-color: #cbd5e1; color: #64748b; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#090515] relative flex flex-col font-sans theme-transition selection:bg-rose-500/30 selection:text-rose-200">
    
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0 dark-only-glow">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-rose-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px]"></div>
    </div>

    <header class="h-24 flex items-center justify-between px-8 z-20 shrink-0 border-b border-white/5 bg-[#090515]/50 backdrop-blur-sm">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span class="w-2 h-8 bg-gradient-to-b from-rose-500 to-purple-600 rounded-full block shadow-[0_0_15px_rgba(244,63,94,0.5)]"></span>
                Marketing Intel
            </h1>
            <p class="text-xs font-bold text-gray-400 mt-1 ml-5 tracking-wide uppercase">Turjo Site Intelligence Hub</p>
        </div>
        
        <div class="flex items-center gap-4">
            <button onclick="toggleTheme()" class="theme-toggle" title="Toggle Theme">
                <i id="themeIcon" class="fas fa-moon"></i>
            </button>

            <div class="flex items-center gap-4 bg-white/5 border border-white/10 px-2 py-2 rounded-full backdrop-blur-md hover:bg-white/10 transition-colors">
                <div class="px-4 text-right hidden md:block">
                    <p class="text-xs font-black text-white uppercase tracking-wider"><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></p>
                    <p class="text-[9px] font-bold text-rose-400 uppercase tracking-widest">Super Admin</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-rose-500 to-purple-600 flex items-center justify-center text-white font-black shadow-lg shadow-rose-500/20 border border-white/20">
                    <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar px-8 pb-12 z-10 pt-8">
        <div class="max-w-[1600px] mx-auto space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="group relative p-6 bg-white/[0.02] backdrop-blur-xl rounded-3xl border border-white/10 hover:border-rose-500/40 transition-all duration-500 hover:shadow-[0_0_30px_rgba(244,63,94,0.15)] overflow-hidden hover:-translate-y-1">
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-gradient-to-br from-rose-500/20 to-transparent rounded-2xl border border-rose-500/20 shadow-inner">
                                <i class="fas fa-chart-pie text-rose-400 text-lg"></i>
                            </div>
                            <span class="text-[10px] font-bold text-green-400 bg-green-500/10 px-2 py-1 rounded-full border border-green-500/20 flex items-center gap-1">
                                <i class="fas fa-arrow-up text-[8px]"></i> 12%
                            </span>
                        </div>
                        <h3 class="text-4xl font-black text-white tracking-tighter drop-shadow-lg">78.5%</h3>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2 group-hover:text-rose-400 transition-colors">Coupon ROI</p>
                    </div>
                </div>

                <div class="group relative p-6 bg-white/[0.02] backdrop-blur-xl rounded-3xl border border-white/10 hover:border-purple-500/40 transition-all duration-500 hover:shadow-[0_0_30px_rgba(168,85,247,0.15)] overflow-hidden hover:-translate-y-1">
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-gradient-to-br from-purple-500/20 to-transparent rounded-2xl border border-purple-500/20 shadow-inner">
                                <i class="fas fa-bolt text-purple-400 text-lg"></i>
                            </div>
                        </div>
                        <h3 class="text-4xl font-black text-white tracking-tighter drop-shadow-lg"><?php echo str_pad($active_offers, 2, '0', STR_PAD_LEFT); ?></h3>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2 group-hover:text-purple-400 transition-colors">Live Campaigns</p>
                    </div>
                </div>

                <div class="group relative p-6 bg-white/[0.02] backdrop-blur-xl rounded-3xl border border-white/10 hover:border-cyan-500/40 transition-all duration-500 hover:shadow-[0_0_30px_rgba(6,182,212,0.15)] overflow-hidden hover:-translate-y-1">
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-gradient-to-br from-cyan-500/20 to-transparent rounded-2xl border border-cyan-500/20 shadow-inner">
                                <i class="fas fa-users-viewfinder text-cyan-400 text-lg"></i>
                            </div>
                        </div>
                        <h3 class="text-4xl font-black text-white tracking-tighter drop-shadow-lg"><?php echo str_pad($repeat_customers, 2, '0', STR_PAD_LEFT); ?></h3>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2 group-hover:text-cyan-400 transition-colors">Loyal Buyers</p>
                    </div>
                </div>

                <div class="group relative p-6 bg-white/[0.02] backdrop-blur-xl rounded-3xl border border-white/10 hover:border-amber-500/40 transition-all duration-500 hover:shadow-[0_0_30px_rgba(245,158,11,0.15)] overflow-hidden hover:-translate-y-1">
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-gradient-to-br from-amber-500/20 to-transparent rounded-2xl border border-amber-500/20 shadow-inner">
                                <i class="fas fa-coins text-amber-400 text-lg"></i>
                            </div>
                        </div>
                        <h3 class="text-4xl font-black text-white tracking-tighter drop-shadow-lg">৳ 12.4k</h3>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2 group-hover:text-amber-400 transition-colors">Promo Revenue</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="xl:col-span-2 p-8 bg-white/[0.02] backdrop-blur-md rounded-[2.5rem] border border-white/10 shadow-2xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-white uppercase tracking-tight">Redemption Analytics</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Real-time Usage Data</p>
                        </div>
                    </div>
                    
                    <div class="h-[350px] w-full relative z-10">
                        <canvas id="marketingChart"></canvas>
                    </div>
                </div>

                <div class="p-8 bg-gradient-to-b from-white/[0.03] to-transparent backdrop-blur-md rounded-[2.5rem] border border-white/10 shadow-2xl flex flex-col justify-between relative overflow-hidden group">
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2.5 bg-rose-600 rounded-lg shadow-lg shadow-rose-600/40">
                                <i class="fas fa-rocket text-white"></i>
                            </div>
                            <h3 class="text-lg font-black text-white uppercase tracking-tight">Offer Injector</h3>
                        </div>
                        <form action="handlers/marketing-handler.php" method="POST" class="space-y-6">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Target Buyer</label>
                                <select name="user_id" required class="w-full bg-[#090515]/80 border border-white/20 rounded-xl px-5 py-4 focus:outline-none focus:border-rose-500 text-sm font-bold text-white transition-all appearance-none cursor-pointer">
                                    <option value="" class="bg-[#090515] text-gray-400">Select User...</option>
                                    <?php 
                                    $users = $conn->query("SELECT id, username FROM users LIMIT 15");
                                    if($users) while($u = $users->fetch_assoc()) echo "<option value='{$u['id']}' class='bg-white text-black dark:bg-[#090515] dark:text-white'>".htmlspecialchars($u['username'])."</option>";
                                    ?>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Select Promo</label>
                                <select name="coupon_id" required class="w-full bg-[#090515]/80 border border-white/20 rounded-xl px-5 py-4 focus:outline-none focus:border-rose-500 text-sm font-bold text-white transition-all appearance-none cursor-pointer">
                                    <option value="" class="bg-[#090515] text-gray-400">Choose Promo...</option>
                                    <?php 
                                    $coupons = $conn->query("SELECT id, code FROM coupons WHERE expiry_date > NOW()");
                                    if($coupons) while($c = $coupons->fetch_assoc()) echo "<option value='{$c['id']}' class='bg-white text-black dark:bg-[#090515] dark:text-white'>".htmlspecialchars($c['code'])."</option>";
                                    ?>
                                </select>
                            </div>

                            <button type="submit" name="assign_offer" class="group relative w-full bg-gradient-to-r from-rose-600 to-rose-800 text-white font-black py-4.5 rounded-xl shadow-lg hover:scale-[1.02] transition-all overflow-hidden mt-4 border border-rose-500/50">
                                <span class="relative flex items-center justify-center gap-2 uppercase text-[11px] tracking-[0.2em]">
                                    <i class="fas fa-paper-plane text-xs"></i> Assign Offer
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// --- THEME ENGINE ---
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('themeIcon');
    
    body.classList.toggle('light-mode');
    
    if (body.classList.contains('light-mode')) {
        icon.classList.replace('fa-moon', 'fa-sun');
        localStorage.setItem('theme', 'light');
        if(window.mChart) updateChartTheme(window.mChart, true);
    } else {
        icon.classList.replace('fa-sun', 'fa-moon');
        localStorage.setItem('theme', 'dark');
        if(window.mChart) updateChartTheme(window.mChart, false);
    }
}

function updateChartTheme(chart, isLight) {
    chart.options.scales.y.ticks.color = isLight ? '#64748b' : '#94a3b8';
    chart.options.scales.x.ticks.color = isLight ? '#1e293b' : '#cbd5e1';
    chart.update();
}

document.addEventListener('DOMContentLoaded', function() {
    // Load saved theme
    if (localStorage.getItem('theme') === 'light') {
        document.body.classList.add('light-mode');
        document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
    }

    const canvas = document.getElementById('marketingChart');
    if (!canvas) return;

    const mCtx = canvas.getContext('2d');
    const gradientFill = mCtx.createLinearGradient(0, 0, 0, 400);
    gradientFill.addColorStop(0, 'rgba(244, 63, 94, 1)'); 
    gradientFill.addColorStop(1, 'rgba(244, 63, 94, 0.1)'); 

    window.mChart = new Chart(mCtx, {
        type: 'bar',
        data: {
            labels: ['WELCOME', 'TURJO25', 'BEAST50', 'GAMEON', 'PRIME'],
            datasets: [{
                label: 'Redemptions',
                data: [150, 85, 290, 60, 195],
                backgroundColor: gradientFill,
                borderRadius: 6,
                barThickness: 40,
                hoverBackgroundColor: '#f43f5e'
            }]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5] },
                    ticks: { color: localStorage.getItem('theme') === 'light' ? '#64748b' : '#94a3b8', font: { size: 11, weight: 'bold' } }
                },
                x: { 
                    grid: { display: false }, 
                    ticks: { color: localStorage.getItem('theme') === 'light' ? '#1e293b' : '#cbd5e1', font: { weight: 'bold' } } 
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>