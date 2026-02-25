<?php 
/**
 * Prime Admin - Stock Control Terminal
 * Project: Turjo Site | Products Hub BD
 * Logic: Neural Permission Guard & Supreme Bypass
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. ডাটাবেস, ফাংশন ও হেডার-সাইডবার ইনক্লুড করা
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

/**
 * 🔥 Module Level Security Guard
 * Stock Control-er jonno 'stock_control.view' পারমিশন চেক করছে।
 * সুপ্রিম এডমিনরা (turjo/turjo0424) অটো বাইপাস পাবে।
 */
if (!hasPermission($conn, 'stock_control.view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// ডাটাবেস থেকে লো স্টক কাউন্ট আনা
$low_stock_res = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
$low_stock_data = $low_stock_res->fetch_assoc();
$low_stock_count = $low_stock_data['total'] ?? 0; 
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0 transition-all duration-300">
    
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-theme-dark/80 backdrop-blur-md border-b border-gray-200 dark:border-[#251d33] sticky top-0 z-20 shrink-0">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight leading-none">Stock Control</h2>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Inventory Management & Tracking</p>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="hidden md:flex bg-rose-500/10 border border-rose-500/20 px-4 py-2 rounded-xl flex-col justify-center">
                <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest leading-none mb-1">Low Stock Alert</p>
                <p class="text-sm font-black text-gray-800 dark:text-white leading-none">
                    <?php echo $low_stock_count; ?> <span class="text-[9px] font-bold text-gray-400 uppercase ml-0.5">Items</span>
                </p>
            </div>
            <div class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold shadow-lg uppercase">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar">
        <div class="p-8 w-full max-w-[1600px] mx-auto space-y-6">
            
            <div class="glass-panel bg-white dark:bg-theme-card rounded-3xl border border-gray-100 dark:border-theme-border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b dark:border-theme-border bg-gray-50/50 dark:bg-white/5">
                                <th class="py-6 px-8">Product Details</th>
                                <th class="py-6 px-6">SKU Code</th>
                                <th class="py-6 px-6">Quantity</th>
                                <th class="py-6 px-6">Stock Level</th>
                                <th class="py-6 px-8 text-right">Quick Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-theme-border align-middle">
                            <?php
                            $sql = "SELECT id, name, sku, stock, image FROM products ORDER BY stock ASC";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0):
                                while($row = $result->fetch_assoc()):
                                    // স্টক লেভেল অনুযায়ী কালার লজিক
                                    $stockColor = "text-green-500";
                                    $statusBg = "bg-green-500/10 border-green-500/20";
                                    $statusText = "In Stock";
                                    $progressColor = "bg-green-500";
                                    $percent = min(($row['stock'] / 50) * 100, 100);

                                    if($row['stock'] <= 0) {
                                        $stockColor = "text-rose-500";
                                        $statusBg = "bg-rose-500/10 border-rose-500/20";
                                        $statusText = "Out of Stock";
                                        $progressColor = "bg-rose-500";
                                    } elseif($row['stock'] < 10) {
                                        $stockColor = "text-yellow-500";
                                        $statusBg = "bg-yellow-500/10 border-yellow-500/20";
                                        $statusText = "Low Stock";
                                        $progressColor = "bg-yellow-500";
                                    }
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition group">
                                <td class="py-5 px-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 p-1 border dark:border-transparent shrink-0">
                                            <img src="../public/uploads/<?php echo $row['image']; ?>" class="w-full h-full object-contain">
                                        </div>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-rose-500 transition"><?php echo $row['name']; ?></span>
                                    </div>
                                </td>
                                <td class="py-5 px-6 text-[11px] font-black text-gray-400 tracking-widest uppercase">
                                    <?php echo $row['sku'] ?? 'N/A'; ?>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="text-sm font-black text-gray-800 dark:text-white"><?php echo $row['stock']; ?> <span class="text-[9px] text-gray-400 uppercase ml-0.5">Units</span></span>
                                </td>
                                <td class="py-5 px-6 min-w-[150px]">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider w-fit border <?php echo $statusBg . ' ' . $stockColor; ?>">
                                            <?php echo $statusText; ?>
                                        </div>
                                        <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full <?php echo $progressColor; ?>" style="width: <?php echo $percent; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-8 text-right">
                                    <form action="handlers/inventory-handler.php" method="POST" class="flex items-center justify-end gap-2">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="new_stock" placeholder="Qty" class="w-16 bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-theme-border rounded-lg px-2 py-1.5 text-[11px] font-bold text-gray-800 dark:text-white focus:outline-none focus:border-rose-500 transition">
                                        
                                        <button type="submit" name="update_stock" 
                                            onclick="<?php echo hasPermission($conn, 'stock_control.edit') ? '' : "event.preventDefault(); Swal.fire('Denied', 'Matrix Restricted: No Edit Access!', 'error')" ?>"
                                            class="w-9 h-9 flex items-center justify-center bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-theme-border text-gray-400 hover:text-white hover:bg-rose-600 rounded-xl transition-all duration-300 shadow-sm">
                                            <i class="fas fa-sync-alt text-[10px]"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" class="py-20 text-center opacity-20">
                                    <i class="fas fa-warehouse text-5xl mb-4"></i>
                                    <h3 class="text-xl font-black uppercase tracking-widest">Inventory Empty</h3>
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

<?php include 'includes/footer.php'; ?>