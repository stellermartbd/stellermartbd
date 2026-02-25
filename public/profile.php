<?php 
/**
 * Project: Turjo Site - Modern User Dashboard (Final Edition)
 * Features: Mobile Optimized, Enhanced Header Cart Trigger, Support Popup, No Italics
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. ডাটাবেস এবং পাথ সেটআপ
require_once __DIR__ . '/../core/db.php'; 

// Link fix korar jonno Base URL dhora
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/"; 

// ২. লগইন চেক করা
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ৩. ইউজারের তথ্য ফেচ করা
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// ৪. মোট অর্ডারের সংখ্যা বের করা
$order_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_count = $order_stmt->get_result()->fetch_assoc()['total'] ?? 0;

include __DIR__ . '/../templates/header.php'; 
?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* গ্লোবাল স্টাইল: সব ধরনের ইটালিক ও কঠিন ডিজাইন বাদ */
    * { font-style: normal !important; }
    body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
    
    .glass-card { background: #ffffff; border: 1px solid #eef2f6; border-radius: 30px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    .profile-banner { background: linear-gradient(135deg, #a855f7 0%, #f43f5e 50%, #f97316 100%); height: 160px; border-radius: 25px; }
    .menu-item { transition: 0.3s; border-bottom: 1px solid #f1f5f9; cursor: pointer; }
    .menu-item:hover { background: #f9fafb; }
    .order-row { transition: 0.2s; cursor: pointer; border-radius: 15px; }
    .order-row:hover { background: #f1f5f9; }
</style>

<div class="min-h-screen py-8 px-4">
    <div class="max-w-4xl mx-auto">
        
        <div class="glass-card overflow-hidden mb-6">
            <div class="profile-banner m-4 relative"></div>
            
            <div class="px-8 pb-8 -mt-14 relative z-10 text-center md:text-left">
                <div class="flex flex-col md:flex-row items-end gap-6 mb-8">
                    <div class="w-28 h-28 rounded-3xl bg-white p-1 shadow-xl mx-auto md:mx-0">
                        <div class="w-full h-full bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-[22px] flex items-center justify-center overflow-hidden border-4 border-white">
                            <span class="text-3xl font-black text-white uppercase"><?php echo substr($user_data['username'] ?? 'U', 0, 1); ?></span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight"><?php echo htmlspecialchars($user_data['username'] ?? ''); ?></h1>
                        <p class="text-slate-400 font-bold text-sm lowercase"><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col items-center">
                        <span class="text-lg font-black text-slate-800">Active</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Cart Status</span>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col items-center">
                        <span class="text-lg font-black text-slate-800"><?php echo $order_count; ?></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Orders</span>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col items-center">
                        <span class="text-lg font-black text-slate-800">Elite</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Rank</span>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col items-center">
                        <span class="text-lg font-black text-slate-800">Live</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Identity</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card overflow-hidden mb-6">
            <div class="p-6 border-b border-slate-50">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Account Control</h3>
            </div>
            
            <div class="flex flex-col">
                <div onclick="toggleTab('orders-list')" class="menu-item p-6 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center"><i class="fas fa-receipt"></i></div>
                            <div class="text-left">
                                <p class="text-sm font-black text-slate-800 uppercase">Your Orders</p>
                                <p class="text-[10px] text-slate-400 font-bold">Check your purchases</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down text-slate-300 text-xs transition-transform" id="order-icon"></i>
                    </div>

                    <div id="orders-list" class="hidden mt-4 pt-4 border-t border-slate-50 space-y-3">
                        <?php 
                        $order_q = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5");
                        $order_q->bind_param("i", $user_id);
                        $order_q->execute();
                        $orders = $order_q->get_result();

                        if($orders->num_rows > 0):
                            while($row = $orders->fetch_assoc()):
                                $st = $row['status'];
                                $color = ($st == 'Cancelled') ? "bg-rose-100 text-rose-600" : (($st == 'Pending') ? "bg-amber-100 text-amber-600" : "bg-emerald-100 text-emerald-600");
                        ?>
                        <div onclick="event.stopPropagation(); showOrderDetails(<?php echo $row['id']; ?>)" class="order-row p-4 border border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-4 text-[11px]">
                                <span class="font-black text-slate-300">#<?php echo $row['id']; ?></span>
                                <span class="font-black text-slate-800 uppercase">Total: ৳<?php echo number_format($row['total_amount']); ?></span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter <?php echo $color; ?>"><?php echo $st; ?></span>
                        </div>
                        <?php endwhile; else: ?>
                            <p class="py-6 text-center text-slate-400 font-black uppercase text-xs">No Orders Found</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div onclick="triggerHeaderCart()" class="menu-item p-6 flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center"><i class="fas fa-shopping-bag"></i></div>
                        <div class="text-left">
                            <p class="text-sm font-black text-slate-800 uppercase">Your Cart</p>
                            <p class="text-[10px] text-slate-400 font-bold">View your bag</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                </div>

                <div onclick="openSupport()" class="menu-item p-6 flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center"><i class="fas fa-headset"></i></div>
                        <div class="text-left">
                            <p class="text-sm font-black text-slate-800 uppercase">Customer Support</p>
                            <p class="text-[10px] text-slate-400 font-bold">Talk to us</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <a href="logout.php" class="w-full bg-slate-900 text-white py-4 rounded-2xl flex items-center justify-center gap-3 font-black uppercase text-xs tracking-[0.2em] hover:bg-rose-600 transition shadow-lg">
                <i class="fas fa-power-off"></i> Log Out
            </a>
        </div>
    </div>
</div>

<div id="orderModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-[35px] shadow-2xl overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center bg-slate-50">
            <h2 class="text-sm font-black text-slate-800 uppercase">Full Order Details</h2>
            <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-white border flex items-center justify-center text-slate-400 hover:text-rose-500 transition"><i class="fas fa-times"></i></button>
        </div>
        <div id="modalContent" class="p-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
            <div class="flex justify-center py-10"><i class="fas fa-spinner fa-spin text-slate-300 text-3xl"></i></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showOrderDetails(orderId) {
        const modal = document.getElementById('orderModal');
        const content = document.getElementById('modalContent');
        modal.classList.remove('hidden');
        content.innerHTML = '<div class="flex justify-center py-10"><i class="fas fa-spinner fa-spin text-slate-300 text-3xl"></i></div>';
        
        fetch('handlers/order-details-fetch.php?order_id=' + orderId)
            .then(res => res.text())
            .then(data => { content.innerHTML = data; });
    }

    function closeModal() { document.getElementById('orderModal').classList.add('hidden'); }
    window.onclick = function(event) { if (event.target == document.getElementById('orderModal')) closeModal(); }

    function triggerHeaderCart() { if (typeof toggleCart === "function") toggleCart(); else window.location.href = 'cart.php'; }

    function openSupport() {
        Swal.fire({
            title: 'Contact Support',
            text: 'Choose contact method:',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'WhatsApp',
            cancelButtonText: 'Email',
            confirmButtonColor: '#25D366',
            cancelButtonColor: '#083b66',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) window.open('https://wa.me/8801847853867', '_blank'); 
            else if (result.dismiss === Swal.DismissReason.cancel) window.location.href = 'mailto:webemail369@gmail.com'; 
        });
    }

    function toggleTab(id) {
        const el = document.getElementById(id);
        const icon = document.getElementById('order-icon');
        el.classList.toggle('hidden');
        if (icon) icon.style.transform = !el.classList.contains('hidden') ? 'rotate(180deg)' : 'rotate(0deg)';
    }
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>