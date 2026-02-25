<?php 
/**
 * Prime Admin - Secured Beast Dashboard
 * Project: Turjo Site | Products Hub BD
 * Logic: Show cinematic fade-in ONLY after first login with Revenue Filtering
 */

// ১. এরর হ্যান্ডলিং ও সেশন স্টার্ট
ini_set('display_errors', 1); 
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. কোর ফাইল কানেকশন
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// ৩. ডাটাবেস ভেরিয়েবল ইনিশিয়ালাইজেশন
$total_products = 0; $low_stock_count = 0; $total_orders_count = 0;
$pending_orders = 0; $completed_orders = 0; $rejected_orders = 0;
$total_revenue = 0; 

// --- ফিল্টারিং লজিক (নতুন সংযোজন) ---
$filter = $_GET['filter'] ?? 'lifetime';
$date_condition = "";

if ($filter == '1day') {
    $date_condition = " AND order_date >= NOW() - INTERVAL 1 DAY";
} elseif ($filter == '7days') {
    $date_condition = " AND order_date >= NOW() - INTERVAL 7 DAY";
} elseif ($filter == '30days') {
    $date_condition = " AND order_date >= NOW() - INTERVAL 30 DAY";
}
// ----------------------------------------

// প্রোডাক্ট কাউন্ট কোয়েরি
$res_p = $conn->query("SELECT COUNT(*) as total FROM products");
if($res_p) $total_products = $res_p->fetch_assoc()['total'];

// লো স্টক এলার্ট কোয়েরি
$res_ls = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 5");
if($res_ls) $low_stock_count = $res_ls->fetch_assoc()['total'];

// অর্ডার স্ট্যাটাস ডিস্ট্রিবিউশন
$res_o = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
if($res_o) {
    while($row = $res_o->fetch_assoc()) {
        $st = $row['status'];
        $count = (int)$row['count'];
        $total_orders_count += $count;
        if($st == 'Pending') $pending_orders = $count;
        if($st == 'Completed') $completed_orders = $count;
        if($st == 'Rejected') $rejected_orders = $count;
    }
}

// টোটাল রেভিনিউ ক্যালকুলেশন (আপনার ডাটাবেস স্ট্রাকচার অনুযায়ী ফিল্টারসহ)
$rev_query = "SELECT SUM((total_price + shipping_cost) - discount_amount) as total 
              FROM orders 
              WHERE status = 'Completed' $date_condition";
$res_rev = $conn->query($rev_query);
if($res_rev) $total_revenue = $res_rev->fetch_assoc()['total'] ?? 0;

// ৪. রিসেন্ট ইনভেন্টরি প্রিভিউ ডাটা
$top_products_result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 5");

// চার্ট ডাটা (অরজিনাল ডাটাবেস থেকে ডাইনামিক ফিল্টারিং)
$chart_query = "SELECT DATE(order_date) as date, SUM((total_price + shipping_cost) - discount_amount) as daily_total 
                FROM orders WHERE status = 'Completed' $date_condition 
                GROUP BY DATE(order_date) ORDER BY order_date ASC";
$res_chart = $conn->query($chart_query);
$chart_labels = [];
$chart_data = [];

while($row = $res_chart->fetch_assoc()) {
    $chart_labels[] = date('M d', strtotime($row['date']));
    $chart_data[] = (float)$row['daily_total'];
}

// যদি ডাটা না থাকে ডিফল্ট ভ্যালু যাতে চার্ট খালি না দেখায়
if(empty($chart_labels)) { 
    $chart_labels = ['No Data']; 
    $chart_data = [0]; 
}

/**
 * 🔥 ইন্টেলিজেন্ট এনিমেশন ট্রিপ-ওয়্যার
 */
