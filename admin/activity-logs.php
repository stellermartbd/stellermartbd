<?php 
/**
 * Prime Beast - Activity Hub & Security Audit (V68.0)
 * Project: Turjo Site | Products Hub BD
 * Logic: Neural Tracking Matrix | Features: Original UI & Multi-page PDF
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// 🛡️ Access Control Node
if (!hasPermission($conn, 'bulk.manage')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

/** * ✅ 1. Advanced Analytics Fetching with SQL Stability */
function fetchBeastCount($conn, $query) {
    $res = $conn->query($query);
    return ($res && $row = $res->fetch_assoc()) ? (int)$row['total'] : 0;
}

$total_logs = fetchBeastCount($conn, "SELECT COUNT(*) as total FROM activity_logs");
$security_alerts = fetchBeastCount($conn, "SELECT COUNT(*) as total FROM activity_logs WHERE status IN ('failed', 'danger', 'attack')");
$logs_24h = fetchBeastCount($conn, "SELECT COUNT(*) as total FROM activity_logs WHERE created_at >= NOW() - INTERVAL 1 DAY");

/** * ✅ 2. Search & Multi-Layer Filtering Logic */
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$intensity = isset($_GET['intensity']) ? mysqli_real_escape_string($conn, $_GET['intensity']) : 'all';
$action_filter = isset($_GET['action_filter']) ? mysqli_real_escape_string($conn, $_GET['action_filter']) : 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "(action_type LIKE '%$search%' OR details LIKE '%$search%' OR ip_address LIKE '%$search%' OR admin_id LIKE '%$search%')";
}
if ($intensity !== 'all') { $where_clauses[] = "status = '$intensity'"; }
if ($action_filter !== 'all') {
    if($action_filter === 'logins') {
        $where_clauses[] = "action_type IN ('login_success', 'login_failed', 'unauthorized_attempt')";
    } elseif($action_filter === 'roles') {
        $where_clauses[] = "action_type IN ('role_create', 'role_update', 'permission_change')";
    } else { $where_clauses[] = "action_type = '$action_filter'"; }
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : '';
$logs_query = "SELECT * FROM activity_logs $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$logs = $conn->query($logs_query);

$total_rows = fetchBeastCount($conn, "SELECT COUNT(*) as total FROM activity_logs $where_sql");
$total_pages = ceil($total_rows / $limit);

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    /* 🚀 ORIGINAL BEAST DESIGN SYSTEM */
    :root { --accent: #f59e0b; --danger: #e11d48; --panel: #110c1d; --bg: #0a0514; }
    
    /* 🌓 LIGHT MODE OVERRIDES */
    body.light-mode { --panel: #ffffff; --bg: #f3f4f6; }
    body.light-mode main { background-color: #f3f4f6 !important; }
    body.light-mode header { background-color: rgba(255, 255, 255, 0.9) !important; border-bottom: 1px solid #e5e7eb; }
    body.light-mode .glass-card { background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    body.light-mode .text-white, body.light-mode h2, body.light-mode h3 { color: #1f2937 !important; }
    body.light-mode .matrix-input { background: #f9fafb !important; border: 1px solid #d1d5db !important; color: #1f2937 !important; }
    body.light-mode .search-matrix-container { background: rgba(255, 255, 255, 0.8); border: 1px solid #e5e7eb; }

    .glass-card { background: var(--panel); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 2.5rem; transition: 0.4s; }
    .glass-card:hover { border-color: var(--accent); transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.5); }
    
    .search-matrix-container { background: rgba(17, 12, 29, 0.6); backdrop-filter: blur(20px); padding: 30px; border-radius: 2.5rem; border: 1px solid rgba(255, 255, 255, 0.08); }
    .matrix-input { background: #000 !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-radius: 1.2rem !important; padding: 15px 25px !important; font-size: 12px; font-weight: 700; transition: 0.3s; }
    .matrix-input:focus { border-color: var(--accent) !important; box-shadow: 0 0 20px rgba(245, 158, 11, 0.2); }

    .intensity-pill { padding: 4px 14px; border-radius: 50px; font-size: 8px; font-weight: 900; text-transform: uppercase; border: 1px solid transparent; }
    .intensity-danger { background: rgba(225, 29, 72, 0.15); color: #e11d48; border-color: rgba(225, 29, 72, 0.3); animation: pulse-red 2s infinite; }
    .intensity-success { background: rgba(16, 185, 129, 0.15); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
    
    @keyframes pulse-red { 0%, 100% { opacity: 1; filter: drop-shadow(0 0 5px #e11d48); } 50% { opacity: 0.5; } }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }

    /* Theme Switcher Styles */
    .theme-toggle { width: 50px; height: 50px; border-radius: 15px; background: rgba(255,255,255,0.05); display: flex; items-center; justify-content: center; cursor: pointer; border: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
    .theme-toggle:hover { background: var(--accent); color: white; }
    body.light-mode .theme-toggle { background: #f3f4f6; border-color: #d1d5db; color: #1f2937; }

    @media print {
        @page { size: landscape; margin: 15mm; }
        body { background: white !important; color: black !important; overflow: visible !important; }
        .no-print, header, .sidebar, .search-matrix-container, .pagination-matrix, button, script { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; width: 100% !important; display: block !important; position: static !important; background: white !important; }
        .flex-1 { overflow: visible !important; display: block !important; }
        .glass-card { 
            border: 1px solid #ddd !important; background: white !important; color: black !important; 
            box-shadow: none !important; margin-bottom: 30px !important; page-break-inside: avoid; 
        }
        .text-white, h2, h3, span, p, div { color: black !important; text-shadow: none !important; }
        table { width: 100% !important; border-collapse: collapse !important; table-layout: auto !important; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        td, th { border-bottom: 1px solid #eee !important; color: black !important; padding: 15px !important; }
        .bg-white\/5 { background: #f9f9f9 !important; }
    }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col p-8 transition-all duration-500">
    
    <header class="h-28 flex items-center justify-between px-12 bg-[#110c1d]/90 backdrop-blur-2xl border-b border-white/5 sticky top-0 z-50 shrink-0 no-print">
        <div class="flex items-center gap-6">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-amber-600 to-rose-600 rounded-2xl blur opacity-25 group-hover:opacity-60 transition duration-1000"></div>
                <div class="relative p-5 bg-[#0a0514] rounded-2xl border border-white/10 shadow-inner">
                    <i class="fas fa-fingerprint text-amber-500 text-3xl animate-pulse"></i>
                </div>
            </div>
            <div>
                <h2 class="text-3xl font-black text-white uppercase tracking-tighter leading-none">Activity Hub</h2>
                <div class="flex items-center gap-2 mt-3">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
                    <p class="text-[10px] text-gray-500 font-black uppercase tracking-[0.3em]">Live Audit Stream Active</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <button onclick="toggleTheme()" class="theme-toggle" title="Toggle Theme">
                <i id="themeIcon" class="fas fa-moon text-xl"></i>
            </button>

            <div class="hidden xl:flex flex-col text-right pr-10 border-r border-white/10">
                <span id="liveClock" class="text-xl font-black text-white tracking-widest tabular-nums">00:00:00</span>
                <span class="text-[9px] text-amber-500 font-black uppercase tracking-widest mt-1">Matrix Time Sync</span>
            </div>
            <div class="flex items-center gap-5">
                <div class="text-right">
                    <span class="block text-sm font-black text-white uppercase tracking-tight">Turjo Admin</span>
                    <span class="block text-[9px] text-rose-500 font-bold uppercase tracking-widest">Supreme Entity</span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-rose-600 p-[2.5px] shadow-lg hover:rotate-6 transition-all">
                    <div class="w-full h-full bg-[#0a0514] rounded-[14px] flex items-center justify-center overflow-hidden">
                        <img src="https://ui-avatars.com/api/?name=Turjo+Admin&background=random" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-10 space-y-12">
        <section class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="glass-card p-10">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Total Node Events</p>
                <h3 class="text-4xl font-black text-white"><?php echo number_format($total_logs); ?></h3>
            </div>
            <div class="glass-card p-10 border-rose-500/20 bg-rose-600/[0.03]">
                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-2">Breach Alerts</p>
                <h3 class="text-4xl font-black text-white"><?php echo $security_alerts; ?></h3>
            </div>
            <div class="glass-card p-10 border-cyan-500/20">
                <p class="text-[10px] font-black text-cyan-500 uppercase tracking-widest mb-2">24H Activity</p>
                <h3 class="text-4xl font-black text-white"><?php echo $logs_24h; ?></h3>
            </div>
            <div class="glass-card p-8 flex flex-col justify-center gap-4 no-print">
                <button onclick="purgeLogs()" class="w-full py-4 bg-rose-600/10 text-rose-600 border border-rose-600/20 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all">Purge Old Nodes</button>
                <button onclick="window.print()" class="w-full py-4 bg-amber-500/10 text-amber-500 border border-amber-500/20 rounded-2xl text-[10px] font-black uppercase text-center tracking-widest hover:bg-amber-500 hover:text-white transition-all">Export Audit Matrix</button>
            </div>
        </section>

        <section class="search-matrix-container shadow-2xl no-print">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-6 relative">
                    <i class="fas fa-search absolute left-8 top-1/2 -translate-y-1/2 text-amber-500 opacity-60"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Entity, Action or IP..." class="matrix-input w-full pl-20 outline-none">
                </div>
                <div class="md:col-span-3">
                    <select name="action_filter" class="matrix-input w-full cursor-pointer appearance-none outline-none">
                        <option value="all">All Protocols</option>
                        <option value="logins" <?php echo $action_filter == 'logins' ? 'selected' : ''; ?>>Identity Tracking</option>
                        <option value="roles" <?php echo $action_filter == 'roles' ? 'selected' : ''; ?>>Role Monitoring</option>
                        <option value="bulk_action" <?php echo $action_filter == 'bulk_action' ? 'selected' : ''; ?>>Bulk Updates</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="intensity" class="matrix-input w-full cursor-pointer appearance-none outline-none">
                        <option value="all">Intensity: All</option>
                        <option value="success" <?php echo $intensity == 'success' ? 'selected' : ''; ?>>Success</option>
                        <option value="danger" <?php echo $intensity == 'danger' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 h-full rounded-2xl text-white shadow-xl transition-all"><i class="fas fa-sync"></i></button>
                </div>
            </form>
        </section>

        <section class="glass-card overflow-hidden mb-20 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/[0.02] text-[11px] text-gray-500 uppercase font-black tracking-widest border-b border-white/5">
                        <tr>
                            <th class="p-10 pl-12">Admin Node</th>
                            <th>Protocol Action</th>
                            <th>Identity Node (IP)</th>
                            <th class="text-center">Intensity</th>
                            <th class="text-right pr-12">Sync Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs font-bold text-gray-400">
                        <?php if($logs && $logs->num_rows > 0): ?>
                            <?php while($l = $logs->fetch_assoc()): 
                                $int_class = ($l['status'] == 'failed' || $l['status'] == 'danger') ? 'intensity-danger' : 'intensity-success';
                                $display_id = !empty($l['admin_id']) ? htmlspecialchars($l['admin_id']) : 'SYSTEM';
                            ?>
                            <tr class="hover:bg-white/[0.04] transition-all border-b border-white/[0.02] group">
                                <td class="p-8 pl-12">
                                    <div class="flex items-center gap-5">
                                        <div class="w-10 h-10 rounded-2xl bg-white/5 flex items-center justify-center text-amber-500 border border-white/5 text-[10px] font-black">
                                            <?php echo strtoupper(substr($display_id, 0, 1)); ?>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-white uppercase tracking-tighter">ID: <?php echo $display_id; ?></span>
                                            <span class="text-[8px] text-gray-600 uppercase font-black">Authorized Unit</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gray-300 group-hover:text-amber-500 transition-all uppercase"><?php echo htmlspecialchars($l['action_type']); ?></span>
                                    <p class="text-[10px] text-gray-600 mt-2 italic font-medium"><?php echo htmlspecialchars($l['details']); ?></p>
                                </td>
                                <td class="font-mono text-cyan-500/80"><?php echo htmlspecialchars($l['ip_address']); ?></td>
                                <td class="text-center"><span class="intensity-pill <?php echo $int_class; ?>"><?php echo htmlspecialchars($l['status']); ?></span></td>
                                <td class="text-right pr-12 text-[10px] text-gray-600 uppercase">
                                    <?php echo date('d M, Y', strtotime($l['created_at'])); ?>
                                    <span class="block text-[9px] mt-1 text-gray-700 font-black"><?php echo date('H:i:s', strtotime($l['created_at'])); ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-32 text-center uppercase text-[12px] tracking-[0.6em] text-gray-600 font-black">No security nodes detected in current matrix</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    // Load saved theme on refresh
    if (localStorage.getItem('theme') === 'light') {
        document.body.classList.add('light-mode');
        document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
    }

    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', { hour12: false });
    }
    setInterval(updateClock, 1000); updateClock();

    function purgeLogs() {
        Swal.fire({
            title: 'Initiate Matrix Purge?',
            text: "All node entries older than 30 days will be disconnected.",
            icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#e11d48', confirmButtonText: 'Execute Purge',
            background: document.body.classList.contains('light-mode') ? '#fff' : '#110c1d',
            color: document.body.classList.contains('light-mode') ? '#000' : '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'handlers/bulk-handler.php',
                    type: 'POST', dataType: 'json',
                    data: { action: 'purge_old_logs', csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' },
                    success: function(res) { 
                        if(res.status === 'success') { Swal.fire('Purged!', res.message, 'success').then(() => location.reload()); }
                        else { Swal.fire('Error', res.message, 'error'); }
                    }
                });
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>