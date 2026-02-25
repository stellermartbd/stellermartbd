<?php 
/**
 * Prime Admin - Enterprise Coupon Hub (V40.0)
 * Stability: Production Ready | Project: Turjo Site
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// CSRF Token logic
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Micro-Permission Guard
if (!hasPermission($conn, 'coupon.manage')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

/** * 1️⃣ Dashboard Overview (Top Section Stats)
 */
$stats = [
    'total' => 0, 'active' => 0, 'expired' => 0, 'discount' => 0, 'usage' => 0, 'blocked' => 0
];

$res1 = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
    SUM(CASE WHEN status = 'disabled' THEN 1 ELSE 0 END) as blocked
    FROM coupons");
if($res1 && $row1 = $res1->fetch_assoc()){
    $stats['total'] = $row1['total'];
    $stats['active'] = $row1['active'];
    $stats['expired'] = $row1['expired'];
    $stats['blocked'] = $row1['blocked'];
}

$res2 = $conn->query("SELECT SUM(discount_amount) as total_given, COUNT(*) as usage_today FROM coupon_usage_logs WHERE DATE(used_at) = CURDATE()");
if($res2 && $row2 = $res2->fetch_assoc()){
    $stats['discount'] = $row2['total_given'] ?? 0;
    $stats['usage'] = $row2['usage_today'];
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<style>
    :root { --accent: #e11d48; --panel: #110c1d; --bg: #0a0514; }
    .coupon-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card-matrix { background: var(--panel); border: 1px solid rgba(255, 255, 255, 0.05); padding: 25px; border-radius: 2rem; text-align: center; }
    .stat-card-matrix h4 { font-size: 24px; font-weight: 900; color: white; }
    .stat-card-matrix p { font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-top: 5px; }
    .matrix-table { background: var(--panel); border-radius: 3rem; border: 1px solid rgba(255, 255, 255, 0.05); overflow: hidden; }
    .matrix-table th { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; padding: 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .matrix-table td { padding: 25px; color: #94a3b8; font-size: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.02); }
    .status-pill { padding: 4px 12px; border-radius: 50px; font-size: 9px; font-weight: 900; text-transform: uppercase; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col p-12">
    
    <section class="coupon-stat-grid">
        <div class="stat-card-matrix">
            <h4><?php echo $stats['total']; ?></h4>
            <p>Total Coupons</p>
        </div>
        <div class="stat-card-matrix border-green-500/20 bg-green-500/5">
            <h4 class="text-green-500"><?php echo $stats['active']; ?></h4>
            <p>Active Nodes</p>
        </div>
        <div class="stat-card-matrix border-rose-500/20 bg-rose-500/5">
            <h4 class="text-rose-500"><?php echo $stats['expired']; ?></h4>
            <p>Expired</p>
        </div>
        <div class="stat-card-matrix border-cyan-500/20 bg-cyan-500/5">
            <h4 class="text-cyan-400">৳<?php echo number_format($stats['discount']); ?></h4>
            <p>Total Discount Given</p>
        </div>
    </section>

    <div class="flex justify-between items-center mb-10">
        <h2 class="text-3xl font-black text-white uppercase tracking-tighter">Coupon Matrix</h2>
        <div class="flex gap-4">
            <button class="bg-white/5 border border-white/10 px-6 py-3 rounded-2xl text-gray-400 font-bold uppercase text-[10px] tracking-widest hover:bg-white/10">
                Export CSV
            </button>
            <button onclick="openCouponModal()" class="bg-rose-600 px-8 py-3 rounded-2xl text-white font-black uppercase text-[10px] tracking-widest shadow-2xl hover:bg-rose-700 transition-all">
                + Generate Coupon
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar matrix-table">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Discount</th>
                    <th>Usage</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC");
                while($row = $coupons->fetch_assoc()):
                    $status_class = $row['status'] == 'active' ? 'bg-green-500/10 text-green-500' : 'bg-rose-500/10 text-rose-500';
                ?>
                <tr class="hover:bg-white/[0.02] transition-all">
                    <td class="font-black text-white"><?php echo $row['code']; ?></td>
                    <td class="uppercase font-bold text-[10px]"><?php echo $row['discount_type']; ?></td>
                    <td><?php echo $row['discount_value']; ?><?php echo $row['discount_type'] == 'percentage' ? '%' : '৳'; ?></td>
                    <td><?php echo $row['usage_count']; ?> / <?php echo $row['total_usage_limit'] == 0 ? '∞' : $row['total_usage_limit']; ?></td>
                    <td class="text-[10px] font-bold"><?php echo date('M d, Y', strtotime($row['end_date'])); ?></td>
                    <td><span class="status-pill <?php echo $status_class; ?>"><?php echo $row['status']; ?></span></td>
                    <td>
                        <div class="flex gap-4">
                            <button class="text-gray-600 hover:text-white"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteCoupon(<?php echo $row['id']; ?>)" class="text-gray-600 hover:text-rose-500"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/**
 * 🎟️ Open Advanced Coupon Modal
 */
function openCouponModal() {
    Swal.fire({
        title: 'Generate New Coupon Protocol',
        width: '800px',
        html: `
            <div class="grid grid-cols-2 gap-6 text-left p-6 bg-black/20 rounded-3xl mt-4">
                <div>
                    <label class="text-[9px] uppercase font-black text-gray-500">Coupon Code</label>
                    <input id="cp_code" class="swal2-input !m-0 !w-full !rounded-xl" placeholder="E.g. SUMMER2026">
                </div>
                <div>
                    <label class="text-[9px] uppercase font-black text-gray-500">Discount Type</label>
                    <select id="cp_type" class="swal2-input !m-0 !w-full !rounded-xl">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (৳)</option>
                        <option value="free_shipping">Free Shipping</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] uppercase font-black text-gray-500">Value</label>
                    <input id="cp_val" type="number" class="swal2-input !m-0 !w-full !rounded-xl">
                </div>
                <div>
                    <label class="text-[9px] uppercase font-black text-gray-500">Expiry Date</label>
                    <input id="cp_expiry" type="date" class="swal2-input !m-0 !w-full !rounded-xl">
                </div>
            </div>
        `,
        confirmButtonText: 'Deploy Coupon',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        preConfirm: () => {
            const data = {
                code: $('#cp_code').val(),
                type: $('#cp_type').val(),
                value: $('#cp_val').val(),
                expiry: $('#cp_expiry').val(),
                action: 'create_coupon',
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            };
            if(!data.code || !data.value || !data.expiry) return Swal.showValidationMessage('All fields are required');
            return $.ajax({
                url: 'handlers/coupon-handler.php',
                type: 'POST',
                dataType: 'json',
                data: data
            });
        }
    }).then((res) => {
        if(res.value && res.value.status === 'success') {
            Swal.fire('Success', 'Coupon Matrix Deployed', 'success').then(() => location.reload());
        }
    });
}

function deleteCoupon(id) {
    Swal.fire({
        title: 'Terminate Coupon Node?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Confirm Delete'
    }).then((res) => {
        if(res.isConfirmed) {
            $.ajax({
                url: 'handlers/coupon-handler.php',
                type: 'POST',
                dataType: 'json',
                data: { id: id, action: 'delete_coupon', csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' },
                success: function(r) { if(r.status === 'success') location.reload(); }
            });
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>