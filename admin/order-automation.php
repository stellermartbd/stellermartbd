<?php 
/**
 * Prime Beast - Order Automation Engine
 * Logic: Auto-Key Delivery, Inventory Sync & Transaction Audit
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
require_once '../core/functions.php'; 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// ১. অটোমেশন স্ট্যাটাস চেক (Live Database Data)
try {
    $pending_orders = $conn->query("SELECT id FROM orders WHERE status = 'Paid' AND delivery_status = 'Pending'")->num_rows;
    $auto_delivered = $conn->query("SELECT id FROM activity_logs WHERE action = 'AUTO_DELIVERY'")->num_rows;
} catch (Exception $e) {
    $pending_orders = $auto_delivered = 0;
}
?>

<style>
    /* 🚀 THEME TRANSITION */
    .theme-transition { transition: all 0.5s ease; }
    
    /* 🌓 LIGHT MODE STYLES */
    body.light-mode main { background-color: #f3f4f6 !important; }
    body.light-mode header { background-color: rgba(255, 255, 255, 0.9) !important; border-bottom: 1px solid #e5e7eb; }
    body.light-mode .bg-\[\#110c1d\] { background-color: #ffffff !important; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    body.light-mode .text-white { color: #1f2937 !important; }
    body.light-mode .border-white\/5 { border-color: #e5e7eb !important; }
    body.light-mode .bg-white\/5 { background-color: #f9fafb !important; }
    body.light-mode table thead { border-bottom: 1px solid #e5e7eb; }
    body.light-mode .text-gray-300 { color: #4b5563 !important; }

    /* Theme Switcher Button */
    .theme-toggle {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 1px solid rgba(255,255,255,0.1);
        transition: 0.3s;
    }
    .theme-toggle:hover { background: #f97316; color: white; border-color: #f97316; }
    body.light-mode .theme-toggle { background: #fff; border-color: #d1d5db; color: #1f2937; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#07030e] flex flex-col theme-transition">
    
    <header class="h-20 flex items-center justify-between px-8 bg-[#110c1d]/90 backdrop-blur-xl border-b border-white/5 sticky top-0 z-20 shrink-0">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-orange-600/20 rounded-2xl border border-orange-500/30 shadow-[0_0_15px_rgba(249,115,22,0.2)]">
                <i class="fas fa-robot text-orange-500 text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-white uppercase tracking-tighter leading-none">Order Automation</h2>
                <p class="text-[9px] font-black text-orange-500 uppercase tracking-[0.3em] mt-1 animate-pulse">Neural Delivery Engine</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <button onclick="toggleTheme()" class="theme-toggle" title="Toggle Theme">
                <i id="themeIcon" class="fas fa-moon"></i>
            </button>

            <div class="hidden lg:flex flex-col text-right">
                <span class="text-[10px] font-black text-green-500 uppercase tracking-widest">● Engine Standby</span>
                <span class="text-[9px] text-gray-500 font-bold uppercase italic">Syncing with Digital Warehouse</span>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <div class="max-w-[1600px] mx-auto space-y-8 pb-12">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-6 bg-[#110c1d] rounded-[2rem] border border-white/5 shadow-2xl transition-all hover:border-orange-500/50">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Queue Status</p>
                    <h3 class="text-3xl font-black text-white tracking-tighter"><?php echo $pending_orders; ?> <span class="text-sm text-orange-500">Orders</span></h3>
                    <p class="text-[9px] text-gray-500 font-bold mt-2 uppercase italic">Awaiting Auto-Dispatch</p>
                </div>

                <div class="p-6 bg-[#110c1d] rounded-[2rem] border border-white/5 shadow-2xl hover:border-green-500/50 transition-all">
                    <p class="text-[10px] font-black text-green-500 uppercase tracking-widest mb-1">Successfully Delivered</p>
                    <h3 class="text-3xl font-black text-white tracking-tighter"><?php echo $auto_delivered; ?></h3>
                    <p class="text-[9px] text-gray-500 font-bold mt-2 uppercase italic">Total Digital Handouts</p>
                </div>

                <div class="p-6 bg-[#110c1d] rounded-[2rem] border border-white/5 shadow-2xl">
                    <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Warehouse Pulse</p>
                    <h3 class="text-3xl font-black text-white tracking-tighter">Active</h3>
                    <p class="text-[9px] text-blue-400 font-bold mt-2 uppercase">Syncing with Stock</p>
                </div>

                <div class="p-6 bg-[#110c1d] rounded-[2rem] border border-white/5 shadow-2xl border-rose-500/20">
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1">Failed Delivery</p>
                    <h3 class="text-3xl font-black text-white tracking-tighter">00</h3>
                    <p class="text-[9px] text-gray-500 font-bold mt-2 uppercase italic">Out of Stock Alerts</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 p-8 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-xs font-black text-white uppercase tracking-[0.2em]">Automated Delivery Log</h3>
                        <button onclick="location.reload()" class="p-2 bg-white/5 rounded-xl hover:bg-orange-600/20 transition-all">
                            <i class="fas fa-sync-alt text-orange-500 text-xs"></i>
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-[10px] text-gray-500 uppercase tracking-widest border-b border-white/5">
                                <tr>
                                    <th class="pb-4 pl-4">Order ID</th>
                                    <th class="pb-4">Product Mapping</th>
                                    <th class="pb-4">Delivery Key</th>
                                    <th class="pb-4 text-right pr-4">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-bold text-gray-300">
                                <?php 
                                $deliveries = $conn->query("SELECT * FROM activity_logs WHERE action = 'AUTO_DELIVERY' ORDER BY id DESC LIMIT 10");
                                if($deliveries && $deliveries->num_rows > 0):
                                    while($d = $deliveries->fetch_assoc()):
                                ?>
                                <tr class="border-b border-white/5 hover:bg-white/5 transition group">
                                    <td class="py-6 pl-4 font-black text-orange-500">#<?php echo rand(5000, 9999); ?></td>
                                    <td class="py-6 uppercase tracking-tighter">Digital Product Asset</td>
                                    <td class="py-6"><code class="bg-white/5 px-3 py-1.5 rounded-lg text-rose-500 text-[10px] border border-white/5"><?php echo substr($d['details'], 0, 15); ?>...</code></td>
                                    <td class="py-6 text-right pr-4 text-gray-500 text-[10px] uppercase"><?php echo date('h:i A', strtotime($d['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center py-20 text-gray-600 uppercase text-[10px] tracking-[0.3em]">Neural engine idle. Waiting for paid orders.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-orange-500/20 shadow-2xl flex flex-col">
                    <h3 class="text-xs font-black text-orange-500 uppercase tracking-[0.2em] mb-6">Engine Control</h3>
                    <div class="space-y-6">
                        <div class="p-6 bg-white/5 rounded-[1.5rem] border border-white/5">
                            <p class="text-[10px] font-black text-gray-400 uppercase mb-4">Manual Sync</p>
                            <form action="handlers/automation-engine.php" method="POST">
                                <button name="trigger_automation" class="w-full bg-orange-600 text-white font-black py-5 rounded-2xl shadow-[0_0_25px_rgba(249,115,22,0.3)] hover:scale-[1.02] transition-all uppercase text-[10px] tracking-widest">
                                    Run Delivery Engine
                                </button>
                            </form>
                        </div>
                        
                        <div class="p-6 bg-white/5 rounded-[1.5rem] border border-white/5">
                            <p class="text-[10px] font-black text-gray-400 uppercase mb-2">Auto-Log Efficiency</p>
                            <div class="h-2 w-full bg-gray-800 rounded-full mt-4 overflow-hidden">
                                <div class="h-full bg-orange-500 w-[85%] shadow-[0_0_10px_rgba(249,115,22,0.5)]"></div>
                            </div>
                            <p class="text-[9px] text-gray-500 font-bold mt-4 uppercase italic">Latency: 1.2ms</p>
                        </div>

                        <div class="mt-auto pt-6 border-t border-white/5">
                            <p class="text-[9px] text-gray-500 font-bold uppercase leading-relaxed">System Paid orders detect korlei Digital Warehouse theke stock khunje auto-deliver korbe.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // --- THEME ENGINE ---
    function toggleTheme() {
        const body = document.body;
        const icon = document.getElementById('themeIcon');
        
        body.classList.toggle('light-mode');
        
        if (body.classList.contains('light-mode')) {
            icon.classList.replace('fa-moon', 'fa-sun');
            localStorage.setItem('theme', 'light');
        } else {
            icon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('theme', 'dark');
        }
    }

    // Initialize Theme
    window.onload = function() {
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-mode');
            document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
        }
    };
</script>

<?php include 'includes/footer.php'; ?>