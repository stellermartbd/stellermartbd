<?php 
/**
 * Prime Admin - Enterprise Bulk Tools Hub (V36.0)
 * Project: Turjo Site | Products Hub BD
 * Features: SQL Safety, AJAX JSON Sync, Micro-Permissions, & Dynamic Logs
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// 1️⃣ CSRF Token Logic
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Micro-Permission Guard
if (!hasPermission($conn, 'bulk.manage')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

/** * ✅ 2️⃣ SQL Query Safety & 4️⃣ Bulk Logs Integration
 * Fetching live analytics without fatal error risk.
 */
$today_actions = 0;
$items_affected = 0;
$failed_actions = 0;

// Today's Sync Count
$res1 = $conn->query("SELECT COUNT(*) as total FROM bulk_logs WHERE DATE(created_at) = CURDATE()");
if($res1 && $row1 = $res1->fetch_assoc()) { $today_actions = $row1['total']; }

// Total Units Affected
$res2 = $conn->query("SELECT SUM(affected_count) as total FROM bulk_logs WHERE DATE(created_at) = CURDATE()");
if($res2 && $row2 = $res2->fetch_assoc()) { $items_affected = $row2['total'] ?? 0; }

// Failed Jobs Count
$res3 = $conn->query("SELECT COUNT(*) as total FROM bulk_logs WHERE status = 'failed' AND DATE(created_at) = CURDATE()");
if($res3 && $row3 = $res3->fetch_assoc()) { $failed_actions = $row3['total']; }

/**
 * ✅ 3️⃣ Search Logic
 */
