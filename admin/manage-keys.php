<?php
/**
 * Prime Admin - Digital Warehouse (Fixed)
 * Fix: Undefined variable $low_stock_q resolved
 */

// ১. সেশন এবং এরর হ্যান্ডলিং
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. কোর ফাইল লোড
require_once '../core/db.php';
require_once '../core/functions.php'; 

// ৩. ডাটাবেস কানেকশন এবং পারমিশন চেক
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

// পারমিশন চেক (যদি আপনার সিস্টেম এ এটি থাকে)
if (function_exists('hasPermission') && !hasPermission($conn, 'manage_keys.view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';

$msg = $_GET['success'] ?? '';
$err = $_GET['error'] ?? '';

// ৪. সার্চ এবং ফিল্টার লজিক
$search = $_GET['search'] ?? '';
$filter_pid = $_GET['product_id'] ?? '';

$where_clause = "1=1";
if($search) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where_clause .= " AND (pk.content LIKE '%$safe_search%' OR p.name LIKE '%$safe_search%')";
}
if($filter_pid) {
    $safe_pid = (int)$filter_pid;
    $where_clause .= " AND pk.product_id = $safe_pid";
}

// ৫. স্ট্যাটাস কাউন্টস এবং ভেরিয়েবল ইনিশিয়ালাইজেশন (FIXED HERE)
$available_count = 0;
$sold_count = 0;
$low_stock_q = null; // ভেরিয়েবল আগে ডিক্লেয়ার করা হলো যাতে এরর না দেয়

try {
    // Available Count
    $ac_res = $conn->query("SELECT id FROM product_keys WHERE status = 'Available'");
    if($ac_res) $available_count = $ac_res->num_rows;

    // Sold Count
    $sc_res = $conn->query("SELECT id FROM product_keys WHERE status = 'Sold'");
    if($sc_res) $sold_count = $sc_res->num_rows;
    
    // Low Stock Alert Query (Less than 5 keys)
    $low_stock_q = $conn->query("
        SELECT p.name, COUNT(pk.id) as count 
        FROM products p 
        LEFT JOIN product_keys pk ON p.id = pk.product_id AND pk.status = 'Available'
        WHERE p.is_digital = 1 
        GROUP BY p.id 
        HAVING count < 5
    ");

} catch (Exception $e) {
    // এরর হলে সব ০ থাকবে, সাইট ক্রাশ করবে না
    $available_count = 0; 
    $sold_count = 0;
}
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-[#0b0f19] flex flex-col min-w-0 transition-all duration-300">
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-[#111827]/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700/50 sticky top-0 z-20 shrink-0 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight leading-none">Digital Warehouse</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Live Inventory Control</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="hidden md:flex gap-6 text-right border-r border-gray-200 dark:border-gray-700 pr-6">
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase leading-none mb-1">In Stock</p>
                    <p class="text-lg font-black text-emerald-500 leading-none"><?php echo number_format($available_count); ?></p>
                </div>
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase leading-none mb-1">Dispatched</p>
                    <p class="text-lg font-black text-blue-500 leading-none"><?php echo number_format($sold_count); ?></p>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white flex items-center justify-center font-bold shadow-lg shadow-rose-500/30 uppercase text-sm border border-white/20">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-8">
        <div class="w-full max-w-[1600px] mx-auto space-y-8">

            <?php if($msg): ?>
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-xl text-xs font-bold uppercase tracking-wide flex items-center gap-3 shadow-sm">
                    <i class="fas fa-check-circle text-lg"></i> <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <?php if($low_stock_q && $low_stock_q->num_rows > 0): ?>
            <div class="p-5 bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 rounded-2xl">
                <div class="flex items-center gap-3 mb-3">
                    <i class="fas fa-exclamation-triangle text-amber-500"></i>
                    <h3 class="text-xs font-black text-amber-600 dark:text-amber-500 uppercase tracking-widest">Low Stock Alert</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php while($ls = $low_stock_q->fetch_assoc()): ?>
                        <span class="px-3 py-1.5 bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 rounded-lg text-[10px] font-bold uppercase border border-amber-200 dark:border-amber-500/30">
                            <?php echo htmlspecialchars($ls['name']); ?>: <span class="text-red-500"><?php echo $ls['count']; ?> left</span>
                        </span>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8 pb-12">
                
                <div class="xl:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-[#1f2937] border border-gray-100 dark:border-gray-700 rounded-3xl shadow-sm p-6 sticky top-6">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-500 flex items-center justify-center text-lg">
                                <i class="fas fa-database"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-widest">Key Injector</h3>
                                <p class="text-[9px] text-gray-400 font-bold">Bulk upload format: 1 key per line</p>
                            </div>
                        </div>

                        <form action="handlers/key-handler.php" method="POST" class="space-y-5">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Select Product</label>
                                <select name="product_id" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3.5 focus:outline-none focus:border-indigo-500 dark:text-gray-200 text-xs font-bold transition-all">
                                    <option value="">-- Choose Digital Item --</option>
                                    <?php
                                    $digi_prods = $conn->query("SELECT id, name FROM products WHERE is_digital = 1 ORDER BY name ASC");
                                    if($digi_prods) while($p = $digi_prods->fetch_assoc()) {
                                        echo "<option value='{$p['id']}'>".htmlspecialchars($p['name'])."</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Key Data</label>
                                <textarea name="keys_data" rows="10" required placeholder="Example:&#10;USER:PASS&#10;LICENSE-KEY-123&#10;TOKEN-XYZ" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500 dark:text-gray-200 text-xs font-mono custom-scrollbar leading-relaxed"></textarea>
                            </div>

                            <button type="submit" name="add_keys" 
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-xl shadow-lg shadow-indigo-600/20 transition-all hover:translate-y-[-2px] flex items-center justify-center gap-2 uppercase tracking-widest text-[10px]">
                                <i class="fas fa-plus-circle"></i> Inject to Stock
                            </button>
                        </form>
                    </div>
                </div>

                <div class="xl:col-span-3 space-y-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-[#1f2937] p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                        <form class="flex w-full md:w-auto gap-3 flex-1" method="GET">
                            <div class="relative flex-1 md:w-64">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search keys..." class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl pl-10 py-3 text-xs font-bold dark:text-white focus:ring-2 ring-indigo-500/50">
                            </div>
                            <select name="product_id" onchange="this.form.submit()" class="bg-gray-50 dark:bg-gray-900 border-none rounded-xl px-4 py-3 text-xs font-bold dark:text-white focus:ring-2 ring-indigo-500/50">
                                <option value="">All Products</option>
                                <?php
                                // Reset pointer for second usage
                                if($digi_prods) {
                                    $digi_prods->data_seek(0);
                                    while($p = $digi_prods->fetch_assoc()) {
                                        $sel = ($p['id'] == $filter_pid) ? 'selected' : '';
                                        echo "<option value='{$p['id']}' $sel>".htmlspecialchars($p['name'])."</option>";
                                    }
                                }
                                ?>
                            </select>
                            <a href="manage-keys.php" class="p-3 bg-gray-100 dark:bg-gray-700 rounded-xl text-gray-500 hover:text-red-500 transition-colors" title="Reset Filters"><i class="fas fa-sync-alt"></i></a>
                        </form>
                        
                        <button class="hidden md:flex px-5 py-3 bg-emerald-500/10 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-all items-center gap-2 border border-emerald-500/20">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </button>
                    </div>

                    <div class="bg-white dark:bg-[#1f2937] border border-gray-100 dark:border-gray-700 rounded-[2rem] shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="p-5 pl-8">Product Name</th>
                                        <th class="p-5">Key Content</th>
                                        <th class="p-5 text-center">Status</th>
                                        <th class="p-5 text-center">Added On</th>
                                        <th class="p-5 text-right pr-8">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <?php
                                    $keys = $conn->query("SELECT pk.*, p.name FROM product_keys pk JOIN products p ON pk.product_id = p.id WHERE $where_clause ORDER BY pk.id DESC LIMIT 50");
                                    
                                    if($keys && $keys->num_rows > 0):
                                        while($k = $keys->fetch_assoc()):
                                            $is_sold = ($k['status'] == 'Sold');
                                            $badge_cls = $is_sold 
                                                ? 'bg-red-50 text-red-500 border-red-100 dark:bg-red-500/10 dark:border-red-500/20' 
                                                : 'bg-emerald-50 text-emerald-500 border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20';
                                    ?>
                                    <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all">
                                        <td class="p-5 pl-8">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 text-xs">
                                                    <i class="fas fa-gamepad"></i>
                                                </div>
                                                <span class="font-bold text-gray-700 dark:text-gray-200 text-xs"><?php echo htmlspecialchars($k['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="p-5">
                                            <div class="flex items-center gap-2 max-w-[250px]">
                                                <code class="font-mono text-[11px] text-gray-500 dark:text-gray-400 truncate bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded select-all" id="key-<?php echo $k['id']; ?>">
                                                    <?php echo htmlspecialchars($k['content']); ?>
                                                </code>
                                                <button onclick="copyToClipboard('key-<?php echo $k['id']; ?>')" class="text-gray-300 hover:text-indigo-500 transition-colors" title="Copy Key">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="p-5 text-center">
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase border <?php echo $badge_cls; ?>">
                                                <?php echo $k['status']; ?>
                                            </span>
                                        </td>
                                        <td class="p-5 text-center text-[10px] text-gray-400 font-bold font-mono">
                                            <?php echo date('d M Y', strtotime($k['added_date'])); ?>
                                        </td>
                                        <td class="p-5 text-right pr-8">
                                            <?php if(!$is_sold): ?>
                                            <a href="handlers/key-handler.php?action=delete&id=<?php echo $k['id']; ?>" onclick="return confirm('Delete this key?')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all" title="Delete Key">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </a>
                                            <?php else: ?>
                                                <span class="text-[10px] text-gray-300 italic">Sold Out</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                        <tr>
                                            <td colspan="5" class="py-16 text-center">
                                                <div class="flex flex-col items-center opacity-50">
                                                    <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">No keys found in inventory</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-100 dark:border-gray-700 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            Showing last 50 entries
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(copyText).then(function() {
        alert("Copied to clipboard: " + copyText);
    });
}
</script>

<?php include 'includes/footer.php'; ?>