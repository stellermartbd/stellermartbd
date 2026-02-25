<?php
/**
 * Real-time Review Management
 * Project: Turjo Site | Built for Performance
 * Logic: Dashboard Stats & Live Moderation [cite: 2026-02-21]
 */

// ১. কোর ফাইল ও সিকিউরিটি কনফিগারেশন
require_once __DIR__ . '/../core/db.php'; 
require_once __DIR__ . '/../core/functions.php'; 
require_once __DIR__ . '/../core/csrf.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// লগইন ভ্যালিডেশন
if (!isset($_SESSION['admin_logged_in'])) { 
    header('Location: login.php'); 
    exit; 
}

// ২. ডাটা ফেচিং ও স্ট্যাটিস্টিকস [cite: 2026-02-21]
$reviews = [];
$pending_count = 0;
$average_rating = 0;

try {
    // সব রিভিউ ফেচ করা (প্রোডাক্টের নাম সহ) [cite: 2026-02-21]
    $sql = "SELECT r.*, p.name as product_name 
            FROM product_reviews r 
            LEFT JOIN products p ON r.product_id = p.id 
            ORDER BY r.id DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        while($row = $result->fetch_assoc()) { 
            $reviews[] = $row; 
            if($row['status'] == 'pending') $pending_count++; 
        }
    }
    
    // এভারেজ রেটিং ক্যালকুলেশন (শুধুমাত্র অ্যাপ্রুভড রিভিউ থেকে) [cite: 2026-02-21]
    $avg_res = $conn->query("SELECT AVG(rating) as avg_r FROM product_reviews WHERE status = 'approved'");
    $avg_data = $avg_res->fetch_assoc();
    $average_rating = number_format($avg_data['avg_r'] ?? 0, 1);

} catch (Exception $e) {
    // ডাটাবেস এরর হ্যান্ডেলিং
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0">
    <header class="h-24 flex items-center justify-between px-10 bg-white/80 dark:bg-theme-card/80 backdrop-blur-2xl border-b dark:border-theme-border sticky top-0 z-40 shrink-0">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-3 h-3 rounded-full bg-green-500 animate-ping"></div>
                <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight uppercase">Reviews Center</h2>
            </div>
            <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.3em]">Customer Feedback - **Turjo Site**</p>
        </div>

        <div class="flex gap-4">
            <div class="text-right">
                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Average Rating</p>
                <div class="flex items-center gap-1 text-yellow-500">
                    <i class="fas fa-star text-xs"></i>
                    <span class="text-lg font-black dark:text-white"><?= $average_rating; ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-10 custom-scrollbar">
        <div class="max-w-[1400px] mx-auto space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-theme-card p-6 rounded-[2rem] border dark:border-theme-border">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Reviews</p>
                    <h4 class="text-2xl font-black dark:text-white"><?= count($reviews); ?></h4>
                </div>
                <div class="bg-white dark:bg-theme-card p-6 rounded-[2rem] border dark:border-theme-border border-l-4 border-l-yellow-500">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pending</p>
                    <h4 class="text-2xl font-black dark:text-white"><?= $pending_count; ?></h4>
                </div>
            </div>

            <div class="bg-white dark:bg-theme-card rounded-[3rem] border dark:border-theme-border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-theme-dark/50">
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-widest">Customer & Product</th>
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-widest">Feedback</th>
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">Rating</th>
                                <th class="px-10 py-8 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Moderation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-theme-border">
                            <?php if (!empty($reviews)): ?>
                                <?php foreach($reviews as $rev): ?>
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-theme-dark/40 transition-all group">
                                    <td class="px-10 py-7">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center font-black text-rose-500 uppercase text-xs">
                                                <?= substr($rev['user_name'] ?? 'U', 0, 1); ?>
                                            </div>
                                            <div>
                                                <span class="block font-black text-sm dark:text-white"><?= htmlspecialchars($rev['user_name'] ?? 'Unknown'); ?></span>
                                                <span class="text-[10px] text-rose-500 font-bold uppercase tracking-tighter italic"><?= htmlspecialchars($rev['product_name'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-7">
                                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed max-w-xs line-clamp-2">
                                            "<?= htmlspecialchars($rev['comment']); ?>"
                                        </p>
                                        <div class="flex items-center gap-3 mt-2">
                                            <span class="text-[9px] text-gray-400 uppercase"><?= date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                            <?php if(!empty($rev['review_image'])): ?>
                                                <span class="text-[9px] font-black text-blue-500 uppercase flex items-center gap-1 cursor-pointer hover:underline" onclick="window.open('../public/uploads/reviews/<?= $rev['review_image']; ?>')">
                                                    <i class="fas fa-image"></i> View Photo
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-10 py-7 text-center">
                                        <div class="flex items-center justify-center gap-1 text-yellow-500 text-[10px]">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="<?= $i <= $rev['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td class="px-10 py-7 text-right">
                                        <div class="flex justify-end gap-2">
                                            <?php if($rev['status'] == 'pending'): ?>
                                                <button onclick="manageReview(<?= $rev['id']; ?>, 'approve')" class="px-4 py-2 bg-green-500/10 text-green-500 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-green-500 hover:text-white transition-all">Approve</button>
                                            <?php else: ?>
                                                <span class="px-4 py-2 bg-blue-500/10 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest">Live</span>
                                            <?php endif; ?>
                                            
                                            <button onclick="manageReview(<?= $rev['id']; ?>, 'delete')" class="p-2.5 bg-gray-100 dark:bg-theme-dark text-gray-400 hover:text-rose-500 rounded-xl transition-all">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-10 py-32 text-center">
                                        <i class="fas fa-comment-slash text-5xl text-gray-200 mb-4 block"></i>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">No feedback yet</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// রিভিউ মডারেশন লজিক [cite: 2026-02-21]
function manageReview(id, action) {
    let confirmMsg = action === 'approve' ? "Approve this feedback for the public site?" : "Permanently delete this feedback and its assets?";
    if (confirm(confirmMsg)) {
        // হ্যান্ডলার ফাইলে রিকোয়েস্ট পাঠানো [cite: 2026-02-21]
        window.location.href = 'handlers/review-mod.php?id=' + id + '&action=' + action;
    }
}

// SweetAlert সাকসেস টোস্ট [cite: 2026-02-21]
const urlParams = new URLSearchParams(window.location.search);
if(urlParams.get('status')) {
    const status = urlParams.get('status');
    Swal.fire({
        title: status === 'approved' ? 'APPROVED' : 'DELETED',
        text: status === 'approved' ? 'Review is now live on the product page.' : 'Review has been permanently removed.',
        icon: 'success',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
}
</script>

<?php include 'includes/footer.php'; ?>