<?php 
/**
 * Prime Admin - Advanced Order Intelligence Terminal (Fixed & Optimized)
 * Project: Turjo Site | Products Hub BD
 * Logic: Fixed Total Amount Mapping & Multi-Product Support [cite: 2026-02-11]
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. কোর ফাইল ইনক্লুড করা [cite: 2026-02-11]
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

/**
 * ২. সিকিউরিটি চেক
 */
if (!hasPermission($conn, 'order_manage.view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-[#0c0816] flex flex-col min-w-0 transition-all duration-300 font-['Plus_Jakarta_Sans']">
    
    <header class="h-24 flex items-center justify-between px-8 bg-white/80 dark:bg-[#0c0816]/80 backdrop-blur-xl border-b border-gray-200 dark:border-white/5 sticky top-0 z-20 shrink-0">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter italic">Order Intelligence</h2>
            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.25em] mt-1 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span> Realtime Customer Stream
            </p>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="flex flex-col items-end hidden md:block">
                <p class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider"><?php echo $_SESSION['admin_username'] ?? 'Turjo Admin'; ?></p>
                <p class="text-[9px] font-bold text-rose-500 uppercase tracking-widest">Site Controller</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 to-indigo-600 p-0.5 shadow-xl transform hover:rotate-6 transition-transform">
                <div class="w-full h-full bg-[#120d1d] rounded-[14px] flex items-center justify-center text-white font-black uppercase">
                    <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <div class="max-w-[1600px] mx-auto space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white dark:bg-white/[0.03] p-6 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Total Intelligence</span>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">
                        <?php echo $conn->query("SELECT id FROM orders")->num_rows; ?> <span class="text-xs text-gray-400 font-bold uppercase ml-2">Orders</span>
                    </h3>
                </div>
                <div class="bg-white dark:bg-white/[0.03] p-6 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest block mb-2">Pending Protocol</span>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">
                        <?php echo $conn->query("SELECT id FROM orders WHERE status='Pending'")->num_rows; ?> <span class="text-xs text-gray-400 font-bold uppercase ml-2">Needs Review</span>
                    </h3>
                </div>
                <div class="bg-white dark:bg-white/[0.03] p-6 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest block mb-2">Total Yield</span>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">
                        ৳<?php 
                        $rev = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status='Success' OR status='Completed'")->fetch_assoc();
                        echo number_format($rev['total'] ?? 0);
                        ?>
                    </h3>
                </div>
            </div>
            
            <div class="bg-white dark:bg-white/[0.02] rounded-[2.5rem] border border-gray-100 dark:border-white/5 shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] border-b dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02]">
                                <th class="py-7 px-8 italic tracking-tight"># Unique ID</th>
                                <th class="py-7 px-6 tracking-tight">Customer Profile</th>
                                <th class="py-7 px-6 tracking-tight">Payment Trace</th>
                                <th class="py-7 px-6 tracking-tight">Financials</th>
                                <th class="py-7 px-6 text-center tracking-tight">Status</th>
                                <th class="py-7 px-8 text-right tracking-tight">Command</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-white/5">
                            <?php
                            $sql = "SELECT o.*, u.username as acc_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 100";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0):
                                while($row = $result->fetch_assoc()):
                                    $st = $row['status'] ?? 'Pending';
                                    $st_color = ($st == 'Pending') ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
                                    if($st == 'Cancelled') $st_color = 'bg-rose-500/10 text-rose-500 border-rose-500/20';
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.01] transition-all group">
                                <td class="py-6 px-8">
                                    <span class="font-black text-gray-900 dark:text-gray-200 tracking-widest text-xs">#<?php echo $row['id']; ?></span>
                                </td>
                                <td class="py-6 px-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-gray-800 dark:text-gray-200 group-hover:text-rose-500 transition uppercase"><?php echo htmlspecialchars($row['customer_name']); ?></span>
                                        <span class="text-[10px] text-gray-500 font-bold flex items-center gap-1 mt-1">
                                            <i class="fas fa-phone text-[8px]"></i> <?php echo $row['customer_phone']; ?>
                                        </span>
                                        <span class="text-[9px] text-indigo-400 font-black uppercase mt-1 italic">Acc: <?php echo $row['acc_name'] ?? 'Guest'; ?></span>
                                    </div>
                                </td>
                                <td class="py-6 px-6">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?php echo $row['payment_method']; ?></span>
                                        <?php if(!empty($row['transaction_id'])): ?>
                                            <code class="text-[10px] font-black text-rose-500 bg-rose-500/10 px-3 py-1 rounded-xl border border-rose-500/20 w-fit uppercase"><?php echo $row['transaction_id']; ?></code>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-6 px-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-gray-900 dark:text-white">৳<?php echo number_format($row['total_amount']); ?></span>
                                    </div>
                                </td>
                                <td class="py-6 px-6 text-center">
                                    <div class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-wider border <?php echo $st_color; ?>">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-2 animate-pulse"></span>
                                        <?php echo $st; ?>
                                    </div>
                                </td>
                                <td class="py-6 px-8 text-right">
                                    <div class="flex justify-end gap-3 opacity-40 group-hover:opacity-100 transition-opacity">
                                        
                                        <?php if($st == 'Pending' || $st == 'Cancelled'): ?>
                                            <a href="handlers/status-handler.php?id=<?php echo $row['id']; ?>&status=Success" 
                                               class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center hover:scale-110 active:scale-90 transition-all shadow-lg shadow-emerald-500/30">
                                                <i class="fas fa-check text-xs"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if($st != 'Cancelled'): ?>
                                            <a href="handlers/status-handler.php?id=<?php echo $row['id']; ?>&status=Cancelled" 
                                               onclick="return confirm('আপনি কি নিশ্চিত যে এই অর্ডারটি বাতিল করতে চান?');"
                                               class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center hover:scale-110 active:scale-90 transition-all shadow-lg shadow-rose-500/30">
                                                <i class="fas fa-times text-xs"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="view-order.php?id=<?php echo $row['id']; ?>" 
                                           class="w-10 h-10 rounded-2xl bg-white dark:bg-white/10 text-gray-400 hover:text-rose-500 hover:bg-rose-500/10 flex items-center justify-center border border-gray-100 dark:border-white/5 transition-all">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="6" class="py-40 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-20">
                                        <i class="fas fa-satellite-dish text-6xl text-gray-400 mb-6 animate-pulse"></i>
                                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-[0.3em]">No Orders Found</h3>
                                    </div>
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