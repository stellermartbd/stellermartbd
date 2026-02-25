<?php 
/**
 * Prime Beast - High-End User Intelligence Hub
 * Professional Behavioral Analytics & Live Tracking
 * Logic: Neural Permission Guard & Supreme Bypass
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. কোর ফাইল এবং সিকিউরিটি ইঞ্জিন লোড
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

/**
 * 🔥 Module Level Security Guard
 */
if (!hasPermission($conn, 'user_intelligence.view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Fetch Initial Stats from Database
$total_users_query = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $total_users_query ? $total_users_query->fetch_assoc()['total'] : 0;

$abandoned_orders_query = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$abandoned_orders = $abandoned_orders_query ? $abandoned_orders_query->fetch_assoc()['total'] : 0;
?>

<style>
    /* 🚀 THEME ENGINE STYLES */
    .theme-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    
    /* 🌓 LIGHT MODE OVERRIDES */
    body.light-mode main { background-color: #f8fafc !important; }
    body.light-mode header { background-color: rgba(255, 255, 255, 0.9) !important; border-bottom: 1px solid #e2e8f0; }
    body.light-mode .glass-panel { background-color: #ffffff !important; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    body.light-mode .text-white, body.light-mode h2, body.light-mode h3 { color: #1e293b !important; }
    body.light-mode .dark\:text-white { color: #1e293b !important; }
    body.light-mode .dark\:bg-\[\#110c1d\] { background-color: #ffffff !important; }
    
    /* Theme Toggle Button */
    .theme-toggle {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(168, 85, 247, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 1px solid rgba(168, 85, 247, 0.2);
        color: #a855f7;
        transition: 0.3s;
    }
    .theme-toggle:hover { background: #a855f7; color: white; }
    body.light-mode .theme-toggle { background: #f1f5f9; border-color: #cbd5e1; color: #64748b; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-[#0a0514] flex flex-col min-w-0 theme-transition">
    
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-[#110c1d]/80 backdrop-blur-md border-b border-gray-200 dark:border-white/5 sticky top-0 z-20 shrink-0 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-purple-600/10 rounded-2xl border border-purple-600/20">
                <i class="fas fa-brain text-purple-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tighter leading-none">User Intelligence</h2>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mt-1">Live Neural Network Tracking</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <button onclick="toggleTheme()" class="theme-toggle" title="Toggle Theme">
                <i id="themeIcon" class="fas fa-moon"></i>
            </button>

            <div class="hidden md:flex items-center gap-3 bg-green-500/10 border border-green-500/20 px-4 py-2 rounded-2xl">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <p class="text-[9px] font-black text-green-500 uppercase tracking-widest">Real-time Feed</p>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black shadow-xl uppercase transition hover:rotate-6">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <div class="max-w-[1600px] mx-auto space-y-8 pb-12">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="glass-panel p-6 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl group transition-all hover:border-purple-500/30">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Global Users</p>
                    <h3 class="text-3xl font-black text-gray-800 dark:text-white tracking-tighter"><?php echo number_format($total_users); ?></h3>
                    <div class="mt-2 h-1 w-full bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500" style="width: 75%"></div>
                    </div>
                </div>

                <div class="glass-panel p-6 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl group transition-all hover:border-amber-500/30">
                    <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1">Abandoned Cart</p>
                    <h3 class="text-3xl font-black text-gray-800 dark:text-white tracking-tighter"><?php echo $abandoned_orders; ?></h3>
                    <p class="text-[9px] text-gray-500 font-bold uppercase mt-2 italic">Waiting for conversion</p>
                </div>

                <div class="glass-panel p-6 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl group transition-all hover:border-blue-500/30">
                    <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-1">Engagement Score</p>
                    <h3 class="text-3xl font-black text-gray-800 dark:text-white tracking-tighter">92.4%</h3>
                    <p class="text-[9px] text-gray-500 font-bold uppercase mt-2 italic">High Retention</p>
                </div>

                <div class="glass-panel p-6 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl group transition-all hover:border-rose-500/30">
                    <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest mb-1">Risk Intensity</p>
                    <h3 class="text-3xl font-black text-gray-800 dark:text-white tracking-tighter">0.5%</h3>
                    <p class="text-[9px] text-gray-500 font-bold uppercase mt-2 italic">Low Threat detected</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 glass-panel p-8 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl">
                    <h3 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-[0.2em] mb-8">Visitor Interaction Pulse</h3>
                    <div class="h-[350px] w-full">
                        <canvas id="trafficPulseChart"></canvas>
                    </div>
                </div>

                <div class="glass-panel p-8 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl flex flex-col justify-between">
                    <h3 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-[0.2em]">Device Fingerprint</h3>
                    <div class="relative h-56 flex items-center justify-center my-6">
                        <canvas id="deviceFingerprintChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">Verified</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="p-4 bg-purple-500/5 rounded-2xl border border-purple-500/10 text-center">
                            <i class="fas fa-mobile-alt text-purple-500 mb-1"></i>
                            <p class="text-[9px] font-black text-gray-500 uppercase">Mobile: 65%</p>
                        </div>
                        <div class="p-4 bg-blue-500/5 rounded-2xl border border-blue-500/10 text-center">
                            <i class="fas fa-desktop text-blue-500 mb-1"></i>
                            <p class="text-[9px] font-black text-gray-500 uppercase">PC: 35%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="glass-panel p-8 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl">
                    <h3 class="text-xs font-black text-rose-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i class="fas fa-shield-alt"></i> Threat Intel Logs
                    </h3>
                    <div class="space-y-4" id="threat-logs">
                        <div class="p-4 bg-rose-500/5 border border-rose-500/10 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <p class="text-[10px] font-black text-gray-700 dark:text-white uppercase">Brute Force Attempt: Blocked</p>
                            </div>
                            <span class="text-[9px] font-black text-gray-500 uppercase tracking-tighter">Synced Now</span>
                        </div>
                    </div>
                </div>

                <div class="glass-panel p-8 bg-white dark:bg-[#110c1d] rounded-[2rem] border dark:border-white/5 shadow-2xl">
                    <h3 class="text-xs font-black text-blue-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt"></i> Regional Flow Heatmap
                    </h3>
                    <div class="h-44">
                        <canvas id="regionHeatmapChart"></canvas>
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
            updateCharts(true);
        } else {
            icon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('theme', 'dark');
            updateCharts(false);
        }
    }

    // Chart Update Logic for Theme Change
    function updateCharts(isLight) {
        const textColor = isLight ? '#64748b' : '#6b7280';
        [trafficPulseChart, regionHeatmapChart].forEach(chart => {
            chart.options.scales.x.ticks.color = textColor;
            chart.update();
        });
    }

    // Initialize Theme on Load
    if (localStorage.getItem('theme') === 'light') {
        document.body.classList.add('light-mode');
        document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
    }

    // 1. Visitor Interaction Pulse Chart
    const pulseCtx = document.getElementById('trafficPulseChart').getContext('2d');
    const pulseGradient = pulseCtx.createLinearGradient(0, 0, 0, 400);
    pulseGradient.addColorStop(0, 'rgba(168, 85, 247, 0.2)');
    pulseGradient.addColorStop(1, 'rgba(168, 85, 247, 0)');

    const trafficPulseChart = new Chart(pulseCtx, {
        type: 'line',
        data: {
            labels: ['12am', '4am', '8am', '12pm', '4pm', '8pm', 'Now'],
            datasets: [{
                data: [350, 890, 1500, 2800, 2100, 3100, 3500],
                borderColor: '#a855f7',
                borderWidth: 4,
                tension: 0.5,
                fill: true,
                backgroundColor: pulseGradient,
                pointRadius: 0,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { color: localStorage.getItem('theme') === 'light' ? '#64748b' : '#6b7280', font: { size: 10, weight: 'bold' } } }
            }
        }
    });

    // 2. Device Fingerprint Doughnut
    const deviceCtx = document.getElementById('deviceFingerprintChart').getContext('2d');
    new Chart(deviceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Mobile', 'Desktop'],
            datasets: [{
                data: [65, 35],
                backgroundColor: ['#a855f7', '#3b82f6'],
                borderWidth: 0,
                cutout: '88%',
                borderRadius: 20
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 3. Regional Flow Bar Chart
    const regionCtx = document.getElementById('regionHeatmapChart').getContext('2d');
    const regionHeatmapChart = new Chart(regionCtx, {
        type: 'bar',
        data: {
            labels: ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna'],
            datasets: [{
                data: [95, 70, 45, 60, 35],
                backgroundColor: '#3b82f6',
                borderRadius: 12,
                barThickness: 15
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { color: localStorage.getItem('theme') === 'light' ? '#64748b' : '#6b7280', font: { size: 9, weight: 'bold' } } }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>