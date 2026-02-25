<?php
/**
 * Prime Beast - Full Notification Command Center
 * Logic: Multi-filter, Bulk Actions & Status Management
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// 🔥 ১৫ মিনিট অটো লগআউট লজিক
$timeout_limit = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_limit)) {
    session_unset(); session_destroy(); header("Location: login.php?reason=timeout"); exit;
}
$_SESSION['last_activity'] = time();

// ফিল্টারিং লজিক
$filter_type = $_GET['type'] ?? 'ALL';
$filter_status = $_GET['status'] ?? 'ALL';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM notifications WHERE 1=1";
if ($filter_type !== 'ALL') $query .= " AND type = '$filter_type'";
if ($filter_status === 'READ') $query .= " AND is_read = 1";
if ($filter_status === 'UNREAD') $query .= " AND is_read = 0";
if (!empty($search)) $query .= " AND (title LIKE '%$search%' OR message LIKE '%$search%')";
$query .= " ORDER BY created_at DESC";

$notifications = $conn->query($query);
?>

<main class="flex-1 h-screen overflow-hidden bg-[#050308] flex flex-col min-w-0 transition-all duration-300">
    
    <header class="h-20 flex items-center justify-between px-8 bg-[#0d0915] border-b border-white/10 sticky top-0 z-20 shrink-0 shadow-[0_4px_30px_rgba(0,0,0,0.8)]">
        <div class="flex items-center gap-5">
            <div class="p-3.5 bg-rose-500/10 rounded-2xl border-2 border-rose-500/40 shadow-2xl">
                <i class="fas fa-bell text-rose-500 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-[900] text-white uppercase tracking-tighter leading-none" style="font-family: 'Orbitron';">Notification Hub</h2>
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.4em] mt-2">Neural Activity Engine</p>
            </div>
        </div>
        
        <div class="flex gap-4">
            <button onclick="bulkAction('MARK_READ')" class="px-6 py-3 bg-blue-600/20 hover:bg-blue-600 text-blue-400 hover:text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all border-2 border-blue-600/30">Mark All Read</button>
            <button onclick="bulkAction('DELETE_ALL')" class="px-6 py-3 bg-rose-600/20 hover:bg-rose-600 text-rose-400 hover:text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all border-2 border-rose-600/30">Purge Logs</button>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-10 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-[#0d0915] via-[#050308] to-[#050308]">
        <div class="max-w-[1500px] mx-auto space-y-10 pb-20">
            
            <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border-2 border-white/5 flex flex-wrap gap-8 items-center shadow-2xl">
                <form method="GET" class="flex flex-wrap gap-8 items-center w-full">
                    <div class="flex-1 min-w-[300px] relative">
                        <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
                        <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search neural logs..." class="w-full bg-black/60 border-2 border-white/5 rounded-2xl py-5 pl-16 pr-8 text-sm font-bold text-white outline-none focus:border-blue-500 transition shadow-inner placeholder:text-gray-700">
                    </div>
                    
                    <select name="type" class="bg-black/60 border-2 border-white/5 rounded-2xl px-8 py-5 text-[11px] font-[900] text-white uppercase tracking-widest outline-none focus:border-blue-500 transition cursor-pointer">
                        <option value="ALL">All Categories</option>
                        <option value="ORDER" <?php if($filter_type=='ORDER') echo 'selected'; ?>>Orders</option>
                        <option value="PRODUCT" <?php if($filter_type=='PRODUCT') echo 'selected'; ?>>Products</option>
                        <option value="SYSTEM" <?php if($filter_type=='SYSTEM') echo 'selected'; ?>>System</option>
                        <option value="CUSTOMER" <?php if($filter_type=='CUSTOMER') echo 'selected'; ?>>Customers</option>
                    </select>

                    <select name="status" class="bg-black/60 border-2 border-white/5 rounded-2xl px-8 py-5 text-[11px] font-[900] text-white uppercase tracking-widest outline-none focus:border-blue-500 transition cursor-pointer">
                        <option value="ALL">All Status</option>
                        <option value="UNREAD" <?php if($filter_status=='UNREAD') echo 'selected'; ?>>Unread</option>
                        <option value="READ" <?php if($filter_status=='READ') echo 'selected'; ?>>Read</option>
                    </select>

                    <button type="submit" class="bg-blue-600 text-white px-10 py-5 rounded-2xl font-[900] text-[11px] uppercase tracking-widest shadow-xl shadow-blue-900/40 hover:scale-105 active:scale-95 transition-all">Execute Filter</button>
                </form>
            </div>

            <div class="space-y-6">
                <?php if($notifications && $notifications->num_rows > 0): ?>
                    <?php while($n = $notifications->fetch_assoc()): ?>
                        <?php 
                            $icon = 'fa-bolt'; $color = 'text-blue-400'; $bg = 'bg-[#161021]';
                            if($n['type'] == 'ORDER') { $icon = 'fa-shopping-cart'; $color = 'text-green-400'; }
                            if($n['type'] == 'SYSTEM') { $icon = 'fa-microchip'; $color = 'text-rose-400'; }
                            
                            $is_unread = !$n['is_read'];
                            $border = $is_unread ? 'border-blue-500/30 shadow-[0_0_20px_rgba(59,130,246,0.1)]' : 'border-white/5';
                            if($n['priority'] == 'HIGH' && $is_unread) { $border = 'border-rose-500/40 shadow-[0_0_25px_rgba(244,63,94,0.15)]'; $bg = 'bg-[#1a0c16]'; }
                        ?>
                        <div class="group flex items-center gap-8 p-8 <?php echo $bg; ?> rounded-[2.5rem] border-2 <?php echo $border; ?> hover:border-blue-500/50 transition-all relative overflow-hidden <?php echo !$is_unread ? 'opacity-40 grayscale-[0.5]' : ''; ?>">
                            
                            <div class="w-16 h-16 rounded-2xl bg-black/60 border-2 border-white/5 flex items-center justify-center shrink-0 shadow-inner">
                                <i class="fas <?php echo $icon; ?> <?php echo $color; ?> text-2xl"></i>
                            </div>
                            
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-2">
                                    <h4 class="text-lg font-[900] text-white uppercase tracking-tighter"><?php echo $n['title']; ?></h4>
                                    <?php if($n['priority'] == 'HIGH'): ?>
                                        <span class="px-3 py-1 bg-rose-600 text-[10px] font-[900] text-white rounded-lg animate-pulse uppercase tracking-widest border border-rose-400/50">Critical</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-300 font-bold leading-relaxed mb-3"><?php echo $n['message']; ?></p>
                                <div class="flex items-center gap-6">
                                    <p class="text-[10px] text-gray-500 font-black uppercase tracking-[0.2em]"><i class="far fa-clock mr-2 text-blue-500"></i> <?php echo date('d M, Y | h:i A', strtotime($n['created_at'])); ?></p>
                                    <p class="text-[10px] text-gray-500 font-black uppercase tracking-[0.2em]"><i class="fas fa-tag mr-2 text-purple-500"></i> <?php echo $n['type']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 opacity-0 group-hover:opacity-100 transition-all translate-x-10 group-hover:translate-x-0">
                                <a href="<?php echo $n['redirect_url']; ?>" class="w-12 h-12 bg-blue-600/20 text-blue-400 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-lg"><i class="fas fa-external-link-alt"></i></a>
                                <button onclick="deleteNotif(<?php echo $n['id']; ?>)" class="w-12 h-12 bg-rose-600/20 text-rose-400 rounded-xl flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-lg"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="py-48 text-center bg-[#110c1d] rounded-[3rem] border-2 border-dashed border-white/10">
                        <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-8 border-2 border-white/5">
                            <i class="fas fa-ghost text-4xl text-gray-700"></i>
                        </div>
                        <h3 class="text-white font-[900] uppercase tracking-[0.6em] text-lg">Neural Silence</h3>
                        <p class="text-[11px] text-gray-600 mt-3 font-black uppercase tracking-widest">No activity data detected within current parameters</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    /**
     * 🔥 BULK ACTIONS & MANAGEMENT
     */
    function bulkAction(action) {
        if(confirm(`SYSTEM PROTOCOL: Execute ${action}?`)) {
            $.post('handlers/notification-manager.php', {bulk: action}, function(res) {
                location.reload();
            });
        }
    }

    function deleteNotif(id) {
        $.post('handlers/notification-manager.php', {delete_id: id}, function(res) {
            location.reload();
        });
    }
</script>

<?php include 'includes/footer.php'; ?>