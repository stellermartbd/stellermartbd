<?php 
/**
 * Prime Beast - Tactical Personnel Hub (Supreme 7.0)
 * Logic: Large Text, Zero Italics, Granular Matrix & God Mode
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

/**
 * 🛰️ NEURAL ROLE TEMPLATES
 */
$predefined_roles = [
    'VIEWER' => [
        'label' => 'Neural Observer',
        'perms' => ['dashboard.view', 'reports.view', 'products.view', 'orders.view', 'customers.view', 'support_hub.view']
    ],
    'MODERATOR' => [
        'label' => 'Tactical Moderator',
        'perms' => ['products.view', 'orders.view', 'orders.edit', 'support_hub.view', 'support_hub.edit']
    ],
    'CONTENT_MANAGER' => [
        'label' => 'Creative Hub',
        'perms' => ['category_manage.view', 'category_manage.add', 'category_manage.edit', 'product_manage.view', 'product_manage.add', 'product_manage.edit']
    ],
    'WAREHOUSE_CHIEF' => [
        'label' => 'Logistics Alpha',
        'perms' => ['manage_keys.view', 'manage_keys.add', 'manage_keys.delete', 'product_manage.view', 'product_manage.edit', 'stock_control.view', 'stock_control.edit']
    ]
];

// Matrix classification - All menu items added from screenshot
$matrix_modules = [
    'Neural' => [
        'Dashboard' => ['view'], 
        'Reports & Stats' => ['view'], 
        'Settings' => ['view', 'edit'], 
        'Admins' => ['view', 'add', 'edit', 'delete'],
        'System Health' => ['view'],
        'Activity Logs' => ['view']
    ],
    'Inventory' => [
        'Manage Keys' => ['view', 'add', 'edit', 'delete'], 
        'Category Manage' => ['view', 'add', 'edit', 'delete'], 
        'Product Manage' => ['view', 'add', 'edit', 'delete'], 
        'Stock Control' => ['view', 'edit'],
        'Bulk Productivity' => ['view', 'edit']
    ],
    'Tactical' => [
        'Order Automation' => ['view', 'edit'], 
        'Order Manage' => ['view', 'edit', 'delete'], 
        'Support Hub' => ['view', 'edit'],
        'Coupons' => ['view', 'add', 'edit', 'delete']
    ],
    'Intel & Content' => [
        'User Intelligence' => ['view'], 
        'Marketing Intel' => ['view'], 
        'Customers' => ['view', 'edit'], 
        'Reviews' => ['view', 'delete'],
        'Slider Manage' => ['view', 'add', 'edit', 'delete'],
        'CMS Pages' => ['view', 'add', 'edit', 'delete'],
        'Notifications' => ['view', 'add', 'delete']
    ]
];
?>

