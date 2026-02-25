<?php 
/**
 * Prime Beast - Financial Intelligence Hub (V61.0)
 * Project: Turjo Site | Products Hub BD
 * Features: Multi-page PDF Export, Neural UI, Real-time Sync
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// 🛡️ Access Control Node
if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php?error=Access+Denied");
    exit();
}

/** * ✅ 1. Advanced Analytics Fetching (Null-Safe) */
$total_revenue = 0; $total_orders = 0; $avg_order = 0;

$res_r = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'Completed'");
if($res_r) $total_revenue = $res_r->fetch_assoc()['total'] ?? 0;

$res_o = $conn->query("SELECT COUNT(*) as total FROM orders");
if($res_o) $total_orders = $res_o->fetch_assoc()['total'] ?? 0;

if($total_orders > 0) $avg_order = $total_revenue / $total_orders;

// Recent Sales Matrix Stream (Fetched 30 nodes to test multi-page PDF)
$recent_sales = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 30");

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    :root { --accent: #f59e0b; --danger: #e11d48; --panel: #110c1d; --bg: #0a0514; }
    
    /* 🚀 Original Professional Styling */
    .glass-card { background: var(--panel); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 2.5rem; transition: 0.4s; }
    .glass-card:hover { border-color: var(--danger); transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
    .text-glow { text-shadow: 0 0 15px rgba(225, 29, 72, 0.5); }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--danger); border-radius: 10px; }

    /* 📄 Multi-Page PDF Printing Logic */
    @media print {
        @page { size: A4; margin: 15mm; }
        body { background: white !important; color: black !important; overflow: visible !important; }
        .no-print, header, .sidebar, button { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; width: 100% !important; display: block !important; }
        .flex-1 { overflow: visible !important; display: block !important; }
        .glass-card { 
            border: 1px solid #eee !important; 
            background: white !important; 
            color: black !important; 
            box-shadow: none !important; 
            margin-bottom: 30px !important;
            page-break-inside: avoid; /* Prevents splitting a card across pages */
        }
        .text-white, h2, h3, h4, span, p { color: black !important; text-shadow: none !important; }
        .text-gray-500, .text-gray-600 { color: #666 !important; }
        canvas { max-width: 100% !important; height: auto !important; }
        table { width: 100% !important; border-collapse: collapse !important; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        .bg-white\/5, .bg-white\/\[0.02\] { background: #f9f9f9 !important; }
    }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col p-8 transition-all duration-500">
    
    <header class="h-24 flex items-center justify-between px-10 bg-[#110c1d]/90 backdrop-blur-xl border-b border-white/5 sticky top-0 z-30 shrink-0 rounded-3xl mb-12 no-print">
        <div class="flex items-center gap-6">
            <div class="relative p-4 bg-[#0a0514] rounded-2xl border border-white/10 shadow-2xl">
                <i class="fas fa-chart-line text-rose-500 text-2xl animate-pulse"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter leading-none text-glow">Reports Hub</h2>
                <div class="flex items-center gap-2 mt-3">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-ping"></span>
                    <p class="text-[9px] font-black text-gray-500 uppercase tracking-[0.4em]">Neural Analytics Active</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <button onclick="window.print()" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition shadow-2xl shadow-rose-600/30 flex items-center gap-2">
                <i class="fas fa-file-pdf"></i> Export Matrix
            </button>
            <div class="flex items-center gap-4 border-l border-white/5 pl-6">
                <div class="text-right hidden sm:block">
                    <span class="block text-xs font-black text-white uppercase tracking-tight"><?php echo $_SESSION['admin_username']; ?></span>
                    <span class="block text-[8px] text-rose-500 font-bold uppercase tracking-widest">Supreme Unit</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-rose-600 p-[2px]">
                    <div class="w-full h-full bg-[#0a0514] rounded-[14px] flex items-center justify-center font-black text-white uppercase italic text-xs">
                        <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-12 pb-24 pr-2 mt-4">
        
        <section class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="glass-card p-10">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Total Revenue</p>
                <h3 class="text-4xl font-black text-white text-glow">৳<?php echo number_format($total_revenue); ?></h3>
                <div class="mt-4 h-1 w-full bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-rose-600 w-[70%] shadow-[0_0_10px_#e11d48]"></div>
                </div>
            </div>
            <div class="glass-card p-10 border-blue-500/10">
                <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-2">Orders Processed</p>
                <h3 class="text-4xl font-black text-white"><?php echo $total_orders; ?></h3>
                <p class="text-[9px] text-gray-600 font-bold mt-3 uppercase tracking-tighter">Synchronized Nodes</p>
            </div>
            <div class="glass-card p-10 border-green-500/10">
                <p class="text-[10px] font-black text-green-500 uppercase tracking-widest mb-2">Avg. Order Value</p>
                <h3 class="text-4xl font-black text-white">৳<?php echo number_format($avg_order); ?></h3>
                <p class="text-[9px] text-gray-600 font-bold mt-3 uppercase tracking-tighter">Profit Matrix</p>
            </div>
            <div class="glass-card p-10 border-purple-500/10">
                <p class="text-[10px] font-black text-purple-500 uppercase tracking-widest mb-2">Conversion Node</p>
                <h3 class="text-4xl font-black text-white">12.5%</h3>
                <p class="text-[9px] text-gray-600 font-bold mt-3 uppercase tracking-tighter">Neural Traffic</p>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 glass-card p-12">
                <div class="flex justify-between items-center mb-12">
                    <h4 class="text-sm font-black text-white uppercase tracking-[0.3em]">Revenue Growth Matrix</h4>
                    <span class="text-[9px] bg-rose-600/10 text-rose-600 px-3 py-1 rounded-full font-black uppercase">Live Analytics</span>
                </div>
                <div class="h-[400px] w-full">
                    <canvas id="revenueGrowthChart"></canvas>
                </div>
            </div>

            <div class="glass-card p-12 flex flex-col items-center">
                <h4 class="text-sm font-black text-white uppercase tracking-[0.3em] mb-12">Saturation Impact</h4>
                <div class="h-72 w-full relative flex items-center justify-center">
                    <canvas id="categorySalesChart"></canvas>
                </div>
                <div class="w-full space-y-6 mt-12">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-500 border-b border-white/5 pb-4">
                        <span>Digital Asset Node</span><span class="text-rose-500">65%</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-500 border-b border-white/5 pb-4">
                        <span>Physical Matrix</span><span class="text-blue-500">25%</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-500">
                        <span>Other Nodes</span><span class="text-amber-500">10%</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="glass-card overflow-hidden mb-20 shadow-2xl">
            <div class="p-10 border-b border-white/5 flex justify-between items-center bg-white/[0.01]">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Full Transaction Intelligence Ledger</h4>
                <span class="text-[9px] text-gray-600 font-black uppercase">Total Records: <?php echo $recent_sales->num_rows; ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/[0.02] text-[11px] text-gray-500 uppercase font-black tracking-widest border-b border-white/5">
                        <tr>
                            <th class="p-10 pl-12">Protocol ID</th>
                            <th>Identity Node</th>
                            <th>Value Matrix</th>
                            <th class="text-right pr-12">Intensity Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs font-bold text-gray-400 divide-y divide-white/[0.02]">
                        <?php if($recent_sales && $recent_sales->num_rows > 0): while($row = $recent_sales->fetch_assoc()): ?>
                        <tr class="hover:bg-white/[0.04] transition-all group">
                            <td class="p-8 pl-12">
                                <div class="flex items-center gap-5">
                                    <div class="w-10 h-10 rounded-2xl bg-white/5 flex items-center justify-center text-rose-500 border border-white/5 text-[10px] font-black group-hover:bg-rose-600 group-hover:text-white transition-all">
                                        #
                                    </div>
                                    <span class="text-white uppercase tracking-tighter">TRX-<?php echo $row['id']; ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="text-gray-300 uppercase"><?php echo htmlspecialchars($row['customer_name'] ?? 'Authorized Guest'); ?></span>
                                <p class="text-[9px] text-gray-600 mt-1 italic"><?php echo $row['created_at']; ?></p>
                            </td>
                            <td>
                                <span class="text-white font-black text-lg">৳<?php echo number_format($row['total_amount']); ?></span>
                                <p class="text-[8px] text-gray-700 uppercase font-black tracking-widest mt-1">Verified Node</p>
                            </td>
                            <td class="text-right pr-12">
                                <span class="px-5 py-2 bg-green-500/10 text-green-500 rounded-full text-[9px] font-black uppercase border border-green-500/20 shadow-lg shadow-green-500/5">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="p-32 text-center uppercase text-[12px] tracking-[0.6em] text-gray-600 font-black">No Matrix Data Synchronized</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-8 border-t border-white/5 text-center no-print">
                <button class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:underline">Load More Matrix Nodes</button>
            </div>
        </section>
    </div>
</main>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 📊 Revenue Growth Chart (Original Professional Style)
    const growthCtx = document.getElementById('revenueGrowthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                data: [4500, 5900, 8000, 8100, 5600, 9500, 12000],
                backgroundColor: '#e11d48',
                borderRadius: 20,
                barThickness: 25
            }]
        },
        options: { 
            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, 
            scales: { 
                y: { grid: { color: 'rgba(255, 255, 255, 0.03)', borderDash: [5, 5] }, ticks: { color: '#475569', font: { weight: 'bold', size: 10 } } }, 
                x: { grid: { display: false }, ticks: { color: '#475569', font: { weight: 'bold', size: 10 } } } 
            } 
        }
    });

    // 🍩 Category Impact Chart (Doughnut)
    const catCtx = document.getElementById('categorySalesChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: ['Digital', 'Physical', 'Other'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: ['#e11d48', '#3b82f6', '#f59e0b'],
                borderWidth: 0, cutout: '88%', hoverOffset: 20
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
</script>

<?php include 'includes/footer.php'; ?>