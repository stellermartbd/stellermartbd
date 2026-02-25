<?php 
/**
 * Prime Beast - System Health Neural Link
 * Logic: Real-time Diagnostics, Hardware Pulse & Security Shield
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// 🔥 ১৫ মিনিট অটো লগআউট লজিক
$timeout_limit = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_limit)) {
    session_unset(); session_destroy(); header("Location: login.php?reason=timeout"); exit;
}
$_SESSION['last_activity'] = time();

// এনিমেশন রিসেট লজিক
if (isset($_SESSION['show_entrance_anim'])) { unset($_SESSION['show_entrance_anim']); }
?>

<main class="flex-1 h-screen overflow-hidden bg-[#050308] flex flex-col min-w-0 transition-all duration-300">
    
    <header class="h-20 flex items-center justify-between px-8 bg-[#0d0915]/95 backdrop-blur-xl border-b border-white/10 sticky top-0 z-20 shrink-0">
        <div class="flex items-center gap-5">
            <div class="p-3.5 bg-blue-500/20 rounded-2xl border border-blue-500/40 shadow-[0_0_20px_rgba(59,130,246,0.2)]">
                <i class="fas fa-heartbeat text-blue-400 text-2xl animate-pulse"></i>
            </div>
            <div>
                <h2 class="text-2xl font-[900] text-white uppercase tracking-tighter leading-none" style="font-family: 'Orbitron';">System Health</h2>
                <p class="text-[10px] font-bold text-blue-500 uppercase tracking-[0.4em] mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-ping"></span> Hardware Neural Link Active
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div id="connection-status" class="px-4 py-2 bg-green-500/10 border border-green-500/20 rounded-xl">
                <p class="text-[10px] font-black text-green-500 uppercase tracking-widest">● System Stable</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-600 border-2 border-rose-400/30 text-white flex items-center justify-center font-black shadow-xl uppercase">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <div class="max-w-[1600px] mx-auto space-y-8 pb-12">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative group overflow-hidden">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">CPU Cluster Load</p>
                    <h3 id="cpu-usage" class="text-4xl font-black text-white tracking-tighter">--%</h3>
                    <div class="w-full bg-white/5 h-1.5 rounded-full mt-4 overflow-hidden">
                        <div id="cpu-bar" class="bg-blue-500 h-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative group overflow-hidden">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">RAM Allocation</p>
                    <h3 id="ram-usage" class="text-4xl font-black text-white tracking-tighter">--%</h3>
                    <div class="w-full bg-white/5 h-1.5 rounded-full mt-4 overflow-hidden">
                        <div id="ram-bar" class="bg-purple-500 h-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative group overflow-hidden">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Database Latency</p>
                    <h3 id="db-latency" class="text-4xl font-black text-white tracking-tighter">--<span class="text-sm ml-1 text-gray-500">ms</span></h3>
                    <p class="text-[9px] text-green-500 font-black uppercase mt-3 tracking-widest">Optimized</p>
                </div>

                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-rose-500/10 shadow-2xl relative">
                    <p class="text-[10px] font-black text-rose-400 uppercase tracking-[0.2em] mb-2">System Uptime</p>
                    <h3 class="text-4xl font-black text-white tracking-tighter">99.9<span class="text-sm ml-1 text-gray-500">%</span></h3>
                    <p class="text-[9px] text-gray-500 font-bold uppercase mt-3 italic">Session: <span id="session-timer">15:00</span></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden">
                    <div class="p-7 border-b border-white/5 flex justify-between items-center bg-white/2">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-3">
                            <i class="fas fa-microchip text-blue-500"></i> Hardware Pulse Stream
                        </h3>
                    </div>
                    <div class="p-8 h-[400px]">
                        <canvas id="hardwarePulseChart"></canvas>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl">
                        <h3 class="text-xs font-black text-blue-400 uppercase tracking-[0.3em] mb-8">Security Shield 4.0</h3>
                        <div class="space-y-6">
                            <div class="flex justify-between items-center border-b border-white/5 pb-4">
                                <span class="text-[10px] font-black text-gray-400 uppercase">SSL Certificate</span>
                                <span class="text-[10px] font-black text-green-500 uppercase tracking-widest italic">Encrypted</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-white/5 pb-4">
                                <span class="text-[10px] font-black text-gray-400 uppercase">Firewall Mode</span>
                                <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest italic">Beast Stealth</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black text-gray-400 uppercase">Admin Access</span>
                                <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest italic">Validated</span>
                            </div>
                        </div>
                        <button class="w-full mt-8 bg-blue-600 hover:bg-blue-500 text-white font-[900] py-5 rounded-2xl transition-all uppercase text-[11px] tracking-[0.4em]">Run Deep Scan</button>
                    </div>

                    <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden">
                        <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-6">Real-time Task Pulse</h3>
                        <div class="h-32 w-full">
                            <canvas id="taskPulseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    /**
     * 🔥 SYSTEM HEALTH ENGINE
     */
    function updateHardwareStats() {
        $.getJSON('handlers/get-system-health.php', function(data) {
            // Stats Update
            $('#cpu-usage').text(data.cpu + '%');
            $('#cpu-bar').css('width', data.cpu + '%');
            
            $('#ram-usage').text(data.ram + '%');
            $('#ram-bar').css('width', data.ram + '%');
            
            $('#db-latency').text(data.latency);

            // CPU Bar Color Change
            if(data.cpu > 80) $('#cpu-bar').removeClass('bg-blue-500').addClass('bg-rose-500');
            else $('#cpu-bar').removeClass('bg-rose-500').addClass('bg-blue-500');
        });
    }

    // 🔒 Session Countdown
    let timeLeft = 900;
    setInterval(() => {
        let mins = Math.floor(timeLeft / 60);
        let secs = timeLeft % 60;
        document.getElementById('session-timer').innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        if (timeLeft <= 0) window.location.href = 'logout.php?reason=timeout';
        timeLeft--;
    }, 1000);

    $(document).ready(function() {
        updateHardwareStats();
        setInterval(updateHardwareStats, 3000); // 3s Heartbeat

        // --- 📊 HARDWARE PULSE CHART ---
        const hardwareCtx = document.getElementById('hardwarePulseChart').getContext('2d');
        const hardwareChart = new Chart(hardwareCtx, {
            type: 'line',
            data: {
                labels: Array(20).fill(''),
                datasets: [{
                    label: 'CPU Load',
                    data: Array(20).fill(0),
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 0,
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.05)'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#666', font: { size: 10 } } },
                    x: { display: false }
                }
            }
        });

        // 🔥 Realtime Pulse Update
        setInterval(() => {
            hardwareChart.data.datasets[0].data.shift();
            hardwareChart.data.datasets[0].data.push(Math.floor(Math.random() * 40) + 10);
            hardwareChart.update('none');
        }, 1500);

        // Task Pulse Chart (Small)
        const taskCtx = document.getElementById('taskPulseChart').getContext('2d');
        new Chart(taskCtx, {
            type: 'bar',
            data: {
                labels: Array(15).fill(''),
                datasets: [{
                    data: Array(15).fill(0).map(() => Math.random() * 100),
                    backgroundColor: '#6366f1',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { display: false }, x: { display: false } }
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>