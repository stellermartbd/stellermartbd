<?php 
/**
 * Prime Admin - Category Matrix Hub (V6.5 - Edit/Delete Ready)
 * Project: Turjo Site | Logic: Neural Taxonomy Sync & Edit Node
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// 🔥 Security Guard - আপনার ইউজার সেন্টার পেজের লজিক অনুযায়ী
if (!hasPermission($conn, 'product.manage')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

// ১. ক্যাটাগরি আপডেট লজিক (Edit Logic)
if (isset($_POST['update_category'])) {
    $id = (int)$_POST['cat_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $conn->query("UPDATE categories SET name = '$name', status = '$status' WHERE id = $id");
    header("Location: categories.php?success=Node+Updated");
    exit();
}

// ২. ক্যাটাগরি ডিলিট লজিক
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // ডিলিট করলে এর সাব-ক্যাটাগরিও মুছে যাবে
    $conn->query("DELETE FROM categories WHERE id = $id OR parent_id = $id");
    header("Location: categories.php?success=Node+Deleted");
    exit();
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

/**
 * 🌳 Recursive Tree Function
 */
function renderCategoryTree($conn, $parent_id = 0, $indent = 0) {
    $sql = "SELECT * FROM categories WHERE parent_id = $parent_id ORDER BY name ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $padding = $indent * 30;
            $is_parent = ($row['parent_id'] == 0);
            ?>
            <tr class="hover:bg-white/[0.03] transition-all group">
                <td class="pl-12">
                    <div class="flex items-center gap-4" style="padding-left: <?php echo $padding; ?>px;">
                        <div class="w-8 h-8 rounded-lg <?php echo $is_parent ? 'bg-rose-600/20 text-rose-500 border-rose-500/20' : 'bg-blue-600/10 text-blue-500 border-blue-500/10'; ?> flex items-center justify-center font-black border">
                            <?php echo $is_parent ? '<i class="fas fa-folder"></i>' : '<i class="fas fa-arrow-right text-[10px]"></i>'; ?>
                        </div>
                        <span class="text-sm font-black <?php echo $is_parent ? 'text-white' : 'text-gray-300'; ?> group-hover:text-rose-500 transition">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </span>
                    </div>
                </td>
                <td class="text-[11px] text-gray-500 font-mono italic">/<?php echo $row['slug']; ?></td>
                <td class="text-center">
                    <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase <?php echo ($row['status'] == 'Active') ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500'; ?>">
                        ● <?php echo $row['status']; ?>
                    </span>
                </td>
                <td class="text-right pr-12">
                    <div class="flex items-center justify-end gap-3">
                        <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="w-9 h-9 rounded-xl bg-blue-600/5 text-blue-500 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-all border border-blue-500/10">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('নিশ্চিত? এর নিচের সব সাব-ক্যাটাগরিও ডিলিট হবে!')" class="w-9 h-9 rounded-xl bg-rose-600/5 text-rose-500 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-all border border-rose-500/10">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
            renderCategoryTree($conn, $row['id'], $indent + 1);
        }
    }
}
?>

<style>
    :root { --accent-rose: #e11d48; }
    .glass-panel { background: rgba(17, 12, 29, 0.6); backdrop-filter: blur(20px); border-radius: 3rem; border: 1px solid rgba(255, 255, 255, 0.05); }
    .matrix-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; align-items: center; justify-content: center; }
    .modal-content { background: #110c1d; width: 100%; max-width: 500px; padding: 40px; border-radius: 2.5rem; border: 1px solid rgba(255, 255, 255, 0.05); }
    .matrix-input { background: #000 !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-radius: 1rem !important; padding: 12px 20px !important; font-size: 13px; font-weight: 700; width: 100%; outline: none; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col transition-all duration-700">
    <header class="h-24 flex items-center justify-between px-12 bg-[#110c1d] border-b border-white/5 sticky top-0 z-50 shrink-0">
        <div class="flex items-center gap-8">
            <div class="p-4 bg-rose-600/10 rounded-2xl border border-rose-600/20 shadow-lg">
                <i class="fas fa-network-wired text-rose-600 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter ">CATEGORY <span class="text-rose-500">MANAGEMENT</span></h2>
                <p class="text-[9px] font-bold text-gray-500 uppercase tracking-[0.4em] mt-2">Neural Nodes: Edit & Delete Interface</p>
            </div>
        </div>
        <button onclick="location.href='add-category.php'" class="bg-rose-600 hover:bg-rose-700 px-8 py-3.5 rounded-2xl text-[10px] font-black text-white uppercase tracking-widest transition-all shadow-xl active:scale-95">
            <i class="fas fa-plus mr-2"></i> CREATE NEW CATEGORY
        </button>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar px-12 pb-32 pt-10">
        <div class="glass-panel max-w-[1600px] mx-auto shadow-2xl overflow-hidden">
            <table class="w-full text-left border-collapse matrix-table">
                <thead>
                    <tr class="bg-white/[0.02]">
                        <th class="pl-12 py-5 text-[9px] uppercase font-black text-gray-500 tracking-widest">CATEGORY MANAGEMENT</th>
                        <th class="py-5 text-[9px] uppercase font-black text-gray-500 tracking-widest">Neural Slug</th>
                        <th class="text-center py-5 text-[9px] uppercase font-black text-gray-500 tracking-widest">Status</th>
                        <th class="text-right pr-12 py-5 text-[9px] uppercase font-black text-gray-500 tracking-widest">Command</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php renderCategoryTree($conn); ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="editModal" class="matrix-modal">
    <div class="modal-content shadow-2xl">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-xl font-black text-white uppercase tracking-tighter italic">Edit <span class="text-rose-500">Node</span></h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-rose-500 transition"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="cat_id" id="edit_id">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">New Identity Name</label>
                <input type="text" name="name" id="edit_name" required class="matrix-input">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Matrix Status</label>
                <select name="status" id="edit_status" class="matrix-input appearance-none">
                    <option value="Active">🟢 Active</option>
                    <option value="Inactive">🔴 Inactive</option>
                </select>
            </div>
            <button type="submit" name="update_category" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl text-[10px] uppercase tracking-widest transition-all shadow-lg active:scale-95">
                Execute Matrix Sync
            </button>
        </form>
    </div>
</div>

<script>
    function openEditModal(category) {
        document.getElementById('edit_id').value = category.id;
        document.getElementById('edit_name').value = category.name;
        document.getElementById('edit_status').value = category.status;
        document.getElementById('editModal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    // মডালের বাইরে ক্লিক করলে বন্ধ হবে
    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) closeModal();
    }
</script>

<?php include 'includes/footer.php'; ?>