$search = $_GET['search'] ?? '';

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    :root { --accent: #e11d48; --panel: #110c1d; --bg: #0a0514; }

    /* 📊 Top Stats Bar: Reorganized */
    .beast-stats-matrix {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 25px;
        margin-bottom: 45px;
    }
    .beast-stat-card {
        background: var(--panel);
        border: 1px solid rgba(255, 255, 255, 0.05);
        padding: 25px;
        border-radius: 2.5rem;
        text-align: center;
        transition: 0.3s;
    }
    .beast-stat-card:hover { border-color: var(--accent); transform: translateY(-5px); }
    .beast-stat-card h4 { font-size: 26px; font-weight: 900; color: white; }
    .beast-stat-card p { font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-top: 5px; }

    /* 🔎 Search Terminal: No Placeholder */
    .search-matrix-box {
        background: rgba(17, 12, 29, 0.7);
        padding: 30px 45px;
        border-radius: 3rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 50px;
    }
    .matrix-input-beast {
        background: #000 !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
        border-radius: 1.2rem !important;
        padding: 15px 25px !important;
        font-weight: 700;
        font-size: 14px;
    }

    /* 📦 Action Grid */
    .bulk-beast-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
        gap: 30px;
        padding-bottom: 80px;
    }
    .card-beast-matrix {
        background: var(--panel);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 3rem;
        padding: 35px;
        transition: 0.4s;
    }
    .action-matrix { margin-top: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    
    .btn-node-beast {
        display: flex; align-items: center; gap: 10px; padding: 14px;
        background: rgba(255, 255, 255, 0.02); border-radius: 1.2rem;
        color: #94a3b8; font-size: 9.5px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 1px; border: 1px solid transparent; transition: 0.3s;
        width: 100%; border: none; cursor: pointer;
    }
    .btn-node-beast:hover { background: var(--accent); color: white; }
    .btn-node-beast:disabled { opacity: 0.5; cursor: not-allowed; }
    .btn-node-beast i { color: var(--accent); font-size: 13px; width: 20px; text-align: center; }
    .btn-node-beast:hover i { color: white; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col p-12">
    
    <section class="beast-stats-matrix">
        <div class="beast-stat-card border-rose-500/20 bg-rose-600/5">
            <h4><?php echo $today_actions; ?></h4>
            <p>Syncs Today</p>
        </div>
        <div class="beast-stat-card">
            <h4><?php echo number_format($items_affected); ?></h4>
            <p>Units Affected</p>
        </div>
        <div class="beast-stat-card border-amber-500/20">
            <h4 class="text-amber-500"><?php echo $failed_actions; ?></h4>
            <p>Failed Jobs</p>
        </div>
        <div class="beast-stat-card group cursor-pointer">
            <i class="fas fa-database text-gray-600 group-hover:text-cyan-400 text-2xl mb-2 transition-all"></i>
            <p>Matrix Backup</p>
        </div>
    </section>

    <section class="search-matrix-box shadow-2xl">
        <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-8">
            <div class="md:col-span-9 relative">
                <i class="fas fa-search absolute left-8 top-1/2 -translate-y-1/2 text-rose-500 opacity-60"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="" class="matrix-input-beast w-full pl-20">
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl h-full uppercase text-[10px] tracking-widest transition-all shadow-xl">Start Neural Sync</button>
            </div>
        </form>
    </section>

    <div class="overflow-y-auto custom-scrollbar pr-2">
        <div class="bulk-beast-grid">

            <div class="card-beast-matrix border-blue-500/10 bg-blue-600/5">
                <h3 class="text-xs font-black text-blue-400 uppercase tracking-widest"><i class="fas fa-photo-video mr-2"></i> Content Matrix</h3>
                <div class="action-matrix">
                    <button class="btn-node-beast"><i class="fas fa-image"></i> Banners</button>
                    <button class="btn-node-beast"><i class="fas fa-sliders-h"></i> Sliders</button>
                </div>
            </div>

            <?php if(hasPermission($conn, 'bulk.product')): ?>
            <div class="card-beast-matrix">
                <h3 class="text-xs font-black text-white uppercase tracking-widest"><i class="fas fa-boxes text-rose-500 mr-2"></i> Product Matrix</h3>
                <div class="action-matrix">
                    <button onclick="triggerBulk('bulk_price_edit', 'Update Prices by 10%?')" class="btn-node-beast"><i class="fas fa-percentage"></i> Price Matrix</button>
                    <button class="btn-node-beast"><i class="fas fa-warehouse"></i> Inventory Sync</button>
                </div>
            </div>
            <?php endif; ?>

            <?php if(hasPermission($conn, 'bulk.customer')): ?>
            <div class="card-beast-matrix">
                <h3 class="text-xs font-black text-white uppercase tracking-widest"><i class="fas fa-users-cog text-cyan-500 mr-2"></i> Identity Matrix</h3>
                <div class="action-matrix">
                    <button onclick="triggerBulk('bulk_email', 'Broadcast Matrix Email?')" class="btn-node-beast"><i class="fas fa-paper-plane"></i> Bulk Email</button>
                    <button onclick="triggerBulk('bulk_block_all', 'Initiate Global Lockdown?')" class="btn-node-beast"><i class="fas fa-user-slash"></i> Node Lock</button>
                </div>
            </div>
            <?php endif; ?>

            <?php if(hasPermission($conn, 'bulk.security')): ?>
            <div class="card-beast-matrix border-rose-500/20 bg-rose-600/5">
                <h3 class="text-xs font-black text-rose-500 uppercase tracking-widest"><i class="fas fa-shield-alt mr-2"></i> Security Node</h3>
                <div class="mt-6">
                    <button onclick="confirmVerify('force_logout_all', 'Kill All Sessions?')" class="btn-node-beast border border-rose-500/20"><i class="fas fa-sign-out-alt"></i> Force Logout All</button>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/**
 * ✅ 1️⃣ & 7️⃣ Security & UI Logic
 * Steps: Loading Spinner -> JSON DataType -> Execution
 */
function triggerBulk(action, title) {
    const btn = event.currentTarget;
    $(btn).prop('disabled', true).append(' <i class="fas fa-spinner fa-spin ml-2"></i>');

    $.ajax({
        url: 'handlers/bulk-handler.php',
        type: 'POST',
        dataType: 'json', // Fixed DataType
        data: { 
            action: action, 
            preview: true, 
            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' 
        },
        success: function(resp) {
            $(btn).prop('disabled', false).find('.fa-spinner').remove();
            if(resp.status === 'preview') {
                confirmProtocol(action, title, resp.count);
            } else { Swal.fire('Error', resp.message, 'error'); }
        }
    });
}

function confirmProtocol(action, title, count) {
    Swal.fire({
        title: title,
        html: `<p class="text-[10px] uppercase font-black text-rose-500">Impact Preview: ${count} Nodes Affected</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Run Protocol',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: 'handlers/bulk-handler.php',
                type: 'POST',
                dataType: 'json', // Fixed DataType
                data: { action: action, csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' }
            });
        }
    }).then((result) => {
        if (result.value) {
            if(result.value.status === 'success') {
                Swal.fire('Success!', result.value.message, 'success').then(() => location.reload());
            } else { Swal.fire('Error', result.value.message, 'error'); }
        }
    });
}

function confirmVerify(action, title) {
    Swal.fire({
        title: 'Neural Verification Required',
        input: 'password',
        inputPlaceholder: 'Enter Admin Password',
        showCancelButton: true,
        confirmButtonText: 'Verify & Execute'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.ajax({
                url: 'handlers/bulk-handler.php',
                type: 'POST',
                dataType: 'json', // Fixed DataType
                data: { 
                    action: action, 
                    admin_password: result.value, 
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' 
                },
                success: function(resp) {
                    if(resp.status === 'success') location.reload();
                    else Swal.fire('Denied', resp.message, 'error');
                }
            });
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>