$show_animation = false;
if (isset($_SESSION['show_entrance_anim'])) {
    $show_animation = true;
    unset($_SESSION['show_entrance_anim']); 
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    html { background-color: #020205 !important; } 
    
    <?php if($show_animation): ?>
    #beast-fade-mask {
        position: fixed; inset: 0; z-index: 99999;
        background: #020205; opacity: 1;
        transition: opacity 3s cubic-bezier(0.4, 0, 0.2, 1), visibility 3s;
    }
    body.is-ready #beast-fade-mask { opacity: 0; visibility: hidden; }
    body:not(.is-ready) { overflow: hidden; }
    <?php endif; ?>
</style>

<?php if($show_animation): ?>
<div id="beast-fade-mask"></div>
<?php endif; ?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0 transition-all duration-300">
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-theme-dark/80 backdrop-blur-md border-b border-gray-200 dark:border-[#251d33] sticky top-0 z-20 shrink-0">
        <div>
            <h2 id="page-title" class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight">Dashboard</h2>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Command Center</p>
        </div>
        
        <div class="flex items-center gap-4">
            <select onchange="location.href='?filter=' + this.value" class="bg-white dark:bg-theme-card border border-gray-200 dark:border-theme-border text-[10px] font-black uppercase rounded-lg px-3 py-2 outline-none shadow-sm transition-all focus:ring-2 focus:ring-rose-500 cursor-pointer">
                <option value="lifetime" <?php echo $filter == 'lifetime' ? 'selected' : ''; ?>>Lifetime</option>
                <option value="1day" <?php echo $filter == '1day' ? 'selected' : ''; ?>>Last 1 Day</option>
                <option value="7days" <?php echo $filter == '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30days" <?php echo $filter == '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
            </select>

            <button id="theme-toggle" class="w-10 h-10 rounded-full bg-white dark:bg-theme-card border border-gray-200 dark:border-theme-border text-gray-500 dark:text-yellow-400 flex items-center justify-center transition shadow-sm">
                <i class="fas fa-moon dark:hidden"></i><i class="fas fa-sun hidden dark:block"></i>
            </button>
            <div class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold shadow-lg uppercase">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar">
        <div class="p-8 space-y-8 w-full max-w-[1600px] mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="col-span-1 lg:col-span-2 glass-panel p-6 bg-white dark:bg-theme-card border border-gray-100 dark:border-theme-border rounded-3xl shadow-sm">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <i class="fas fa-bolt text-yellow-500"></i> Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <?php if (hasPermission($conn, 'Product Manage', 'add')): ?>
                        <a href="add-product.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 hover:bg-rose-100 dark:hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition border border-rose-100 dark:border-rose-500/10 text-center">
                            <i class="fas fa-box-open text-xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-tighter">Add Item</span>
                        </a>
                        <?php endif; ?>
                        <a href="products.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 transition border border-blue-100 dark:border-blue-500/10 text-center">
                            <i class="fas fa-list text-xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-tighter">Inventory</span>
                        </a>
                        <?php if (hasPermission($conn, 'Order Manage', 'view')): ?>
                        <a href="orders.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-purple-50 dark:bg-purple-500/10 hover:bg-purple-100 dark:hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 transition border border-purple-100 dark:border-purple-500/10 text-center">
                            <i class="fas fa-shopping-cart text-xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-tighter">Orders</span>
                        </a>
                        <?php endif; ?>
                        <a href="customers.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-orange-50 dark:bg-orange-500/10 hover:bg-orange-100 dark:hover:bg-orange-500/20 text-orange-600 dark:text-orange-400 transition border border-orange-100 dark:border-orange-500/10 text-center">
                            <i class="fas fa-users text-xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-tighter">Customers</span>
                        </a>
                        <a href="reports.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-green-50 dark:bg-green-500/10 hover:bg-green-100 dark:hover:bg-green-500/20 text-green-600 dark:text-green-400 transition border border-green-100 dark:border-green-500/10 text-center">
                            <i class="fas fa-chart-line text-xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-tighter">Reports</span>
                        </a>
                        <a href="settings.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-100 dark:border-gray-700 transition hover:bg-gray-100 text-center">
                            <i class="fas fa-cog text-xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-tighter">Settings</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="glass-panel p-6 flex justify-between items-center bg-white dark:bg-theme-card rounded-3xl border dark:border-theme-border shadow-sm">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1"><?php echo ucfirst($filter); ?> Revenue</p>
                            <h3 class="text-2xl font-black text-gray-800 dark:text-white">৳<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center text-xl">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="glass-panel p-6 flex justify-between items-center bg-white dark:bg-theme-card rounded-3xl border dark:border-theme-border shadow-sm">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Low Stock Alerts</p>
                            <h3 class="text-2xl font-black text-rose-500"><?php echo $low_stock_count; ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-rose-50 dark:bg-rose-500/10 text-rose-500 rounded-xl flex items-center justify-center text-xl">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 glass-panel p-6 bg-white dark:bg-theme-card rounded-3xl border dark:border-theme-border shadow-sm">
                    <div class="h-64 w-full">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                <div class="glass-panel p-6 flex flex-col justify-between bg-white dark:bg-theme-card rounded-3xl border dark:border-theme-border shadow-sm">
                    <div class="h-48 relative flex justify-center items-center my-4">
                        <canvas id="orderStatusChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $total_orders_count; ?></span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase">Total Orders</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> Pending</div>
                            <span><?php echo $pending_orders; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Completed</div>
                            <span><?php echo $completed_orders; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-6 bg-white dark:bg-theme-card rounded-3xl border dark:border-theme-border shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Inventory Review</h3>
                    <a href="products.php" class="text-xs font-black text-rose-600 uppercase tracking-widest">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b dark:border-theme-border bg-gray-50/50 dark:bg-white/5">
                                <th class="p-4 pl-6">Product Info</th>
                                <th class="p-4">Price</th>
                                <th class="p-4">Stock</th>
                                <th class="p-4 text-right pr-6">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $top_products_result->fetch_assoc()): ?>
                            <tr class="border-b dark:border-theme-border hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="py-3 pl-6 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-white/10 p-1 border dark:border-transparent">
                                        <img src="../public/uploads/<?php echo $row['image']; ?>" class="w-full h-full object-contain">
                                    </div>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200"><?php echo $row['name']; ?></span>
                                </td>
                                <td class="py-3 text-sm font-bold text-gray-600 dark:text-gray-400"><?php echo formatPrice($row['price']); ?></td>
                                <td class="py-3">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo ($row['stock'] < 5) ? 'bg-rose-500/10 text-rose-500' : 'bg-green-500/10 text-green-500'; ?>">
                                        <?php echo $row['stock']; ?> Stock
                                    </span>
                                </td>
                                <td class="py-3 text-right pr-6">
                                    <a href="edit-product.php?id=<?php echo $row['id']; ?>" class="text-gray-400 hover:text-rose-600"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.body.classList.add('is-ready');
        }, 100); 
    });

    // Revenue Chart Logic with Dynamic Data
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Revenue',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#3b82f6',
                borderWidth: 2, tension: 0.4, fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                pointRadius: 3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { display: true }, x: { grid: { display: false } } } }
    });

    // Doughnut Chart Logic
    const orderCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(orderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Completed', 'Rejected'],
            datasets: [{
                data: [<?php echo $pending_orders; ?>, <?php echo $completed_orders; ?>, <?php echo $rejected_orders; ?>],
                backgroundColor: ['#fbbf24', '#22c55e', '#ef4444'],
                borderWidth: 0, cutout: '80%'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
</script>

<?php include 'includes/footer.php'; ?>