<style>
    /* 🔥 Zero Italics & Large Text */
    * { font-style: normal !important; }
    .text-sm { font-size: 0.95rem !important; }
    .text-xs { font-size: 0.85rem !important; }
    
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e1b2e; border-radius: 10px; }
    
    .tab-btn.active { background: #4f46e5; color: white; border-color: #6366f1; }
    .perm-checkbox:checked { filter: drop-shadow(0 0 5px currentColor); }
    
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#050308] flex flex-col min-w-0 transition-all duration-300">
    
    <header class="h-20 flex items-center justify-between px-10 bg-[#0d0915] border-b border-indigo-500/20 sticky top-0 z-20 shrink-0">
        <div class="flex items-center gap-5">
            <div class="p-3 bg-indigo-600/10 rounded-2xl border border-indigo-500/30">
                <i class="fas fa-id-card-alt text-indigo-400 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-[900] text-white uppercase tracking-tighter">Admin Control</h2>
                <p class="text-xs font-black text-indigo-500 uppercase tracking-[0.3em] mt-1">SUPREME PRIME ADMIN: TURJO SARKER</p>
            </div>
        </div>
        
        <div class="flex items-center gap-8">
            <div class="flex items-center gap-3 px-5 py-2 bg-green-500/10 border border-green-500/20 rounded-xl">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
                <span class="text-[11px] font-black text-green-500 uppercase tracking-widest">Neural Link Active</span>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-indigo-600 border border-indigo-400/30 flex items-center justify-center font-black text-white text-lg">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-[#0d0915] via-[#050308] to-[#050308]">
        <div class="max-w-[1700px] mx-auto space-y-8 pb-32">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl relative overflow-hidden group">
                    <p class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Total Admins</p>
                    <h3 class="text-4xl font-[900] text-white tracking-tighter">
                        <?php 
                        $admin_count = $conn->query("SELECT id FROM admins")->num_rows;
                        echo $admin_count; 
                        ?>
                    </h3>
                </div>

                <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-indigo-500/20 shadow-2xl">
                    <p class="text-[11px] font-black text-indigo-400 uppercase tracking-widest mb-2">Active Roles</p>
                    <h3 class="text-4xl font-[900] text-white tracking-tighter">
                        <?php 
                        echo $conn->query("SELECT id FROM roles")->num_rows; 
                        ?>
                    </h3>
                </div>

                <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-white/5 shadow-2xl">
                    <p class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Security</p>
                    <h3 class="text-4xl font-[900] text-green-500 tracking-tighter uppercase">ONLINE</h3>
                </div>

                <div class="p-8 bg-[#110c1d] rounded-[2.5rem] border border-rose-500/10 shadow-2xl">
                    <p class="text-[11px] font-black text-rose-500 uppercase tracking-widest mb-2">Uptime Cycle</p>
                    <h3 id="session-timer" class="text-4xl font-[900] text-white tracking-tighter">15:00</h3>
                </div>
            </div>

            <div class="flex gap-6 border-b border-white/5 pb-6">
                <button onclick="switchTab('staff-dir')" class="tab-btn active px-10 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest transition-all">Directory</button>
                <button onclick="switchTab('deploy-hub')" class="tab-btn px-10 py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest transition-all text-gray-500 hover:text-white">Deployment Hub</button>
            </div>

            <div id="staff-dir" class="tab-content animate-fade-in">
                <div class="bg-[#110c1d] rounded-[3rem] border border-white/5 shadow-2xl overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-black/40 text-[11px] text-gray-500 font-black uppercase tracking-widest">
                            <tr>
                                <th class="p-6 pl-12">Admin Username</th>
                                <th class="p-6 text-center">Roles</th>
                                <th class="p-6 text-center">Neural Status</th>
                                <th class="p-6 text-right pr-12">Command</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-bold text-white/90">
                            <tr class="border-b border-indigo-500/20 bg-indigo-600/5 transition-all">
                                <td class="p-8 pl-12">
                                    <div class="flex items-center gap-6">
                                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 border border-indigo-400/50 flex items-center justify-center text-white text-2xl font-[900] shadow-2xl">TS</div>
                                        <div>
                                            <p class="text-lg font-[900] text-indigo-400 uppercase tracking-tighter">TURJO SARKER</p>
                                            <p class="text-[10px] text-gray-500 uppercase tracking-widest font-black">SUPREME PRIME ADMIN</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8 text-center">
                                    <span class="px-6 py-2.5 bg-indigo-600/20 rounded-xl text-[11px] font-[900] uppercase border border-indigo-500/40 text-indigo-300">GOD MODE ACTIVE</span>
                                </td>
                                <td class="p-8 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                                        <span class="uppercase text-[11px] font-black text-green-500 tracking-widest">SUPREME ADMIN</span>
                                    </div>
                                </td>
                                <td class="p-8 text-right pr-12">
                                    <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest border border-white/5 px-6 py-3 rounded-xl">LOCKED</span>
                                </td>
                            </tr>

                            <?php 
                            $staffs = $conn->query("SELECT a.*, r.name as role_name FROM admins a LEFT JOIN roles r ON a.role_id = r.id WHERE a.username != 'TURJO SARKER' ORDER BY a.id DESC");
                            while($s = $staffs->fetch_assoc()): ?>
                            <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-all group">
                                <td class="p-6 pl-12">
                                    <div class="flex items-center gap-6">
                                        <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-gray-400 font-black text-lg"><?php echo strtoupper(substr($s['username'],0,1)); ?></div>
                                        <div>
                                            <p class="text-base font-black text-white uppercase"><?php echo htmlspecialchars($s['username']); ?></p>
                                            <p class="text-[10px] text-gray-600 uppercase">RBAC-SEC-<?php echo $s['id']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6 text-center uppercase text-[11px] text-indigo-400 font-black">
                                    <?php echo htmlspecialchars($s['role_name'] ?? 'RESTRICTED'); ?>
                                </td>
                                <td class="p-6 text-center text-[11px] font-[900] tracking-widest <?php echo ($s['status'] == 'Active') ? 'text-green-500' : 'text-rose-500'; ?>">
                                    <?php echo strtoupper($s['status'] ?? 'OFFLINE'); ?>
                                </td>
                                <td class="p-6 text-right pr-12">
                                    <div class="flex justify-end gap-4 translate-x-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all">
                                        <button onclick='openEditModal(<?php echo json_encode($s); ?>)' class="w-10 h-10 bg-indigo-600/10 rounded-xl hover:bg-indigo-600 text-indigo-400 hover:text-white transition-all border border-indigo-600/20 flex items-center justify-center shadow-lg"><i class="fas fa-edit text-xs"></i></button>
                                        <button onclick="confirmDelete(<?php echo $s['id']; ?>, '<?php echo $s['username']; ?>')" class="w-10 h-10 bg-rose-600/10 rounded-xl hover:bg-rose-600 text-rose-500 hover:text-white transition-all border border-rose-600/20 flex items-center justify-center shadow-lg"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="deploy-hub" class="tab-content hidden animate-fade-in">
                <div class="grid grid-cols-4 gap-6 mb-8">
                    <?php 
                    $legends = ['view' => ['Gray', '1. View Protocol'], 'add' => ['Blue', '2. Add Item'], 'edit' => ['Amber', '3. Edit Item'], 'delete' => ['Rose', '4. Delete Item']];
                    foreach($legends as $key => $val): 
                    ?>
                    <div class="p-4 bg-[#110c1d] rounded-[1.5rem] border border-white/5 flex items-center gap-4">
                        <span class="w-3.5 h-3.5 rounded-md bg-<?php echo ($key=='view'?'gray':($key=='add'?'blue':($key=='edit'?'amber':'rose'))); ?>-600 shadow-xl"></span>
                        <p class="text-[12px] font-black text-gray-400 uppercase tracking-widest"><?php echo $val[1]; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form action="handlers/role-handler.php" method="POST" id="rbacForm" class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <input type="hidden" name="admin_id" id="form_admin_id">
                    
                    <div class="lg:col-span-1 p-10 bg-[#110c1d] rounded-[3rem] border border-indigo-500/20 shadow-2xl h-fit">
                        <h3 class="text-xs font-black text-indigo-400 uppercase tracking-[0.5em] mb-10 text-center" id="deploy-title">New Admin</h3>
                        <div class="space-y-6">
                            <input type="text" name="username" id="form_username" placeholder="User name" required class="w-full bg-black/40 border border-white/10 rounded-2xl px-6 py-5 text-sm font-black text-white outline-none focus:border-indigo-500">
                            <input type="password" name="password" id="form_password" placeholder="Access Password" class="w-full bg-black/40 border border-white/10 rounded-2xl px-6 py-5 text-sm font-black text-white outline-none focus:border-indigo-500">
                            
                            <div class="space-y-3 pt-6 border-t border-white/5">
                                <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest ml-4">Role Template</p>
                                <select onchange="applyPredefinedRole(this.value)" class="w-full bg-indigo-600/10 border border-indigo-500/30 rounded-2xl px-6 py-4 text-[11px] font-black text-indigo-400 uppercase cursor-pointer">
                                    <option value="">MANUAL DEPLOY</option>
                                    <?php foreach($predefined_roles as $id => $role): ?>
                                        <option value="<?php echo $id; ?>"><?php echo $role['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <input type="text" name="role_name" id="role_name_input" placeholder="Rank Designation" required class="w-full bg-black/40 border border-white/10 rounded-2xl px-6 py-5 text-sm font-black text-white outline-none focus:border-indigo-500">
                            
                            <button type="submit" name="deploy_admin_with_role" id="submit-btn" class="w-full bg-indigo-600 text-white font-[900] py-6 rounded-2xl uppercase text-[12px] tracking-[0.5em] shadow-2xl hover:bg-indigo-700 transition-all border-b-4 border-indigo-800">EXECUTE DEPLOY</button>
                            <button type="button" onclick="cancelEdit()" id="cancel-btn" class="hidden w-full bg-rose-600/10 text-rose-500 font-[900] py-4 rounded-2xl uppercase text-[10px] tracking-widest mt-4">Discard Editing</button>
                        </div>
                    </div>

                    <div class="lg:col-span-3 p-10 bg-[#110c1d] rounded-[3.5rem] border border-white/5 shadow-2xl relative overflow-hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <?php foreach($matrix_modules as $groupName => $modList): ?>
                            <div class="p-8 bg-black/30 rounded-[2.5rem] border border-white/5">
                                <h4 class="text-[11px] font-[900] text-indigo-500 uppercase tracking-[0.5em] mb-6 border-l-4 border-indigo-600 pl-4"><?php echo $groupName; ?> Sector</h4>
                                <div class="space-y-4">
                                    <?php foreach($modList as $modName => $allowedActions): 
                                        $slug = strtolower(str_replace(' ', '_', $modName));
                                    ?>
                                    <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-white/[0.04] transition-all group/row">
                                        <span class="text-[12px] font-black text-gray-300 uppercase group-hover/row:text-white transition-all"><?php echo $modName; ?></span>
                                        <div class="flex gap-4 min-w-[120px] justify-end">
                                            <?php foreach(['view', 'add', 'edit', 'delete'] as $act): ?>
                                                <?php if(in_array($act, $allowedActions)): ?>
                                                    <input type="checkbox" name="permissions[]" value="<?php echo $slug.'.'.$act; ?>" class="perm-checkbox w-6 h-6 rounded-lg border-2 border-white/10 bg-transparent text-<?php echo ($act=='view'?'gray':($act=='add'?'blue':($act=='edit'?'amber':'rose'))); ?>-500 focus:ring-0 cursor-pointer shadow-lg">
                                                <?php else: ?>
                                                    <div class="w-6 h-6 border-2 border-white/5 bg-white/[0.02] rounded-lg flex items-center justify-center opacity-20"><i class="fas fa-lock text-[8px]"></i></div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const beastRoles = <?php echo json_encode($predefined_roles); ?>;

    function applyPredefinedRole(roleKey) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
        if (!roleKey) return;
        beastRoles[roleKey].perms.forEach(perm => {
            const cb = document.querySelector(`.perm-checkbox[value="${perm}"]`);
            if(cb) cb.checked = true;
        });
        document.getElementById('role_name_input').value = beastRoles[roleKey].label;
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Neural Template Linked', showConfirmButton: false, timer: 1500, background: '#110c1d', color: '#fff' });
    }

    function openEditModal(staff) {
        switchTab('deploy-hub');
        document.getElementById('deploy-title').innerText = "Modify Matrix";
        document.getElementById('form_admin_id').value = staff.id;
        document.getElementById('form_username').value = staff.username;
        document.getElementById('role_name_input').value = staff.role_name;
        document.getElementById('form_password').placeholder = "Key unchanged if blank";
        document.getElementById('submit-btn').innerText = "COMMIT UPDATES";
        document.getElementById('submit-btn').name = "update_admin_role";
        document.getElementById('cancel-btn').classList.remove('hidden');

        fetch(`handlers/role-handler.php?get_perms=1&role_id=${staff.role_id}`)
            .then(res => res.json())
            .then(data => {
                document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = data.includes(cb.value));
            });
    }

    function cancelEdit() {
        document.getElementById('rbacForm').reset();
        document.getElementById('deploy-title').innerText = "Neural Deploy";
        document.getElementById('form_admin_id').value = "";
        document.getElementById('submit-btn').innerText = "EXECUTE DEPLOY";
        document.getElementById('submit-btn').name = "deploy_admin_with_role";
        document.getElementById('cancel-btn').classList.add('hidden');
        document.getElementById('form_password').placeholder = "Access Password";
    }

    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active', 'bg-indigo-600', 'text-white'));
        document.getElementById(tabId).classList.remove('hidden');
        if(window.event) {
            window.event.currentTarget.classList.add('active', 'bg-indigo-600', 'text-white');
        } else {
            const activeBtn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick').includes(tabId));
            if(activeBtn) activeBtn.classList.add('active', 'bg-indigo-600', 'text-white');
        }
    }

    function confirmDelete(id, name) {
        if(name === "TURJO SARKER") return; 
        Swal.fire({ title: 'DELETE ADMIN?', text: `Erase ${name} from command?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#6366f1', cancelButtonColor: '#e11d48', confirmButtonText: 'DELETE', background: '#0d0915', color: '#fff' }).then((result) => {
            if (result.isConfirmed) window.location.href = 'handlers/role-handler.php?action=delete_staff&id=' + id;
        });
    }

    let timeLeft = 900;
    setInterval(() => {
        let mins = Math.floor(timeLeft / 60); let secs = timeLeft % 60;
        const timer = document.getElementById('session-timer');
        if(timer) timer.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        if (timeLeft <= 0) window.location.href = 'logout.php?reason=timeout';
        timeLeft--;
    }, 1000);
</script>

<?php include 'includes/footer.php'; ?>