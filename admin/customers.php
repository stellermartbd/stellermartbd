<?php 
/**
 * Prime Admin - Users Center Beast Terminal (V18.0)
 * Layout: Advanced Spacing, Top Search (Lowered), & Compact Matrix
 * Project: Turjo Site | Logic: AI Segmentation & Global Stats
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. কোর ইঞ্জিন এবং সিকিউরিটি লোড
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

/**
 * 🔥 Neural Security Guard
 */
if (!hasPermission($conn, 'customers.view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

// ২. Advanced Matrix Filters Logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$sort_by = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'newest';

// Base Intelligence Query
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
          (SELECT SUM(total_amount) FROM orders WHERE user_id = u.id) as ltv
          FROM users u WHERE 1=1";

if(!empty($search)) $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%')";
if(!empty($status_filter)) $query .= " AND u.status = '$status_filter'";

if($sort_by == 'highest_spend') $query .= " ORDER BY ltv DESC";
elseif($sort_by == 'most_orders') $query .= " ORDER BY total_orders DESC";
else $query .= " ORDER BY u.id DESC";

$result = $conn->query($query);
$total_users = ($result) ? $result->num_rows : 0;

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    :root { 
        --accent-rose: #e11d48; 
        --panel-bg: #110c1d; 
        --matrix-bg: #0a0514; 
    }

    /* 🔎 Search Terminal: Lowered Section */
    .lowered-search-section {
        margin-top: 40px; /* Header theke niche soraono hoyeche */
        padding: 30px 45px;
        background: rgba(17, 12, 29, 0.5);
        border-radius: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .beast-filter-input {
        background: #000 !important; /* Sposto korar jonno pure dark */
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
        border-radius: 1.2rem !important;
        padding: 14px 22px !important;
        font-weight: 700;
        font-size: 13px;
        transition: 0.4s ease;
    }
    
    /* Stats Box Grid */
    .stats-matrix-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 25px;
        margin-top: 35px;
    }

    .compact-stats-box {
        background: var(--panel-bg);
        padding: 30px;
        border-radius: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.03);
        text-align: center;
        transition: 0.3s ease;
    }
    .compact-stats-box:hover { border-color: rgba(225, 29, 72, 0.3); transform: scale(1.02); }

    /* Compact Table Styling */
    .user-matrix-table td { padding: 25px 25px !important; vertical-align: middle; }
    .user-matrix-table th { 
        padding: 20px 25px !important; 
        letter-spacing: 0.25em; 
        color: #475569; 
        font-size: 9px; 
        font-weight: 900; 
        text-transform: uppercase; 
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .status-pill {
        font-size: 8px;
        font-weight: 900;
        padding: 5px 12px;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .active-node { color: #10b981; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.15); }
    .blocked-node { color: #e11d48; background: rgba(225, 29, 72, 0.08); border: 1px solid rgba(225, 29, 72, 0.15); }

    /* Avatar Matrix */
    .avatar-unit {
        width: 60px;
        height: 60px;
        border-radius: 1.5rem;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col min-w-0 transition-all duration-700">
    
    <header class="h-24 flex items-center justify-between px-12 bg-[#110c1d] border-b border-white/5 sticky top-0 z-50 shrink-0">
        <div class="flex items-center gap-8">
            <div class="p-4 bg-rose-600/10 rounded-2xl border border-rose-600/20">
                <i class="fas fa-users-cog text-rose-600 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter">Users Center</h2>
                <p class="text-[9px] font-bold text-gray-500 uppercase tracking-[0.4em] mt-2">Matrix v18.0 / Turjo Site</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <button onclick="location.href='export-users.php'" class="bg-white/5 border border-white/10 px-7 py-3 rounded-2xl text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-rose-500 transition-all">
                Export Database
            </button>
            <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black shadow-2xl uppercase border border-white/10">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar px-12 pb-32">
        
        <section class="lowered-search-section max-w-[1800px] mx-auto shadow-2xl">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-6 relative">
                    <i class="fas fa-search absolute left-7 top-1/2 -translate-y-1/2 text-rose-500 opacity-60"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="" class="beast-filter-input w-full pl-16">
                </div>
                <div class="md:col-span-2">
                    <select name="status" class="beast-filter-input w-full cursor-pointer appearance-none">
                        <option value="">Status: All Nodes</option>
                        <option value="Active" <?php if($status_filter == 'Active') echo 'selected'; ?>>Active</option>
                        <option value="Blocked" <?php if($status_filter == 'Blocked') echo 'selected'; ?>>Blocked</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="sort" class="beast-filter-input w-full cursor-pointer appearance-none">
                        <option value="newest">Latest Joined</option>
                        <option value="highest_spend" <?php if($sort_by == 'highest_spend') echo 'selected'; ?>>Top Payload</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl h-full uppercase text-[10px] tracking-widest transition-all shadow-lg">Run Sync</button>
                </div>
            </form>
        </section>

        <div class="max-w-[1800px] mx-auto space-y-12">
            
            <section class="stats-matrix-grid">
                <div class="compact-stats-box bg-rose-600/5 border-rose-500/10">
                    <h4 class="text-3xl font-black text-white"><?php echo $total_users; ?></h4>
                    <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-2">Active Entities</p>
                </div>
                <div class="compact-stats-box group cursor-pointer">
                    <i class="fas fa-envelope-open-text text-gray-600 group-hover:text-rose-500 mb-4 text-2xl transition-all"></i>
                    <p class="text-[9px] font-black text-white uppercase">Matrix Email</p>
                </div>
                <div class="compact-stats-box group cursor-pointer">
                    <i class="fas fa-ticket-alt text-gray-600 group-hover:text-amber-500 mb-4 text-2xl transition-all"></i>
                    <p class="text-[9px] font-black text-white uppercase">Bulk Coupon</p>
                </div>
                <div class="compact-stats-box group cursor-pointer">
                    <i class="fas fa-database text-gray-600 group-hover:text-cyan-400 mb-4 text-2xl transition-all"></i>
                    <p class="text-[9px] font-black text-white uppercase">Matrix Backup</p>
                </div>
            </section>

            <div class="glass-panel bg-[#110c1d]/60 rounded-[3.5rem] border border-white/5 shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse user-matrix-table">
                        <thead>
                            <tr class="bg-white/[0.01]">
                                <th class="pl-12">User Identity</th>
                                <th>Economic Payload (LTV)</th>
                                <th class="text-center">Matrix Status</th>
                                <th class="text-right pr-12">Command Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): 
                                $isBlocked = ($row['status'] == 'Blocked');
                                $ltv = $row['ltv'] ?? 0;
                            ?>
                            <tr class="hover:bg-white/[0.02] transition-all group <?php echo $isBlocked ? 'opacity-40 grayscale' : ''; ?>">
                                <td class="pl-12">
                                    <div class="flex items-center gap-6">
                                        <div class="avatar-unit <?php echo $isBlocked ? 'bg-gray-800' : 'bg-rose-600/10 text-rose-600'; ?> font-black group-hover:rotate-3 transition-all duration-500">
                                            <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span class="text-base font-black text-white group-hover:text-rose-500 transition block"><?php echo htmlspecialchars($row['username']); ?></span>
                                            <span class="text-[10px] text-gray-500 font-bold block lowercase opacity-60"><?php echo htmlspecialchars($row['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="space-y-1">
                                        <p class="text-base font-black text-white tracking-tighter">৳<?php echo number_format($ltv); ?></p>
                                        <p class="text-[8px] font-bold text-gray-600 uppercase"><?php echo $row['total_orders']; ?> Deployments Sync</p>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill <?php echo $isBlocked ? 'blocked-node' : 'active-node'; ?>">
                                        <?php echo $isBlocked ? 'Banned Matrix' : 'Identity Active'; ?>
                                    </span>
                                </td>
                                <td class="text-right pr-12">
                                    <div class="flex items-center justify-end gap-5">
                                        <button onclick="window.open('https://wa.me/<?php echo $row['phone']; ?>')" class="w-12 h-12 rounded-2xl bg-green-500/5 text-green-500 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-lg border border-green-500/10"><i class="fab fa-whatsapp text-lg"></i></button>
                                        <a href="customer-details.php?id=<?php echo $row['id']; ?>" class="w-12 h-12 rounded-2xl bg-white/5 text-gray-400 hover:text-blue-500 flex items-center justify-center border border-white/5 shadow-lg"><i class="fas fa-eye text-lg"></i></a>
                                        
                                        <button onclick="toggleAccess(<?php echo $row['id']; ?>, '<?php echo $row['status']; ?>')" 
                                                class="w-12 h-12 rounded-2xl <?php echo $isBlocked ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-rose-600/5 text-rose-500 border-rose-500/15'; ?> flex items-center justify-center hover:scale-110 transition-all shadow-xl">
                                            <i class="fas <?php echo $isBlocked ? 'fa-user-check' : 'fa-user-slash'; ?> text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" class="py-56 text-center opacity-30 text-white font-black uppercase tracking-[0.5em]">Zero Database Matrices Found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    /**
     * 🔐 Master Access Toggling Protocol
     */
    function toggleAccess(id, currentStatus) {
        const action = (currentStatus === 'Active' || currentStatus === '') ? 'Blocked' : 'Active';
        const actionText = (action === 'Blocked') ? 'LOCK DOWN IDENTITY?' : 'RESTORE MATRIX ACCESS?';
        
        Swal.fire({
            title: actionText,
            text: `Updating user network status for ID #${id}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: (action === 'Blocked') ? '#e11d48' : '#10b981',
            confirmButtonText: `YES, EXECUTE`
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
                                location.reload(); 
                            } else {
                                Swal.fire('Sync Error', data.message, 'error');
                            }
                        } catch(e) { console.log('Protocol Integrity Failure.'); }
                    }
                });
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>