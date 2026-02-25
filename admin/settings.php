<?php 
/**
 * Prime Beast - Professional System Settings (V52.0)
 * Project: Turjo Site | Logic: Dynamic Persistent Configuration
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// 🛡️ Access Control
if (!hasPermission($conn, 'settings.manage')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

// ১. ডাটাবেস থেকে ID নির্বিশেষে প্রথম রো-টি ফেচ করা
$settings_res = $conn->query("SELECT * FROM settings ORDER BY id ASC LIMIT 1");
$settings = $settings_res->fetch_assoc();

// ২. সেটিংস আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = mysqli_real_escape_string($conn, trim($_POST['site_name']));
    $contact_email = mysqli_real_escape_string($conn, trim($_POST['contact_email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $whatsapp_num = mysqli_real_escape_string($conn, trim($_POST['whatsapp_order_number'] ?? ''));
    $currency_unit = mysqli_real_escape_string($conn, trim($_POST['currency_unit'] ?? '৳'));
    
    // লোগো হ্যান্ডলিং (Public Directory Validation)
    $logo_name = $settings['logo'] ?? 'logo.png'; 
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $target_dir = "../public/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo_name = "logo_" . time() . "." . $ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $logo_name);
    }

    // 🛠️ Persistent Update Matrix
    if (!$settings) {
        $sql = "INSERT INTO settings (site_name, contact_email, phone, whatsapp_order_number, currency_unit, logo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $site_name, $contact_email, $phone, $whatsapp_num, $currency_unit, $logo_name);
    } else {
        $sql = "UPDATE settings SET site_name = ?, contact_email = ?, phone = ?, whatsapp_order_number = ?, currency_unit = ?, logo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $site_name, $contact_email, $phone, $whatsapp_num, $currency_unit, $logo_name, $settings['id']);
    }

    if($stmt->execute()){
        // ✅ Audit Hub-e log pathano
        $admin_id = $_SESSION['admin_id'] ?? 'SYSTEM';
        $details = "System configuration updated: $site_name";
        $conn->query("INSERT INTO activity_logs (admin_id, action_type, status, details, ip_address) 
                      VALUES ('$admin_id', 'settings_update', 'success', '$details', '{$_SERVER['REMOTE_ADDR']}')");
        
        $success_msg = "Configuration Saved Successfully!";
        $settings_res = $conn->query("SELECT * FROM settings ORDER BY id ASC LIMIT 1");
        $settings = $settings_res->fetch_assoc();
    } else {
        $error_msg = "Matrix Error: " . $stmt->error;
    }
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col p-8 transition-all duration-500">
    
    <header class="h-24 flex items-center justify-between px-10 bg-[#110c1d]/90 backdrop-blur-xl border-b border-white/5 sticky top-0 z-30 shrink-0">
        <div class="flex items-center gap-6">
            <div class="p-4 bg-white/5 rounded-2xl border border-white/10 shadow-lg">
                <i class="fas fa-cog text-amber-500 text-2xl animate-spin-slow"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter">System Matrix</h2>
                <p class="text-[9px] text-gray-500 uppercase tracking-widest mt-1">Live Sync Status: <?php echo $settings ? 'Connected' : 'Setup Required'; ?></p>
            </div>
        </div>
        <div class="flex items-center gap-4 text-right">
             <span id="liveClock" class="text-sm font-black text-white tracking-widest tabular-nums">00:00:00</span>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-10 space-y-10">
        <div class="max-w-[1000px] mx-auto space-y-8 pb-20">
            
            <?php if(isset($success_msg)): ?>
                <div class="bg-green-500/10 border border-green-500/20 text-green-500 px-8 py-5 rounded-[2rem] text-[10px] font-black uppercase tracking-widest shadow-lg">
                    <i class="fas fa-check-circle mr-3"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-8">
                
                <div class="glass-card p-10 bg-[#110c1d] rounded-[3rem] border border-white/5 shadow-2xl space-y-10">
                    <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.3em] border-b border-white/5 pb-6">Core Configuration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Platform Identity</label>
                            <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? ''); ?>" placeholder="Turjo Site" class="w-full bg-black border border-white/10 rounded-2xl px-6 py-5 focus:border-amber-500 text-white font-bold transition-all text-sm outline-none">
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Active Currency</label>
                            <select name="currency_unit" class="w-full bg-black border border-white/10 rounded-2xl px-6 py-5 focus:border-amber-500 text-white font-bold transition-all text-sm outline-none appearance-none">
                                <option value="৳" <?= (($settings['currency_unit'] ?? '৳') == '৳') ? 'selected' : ''; ?>>Bangladeshi Taka (৳)</option>
                                <option value="$" <?= (($settings['currency_unit'] ?? '') == '$') ? 'selected' : ''; ?>>US Dollar ($)</option>
                            </select>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-emerald-500 uppercase tracking-widest">WhatsApp Order Node</label>
                            <input type="text" name="whatsapp_order_number" value="<?= htmlspecialchars($settings['whatsapp_order_number'] ?? ''); ?>" class="w-full bg-emerald-500/5 border border-emerald-500/20 rounded-2xl px-6 py-5 focus:border-emerald-500 text-white font-bold transition-all text-sm outline-none">
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Neural Support Email</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? ''); ?>" class="w-full bg-black border border-white/10 rounded-2xl px-6 py-5 focus:border-amber-500 text-white font-bold transition-all text-sm outline-none">
                        </div>
                    </div>
                </div>

                <div class="glass-card p-10 bg-[#110c1d] rounded-[3rem] border border-white/5 shadow-2xl">
                    <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.3em] border-b border-white/5 pb-6 mb-8">Visual Identity</h3>
                    <div class="flex items-center gap-10">
                        <div class="w-32 h-32 rounded-[2rem] bg-black border border-white/5 flex items-center justify-center overflow-hidden p-6 shadow-inner">
                            <img src="../public/uploads/<?= $settings['logo'] ?? 'logo.png'; ?>" class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="flex-1 space-y-4">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Upload Supreme Logo</label>
                            <input type="file" name="logo" class="text-xs text-gray-500 file:mr-6 file:py-3 file:px-8 file:rounded-2xl file:border-0 file:bg-amber-500 file:text-white file:font-black file:uppercase file:text-[9px] cursor-pointer">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-12 py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-2xl shadow-amber-500/20">Apply Matrix Configuration</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', { hour12: false });
    }
    setInterval(updateClock, 1000); updateClock();
</script>

<?php include 'includes/footer.php'; ?>