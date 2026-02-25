<?php 
/**
 * Prime Admin - Advanced Customer Profile Terminal (Neural 8.5)
 * Project: Turjo Site | Features: AI Insights, Wallet System, Communication & Order History
 * Design Update: Premium Spacing & Hijibiji-Free UI
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// ১. সিকিউরিটি গার্ড (Neural Security)
if (!hasPermission($conn, 'customers.view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) { header("Location: customers.php"); exit(); }

// ২. কাস্টমার ডাটা ও Analytics ফেচিং
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
          (SELECT SUM(total_amount) FROM orders WHERE user_id = u.id) as ltv,
          (SELECT AVG(total_amount) FROM orders WHERE user_id = u.id) as avg_order_val,
          (SELECT MAX(order_date) FROM orders WHERE user_id = u.id) as last_order
          FROM users u WHERE u.id = $user_id LIMIT 1";
$result = $conn->query($query);
$customer = $result->fetch_assoc();

if (!$customer) { header("Location: customers.php"); exit(); }

// ৩. AI Segmentation & Rank Logic
$segment = "New Customer"; $rank = "BRONZE";
$rank_color = "text-orange-500 bg-orange-500/10 border-orange-500/20";

if (($customer['ltv'] ?? 0) > 50000) { $rank = "PLATINUM"; $rank_color = "text-cyan-400 bg-cyan-400/10 border-cyan-400/20"; $segment = "VIP Spender"; }
elseif (($customer['ltv'] ?? 0) > 15000) { $rank = "GOLD"; $rank_color = "text-yellow-500 bg-yellow-500/10 border-yellow-500/20"; $segment = "High Spender"; }
elseif (($customer['total_orders'] ?? 0) > 5) { $rank = "SILVER"; $rank_color = "text-gray-400 bg-gray-400/10 border-gray-400/20"; $segment = "Returning User"; }

// Identity Lockdown Status
$isBlocked = ($customer['status'] == 'Blocked');

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    /* Spacing Fix: Hijibiji dur korar jonno */
    .tracking-neural { letter-spacing: 0.15em; }
    .breathable-padding { padding: 3rem !important; }
    .glass-panel { transition: transform 0.3s ease; }
    .glass-panel:hover { transform: translateY(-5px); }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col min-w-0 transition-all duration-300">
    
    <header class="h-24 flex items-center justify-between px-10 bg-[#110c1d]/80 backdrop-blur-md border-b border-white/5 sticky top-0 z-20 shrink-0">
        <div class="flex items-center gap-6">
            <a href="customers.php" class="w-12 h-12 rounded-2xl bg-white/5 text-gray-400 flex items-center justify-center hover:text-rose-500 transition-all shadow-lg border border-white/5">
                <i class="fas fa-chevron-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter leading-none">User Profile</h2>
                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-[0.4em] mt-2">Matrix Identity: #U-<?php echo $customer['id']; ?> / Turjo Site</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <button onclick="window.open('https://wa.me/<?php echo $customer['phone']; ?>')" class="w-12 h-12 rounded-[1.2rem] bg-green-500/5 text-green-500 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-xl"><i class="fab fa-whatsapp"></i></button>
            <button class="w-12 h-12 rounded-[1.2rem] bg-blue-500/5 text-blue-500 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all shadow-xl"><i class="fas fa-envelope"></i></button>
            <button class="px-8 py-3 rounded-[1.2rem] bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black uppercase tracking-widest transition-all shadow-2xl shadow-rose-600/30">Execute Control</button>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-10 <?php echo $isBlocked ? 'grayscale-[0.4]' : ''; ?>">
        <div class="max-w-[1500px] mx-auto space-y-12 pb-24">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                <div class="space-y-8">
                    <div class="glass-panel p-10 bg-[#110c1d] rounded-[3rem] border border-white/5 shadow-2xl relative overflow-hidden">
                        <div class="absolute top-6 right-6">
                            <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest <?php echo $rank_color; ?> border">
                                <?php echo $rank; ?> RANK
                            </span>
                        </div>
                        
                        <div class="flex flex-col items-center text-center mt-6">
                            <div class="w-28 h-28 rounded-[2.5rem] <?php echo $isBlocked ? 'bg-gray-800 text-gray-500' : 'bg-rose-600/10 text-rose-600'; ?> flex items-center justify-center font-black text-5xl border-2 border-rose-600/20 shadow-2xl mb-6 rotate-3">
                                <?php echo strtoupper(substr($customer['username'], 0, 1)); ?>
                            </div>
                            <h3 class="text-3xl font-black text-white uppercase tracking-wider leading-tight"><?php echo htmlspecialchars($customer['username']); ?></h3>
                            <p class="text-sm font-bold text-gray-500 mt-2 tracking-neural lowercase"><?php echo htmlspecialchars($customer['email']); ?></p>
                            
                            <div class="mt-6 flex gap-3">
                                <span class="px-3 py-1 bg-white/5 rounded-lg text-[9px] font-black text-gray-400 uppercase tracking-widest"><?php echo $segment; ?></span>
                                <span class="px-3 py-1 <?php echo $isBlocked ? 'bg-rose-500/10 text-rose-500 border-rose-500/20' : 'bg-green-500/5 text-green-500 border-green-500/10'; ?> rounded-lg text-[9px] font-black uppercase tracking-widest border">
                                    <?php echo $isBlocked ? 'Banned' : 'Verified'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="mt-12 pt-10 border-t border-white/5 grid grid-cols-2 gap-8 text-center">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Wallet Credit</p>
                                <p class="text-2xl font-black text-green-500 tracking-tighter">৳<?php echo number_format($customer['wallet_balance'] ?? 0); ?></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Reward Points</p>
                                <p class="text-2xl font-black text-yellow-500 tracking-tighter"><?php echo $customer['loyalty_points'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 bg-purple-600/5 border border-purple-500/10 rounded-[2.5rem] relative">
                        <h4 class="text-[10px] font-black text-purple-400 uppercase tracking-[0.3em] flex items-center gap-3 mb-6">
                            <i class="fas fa-brain text-sm"></i> Neural Predictor
                        </h4>
                        <p class="text-xs text-gray-400 font-bold leading-loose tracking-wide">
                            <?php if($isBlocked): ?>
                                IDENTITY IS UNDER LOCKDOWN. <br>MATRIX ACCESS REVOKED DUE TO SECURITY PROTOCOLS.
                            <?php elseif(($customer['total_orders'] ?? 0) > 0): ?>
                                HIGH RETENTION IDENTIFIED. <br>USER IS IN <span class="text-green-500 underline">TOP 5% ENGAGEMENT</span>.
                            <?php else: ?>
                                IDLE IDENTITY DETECTED. <br>SUGGEST RE-ENGAGEMENT ACTION.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="glass-panel p-8 bg-[#110c1d] rounded-[2rem] border border-white/5 shadow-xl">
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Lifetime LTV</p>
                            <h3 class="text-3xl font-black text-white tracking-tighter">৳<?php echo number_format($customer['ltv'] ?? 0); ?></h3>
                        </div>
                        <div class="glass-panel p-8 bg-[#110c1d] rounded-[2rem] border border-white/5 shadow-xl">
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Avg. Payload</p>
                            <h3 class="text-3xl font-black text-white tracking-tighter">৳<?php echo number_format($customer['avg_order_val'] ?? 0); ?></h3>
                        </div>
                        <div class="glass-panel p-8 bg-[#110c1d] rounded-[2rem] border border-white/5 shadow-xl">
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Last Sync</p>
                            <h3 class="text-base font-black text-rose-500 tracking-widest uppercase mt-1"><?php echo $customer['last_order'] ? date('d M, Y', strtotime($customer['last_order'])) : 'OFFLINE'; ?></h3>
                        </div>
                    </div>

                    <div class="glass-panel bg-[#110c1d] rounded-[3rem] border border-white/5 shadow-2xl overflow-hidden">
                        <div class="p-10 border-b border-white/5 flex justify-between items-center bg-white/[0.01]">
                            <h3 class="text-[11px] font-black text-white uppercase tracking-[0.3em]">Deployment Matrix</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <tbody class="divide-y divide-white/5">
                                    <?php 
                                    $orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC LIMIT 5");
                                    if($orders && $orders->num_rows > 0): while($o = $orders->fetch_assoc()): ?>
                                    <tr class="hover:bg-white/[0.02] transition">
                                        <td class="py-8 px-10">
                                            <span class="text-xs font-black text-gray-400 tracking-widest uppercase">#ORD-<?php echo $o['id']; ?></span>
                                        </td>
                                        <td class="py-8 px-8 font-black text-white text-base tracking-tighter">৳<?php echo number_format($o['total_amount']); ?></td>
                                        <td class="py-8 px-8">
                                            <span class="px-4 py-1.5 rounded-xl bg-green-500/10 text-green-500 text-[10px] font-black uppercase tracking-widest border border-green-500/20"><?php echo $o['status']; ?></span>
                                        </td>
                                        <td class="py-8 px-10 text-right">
                                            <a href="view-order.php?id=<?php echo $o['id']; ?>" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-gray-500 hover:text-rose-500 transition-all"><i class="fas fa-external-link-alt text-xs"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="py-24 text-center text-gray-600 text-[11px] font-black uppercase tracking-[0.4em]">No Active Deployments.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mt-12">
                <button class="p-10 bg-[#110c1d] rounded-[2.5rem] border border-white/5 text-center group hover:border-rose-500 transition-all duration-500 shadow-xl">
                    <i class="fas fa-key text-gray-600 group-hover:text-rose-500 mb-6 text-3xl"></i>
                    <p class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Reset Identity</p>
                </button>
                <button class="p-10 bg-[#110c1d] rounded-[2.5rem] border border-white/5 text-center group hover:border-amber-500 transition-all duration-500 shadow-xl">
                    <i class="fas fa-gift text-gray-600 group-hover:text-amber-500 mb-6 text-3xl"></i>
                    <p class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Assign Coupon</p>
                </button>
                <button class="p-10 bg-[#110c1d] rounded-[2.5rem] border border-white/5 text-center group hover:border-cyan-400 transition-all duration-500 shadow-xl">
                    <i class="fas fa-plus-circle text-gray-600 group-hover:text-cyan-400 mb-6 text-3xl"></i>
                    <p class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Adjust Wallet</p>
                </button>
                
                <button onclick="toggleAccess(<?php echo $customer['id']; ?>, '<?php echo $customer['status']; ?>')" 
                        class="p-10 <?php echo $isBlocked ? 'bg-green-600/5 border-green-500/10' : 'bg-rose-600/5 border-rose-500/10'; ?> rounded-[2.5rem] border text-center group hover:scale-[1.02] transition-all duration-500 shadow-xl">
                    <i class="fas <?php echo $isBlocked ? 'fa-user-check text-green-500' : 'fa-user-slash text-rose-500'; ?> mb-6 text-3xl"></i>
                    <p class="text-[10px] font-black <?php echo $isBlocked ? 'text-green-500' : 'text-rose-500'; ?> uppercase tracking-[0.2em]">
                        <?php echo $isBlocked ? 'Unban Access' : 'Ban Access'; ?>
                    </p>
                </button>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    /**
     * 🔐 Neural Access Toggle
     * Ajax-er maddhome Ban/Unban execute kore.
     */
    function toggleAccess(id, currentStatus) {
        const action = (currentStatus === 'Active') ? 'Blocked' : 'Active';
        const actionText = (action === 'Blocked') ? 'Ban this Identity?' : 'Restore User Access?';
        const confirmBtn = (action === 'Blocked') ? '#e11d48' : '#10b981';

        Swal.fire({
            title: actionText,
            text: `Matrix protocol will be updated for user #${id}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmBtn,
            cancelButtonColor: '#1e162e',
            confirmButtonText: `Yes, Execute ${action}`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'handlers/customer-control.php',
                    type: 'POST',
                    data: { user_id: id, action: action },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                Swal.fire('Identity Updated!', data.message, 'success').then(() => {
                                    location.reload(); 
                                });
                            } else {
                                Swal.fire('Protocol Failed', data.message, 'error');
                            }
                        } catch(e) {
                            Swal.fire('Matrix Error', 'Invalid response from terminal.', 'error');
                        }
                    }
                });
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>