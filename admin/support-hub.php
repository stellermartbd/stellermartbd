<?php 
/**
 * Prime Beast - Support Command Center 2.0
 * Project: Turjo Site | Products Hub BD
 * Logic: AJAX Live Stream, High-Contrast UI & Neural Ticketing
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
?>

<main class="flex-1 h-screen overflow-hidden bg-[#050308] flex flex-col min-w-0 transition-all duration-300">
    
    <header class="h-20 flex items-center justify-between px-8 bg-[#0d0915]/95 backdrop-blur-xl border-b border-white/10 sticky top-0 z-20 shrink-0">
        <div class="flex items-center gap-5">
            <div class="p-3.5 bg-green-500/20 rounded-2xl border border-green-500/40 shadow-[0_0_20px_rgba(34,197,94,0.2)]">
                <i class="fas fa-headset text-green-400 text-2xl animate-pulse"></i>
            </div>
            <div>
                <h2 class="text-2xl font-[900] text-white uppercase tracking-tighter leading-none" style="font-family: 'Orbitron';">Support Hub</h2>
                <p class="text-[10px] font-bold text-green-500/80 uppercase tracking-[0.4em] mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-ping"></span> Realtime Resolution Link
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-8">
            <div class="hidden lg:flex gap-8 border-r border-white/10 pr-8 text-right">
                <div>
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Active Tickets</p>
                    <p id="live-open-count" class="text-lg font-black text-rose-500 shadow-rose-500/20 shadow-sm">--</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Refunds</p>
                    <p id="live-refund-count" class="text-lg font-black text-amber-500">--</p>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-600 border-2 border-rose-400/30 text-white flex items-center justify-center font-black shadow-[0_0_15px_rgba(225,29,72,0.4)] uppercase transition hover:scale-105 active:scale-95">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <div class="max-w-[1600px] mx-auto space-y-8 pb-12">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative group hover:border-green-500/30 transition-all duration-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Efficiency Rating</p>
                    <h3 class="text-4xl font-black text-white tracking-tighter">14<span class="text-base ml-1 text-green-500">m</span></h3>
                    <div class="mt-4 inline-flex px-3 py-1 bg-green-500/10 border border-green-500/20 rounded-full">
                        <span class="text-[9px] font-black text-green-400 uppercase tracking-tighter italic">God Speed Level</span>
                    </div>
                    <i class="fas fa-bolt text-7xl absolute -right-4 -bottom-4 opacity-[0.05] group-hover:opacity-10 transition-all rotate-12 group-hover:rotate-0 text-yellow-500"></i>
                </div>

                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative group hover:border-blue-500/30 transition-all">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">User Trust</p>
                    <h3 class="text-4xl font-black text-white tracking-tighter">99.2%</h3>
                    <p class="text-[9px] text-blue-400 font-bold uppercase mt-3 italic tracking-widest">+2.4% this week</p>
                    <i class="fas fa-heart text-7xl absolute -right-4 -bottom-4 opacity-[0.05] text-rose-500"></i>
                </div>

                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative group hover:border-amber-500/30 transition-all">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Neural Queries</p>
                    <h3 id="live-query-display" class="text-4xl font-black text-white tracking-tighter">04</h3>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="w-2 h-2 bg-amber-500 rounded-full animate-ping"></div>
                        <span class="text-[9px] text-amber-500 font-black uppercase tracking-widest">Live Monitoring</span>
                    </div>
                </div>

                <div class="p-7 bg-[#110c1d] rounded-[2.5rem] border border-rose-500/10 shadow-2xl relative group">
                    <p class="text-[10px] font-black text-rose-400 uppercase tracking-[0.2em] mb-2">Session Health</p>
                    <h3 id="session-timer" class="text-4xl font-black text-white tracking-tighter">15:00</h3>
                    <p class="text-[9px] text-gray-500 font-bold uppercase mt-3 italic">Automatic Lockout</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden">
                    <div class="p-7 border-b border-white/5 flex justify-between items-center bg-white/2">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-3">
                            <i class="fas fa-stream text-blue-500"></i> Active Resolution Stream
                        </h3>
                        <button onclick="refreshTickets()" class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-xl transition-all active:rotate-180 duration-500">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-black/20 text-[11px] text-gray-500 uppercase tracking-[0.2em]">
                                    <th class="p-7 pl-10">Thread Info</th>
                                    <th class="p-7">Class</th>
                                    <th class="p-7 text-center">Priority</th>
                                    <th class="p-7 text-right pr-10">Operations</th>
                                </tr>
                            </thead>
                            <tbody id="ticket-stream-body" class="text-xs font-bold">
                                <tr><td colspan="4" class="text-center py-20 animate-pulse text-gray-600 tracking-widest uppercase">Syncing Stream...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-green-500/10 shadow-2xl group hover:border-green-500/20 transition-all">
                        <h3 class="text-xs font-black text-green-400 uppercase tracking-[0.3em] mb-8 flex items-center gap-3">
                            <i class="fas fa-satellite-dish animate-bounce"></i> Quick Broadcast
                        </h3>
                        <form id="broadcastForm" class="space-y-5">
                            <select name="type" class="w-full bg-black/40 border border-white/5 rounded-2xl p-5 text-[11px] font-bold text-white uppercase tracking-widest outline-none focus:border-green-500 transition shadow-inner">
                                <option value="Announcement">Global Announcement</option>
                                <option value="Maintenance">Maintenance Alert</option>
                                <option value="Promo">Promo Notification</option>
                            </select>
                            <textarea name="message" rows="5" placeholder="Type global tactical message..." class="w-full bg-black/40 border border-white/5 rounded-2xl p-5 text-xs font-bold text-white outline-none focus:border-green-500 transition custom-scrollbar placeholder:text-gray-700"></textarea>
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-[900] py-6 rounded-2xl shadow-xl shadow-green-900/20 hover:scale-[1.02] active:scale-95 transition-all uppercase text-[11px] tracking-[0.4em]">Initialize Push</button>
                        </form>
                    </div>

                    <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden relative">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Neural Chat Pulse</h3>
                            <span id="live-chat-count" class="text-[10px] font-black text-green-500">● 04 Waiting</span>
                        </div>
                        <div class="h-32 w-full">
                            <canvas id="supportPulseChart"></canvas>
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
     * 🔥 REAL-TIME ENGINE
     */
    function updateLiveStats() {
        $.getJSON('handlers/get-support-stats.php', function(data) {
            $('#live-open-count').text(data.open).addClass('animate-pulse');
            $('#live-refund-count').text(data.refunds);
            $('#live-query-display').text(data.queries);
            $('#live-chat-count').text('● ' + data.waiting + ' Waiting');
            setTimeout(() => $('#live-open-count').removeClass('animate-pulse'), 1000);
        });
    }

    function refreshTickets() {
        $('#ticket-stream-body').load('handlers/fetch-live-tickets.php');
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
        updateLiveStats();
        refreshTickets();
        setInterval(updateLiveStats, 10000);
        setInterval(refreshTickets, 30000);

        // Gradient Chart Logic
        const pulseCtx = document.getElementById('supportPulseChart').getContext('2d');
        const pulseChart = new Chart(pulseCtx, {
            type: 'line',
            data: {
                labels: ['', '', '', '', '', '', ''],
                datasets: [{
                    data: [10, 25, 15, 35, 20, 45, 30],
                    borderColor: '#22c55e',
                    borderWidth: 4,
                    tension: 0.5,
                    pointRadius: 0,
                    fill: true,
                    backgroundColor: (context) => {
                        const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 150);
                        gradient.addColorStop(0, 'rgba(34, 197, 94, 0.2)');
                        gradient.addColorStop(1, 'transparent');
                        return gradient;
                    }
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { display: false }, x: { display: false } }
            }
        });

        // 🔥 Realtime Chart Pulse
        setInterval(() => {
            pulseChart.data.datasets[0].data.shift();
            pulseChart.data.datasets[0].data.push(Math.floor(Math.random() * 50) + 10);
            pulseChart.update('none');
        }, 2000);
    });
</script>

<?php include 'includes/footer.php'; ?>