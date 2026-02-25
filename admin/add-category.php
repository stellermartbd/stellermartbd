<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
require_once '../core/functions.php'; 

if (!hasPermission($conn, 'product.manage')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Recursive Function: সব লেভেলের ক্যাটাগরি ড্রপডাউনে দেখানোর জন্য
function displayCategoryOptions($conn, $parent_id = 0, $spacing = '', $user_level = 0) {
    $query = "SELECT id, name FROM categories WHERE parent_id = $parent_id ORDER BY name ASC";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . $spacing . ' ➔ ' . $row['name'] . '</option>';
            // নিজের ভেতর আবার খোঁজা (Deep Level)
            displayCategoryOptions($conn, $row['id'], $spacing . '---', $user_level + 1);
        }
    }
}
?>

<style>
    :root { --accent-rose: #e11d48; }
    .glass-panel { background: rgba(17, 12, 29, 0.7); backdrop-filter: blur(20px); border-radius: 3rem; border: 1px solid rgba(255, 255, 255, 0.05); }
    .matrix-input { background: #000 !important; border: 1px solid rgba(255, 255, 255, 0.15) !important; color: #fff !important; border-radius: 1.2rem !important; padding: 16px 25px !important; font-size: 13px; font-weight: 700; }
    .matrix-input:focus { border-color: var(--accent-rose) !important; box-shadow: 0 0 20px rgba(225, 29, 72, 0.2); }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col p-8">
    <header class="h-24 flex items-center justify-between px-10 bg-[#110c1d]/90 rounded-3xl mb-12 border-b border-white/5">
        <div class="flex items-center gap-6">
            <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                <i class="fas fa-folder-plus text-rose-500 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white uppercase italic">Deep Category <span class="text-rose-500">Matrix</span></h2>
                <p class="text-[9px] text-gray-500 font-black uppercase tracking-[0.4em]">Fashion > Men > Shirt Logic</p>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto flex flex-col items-center">
        <div class="w-full max-w-[850px] space-y-8 pb-20">
            <div class="glass-panel p-12 shadow-2xl">
                <form action="handlers/category-handler.php" method="POST" class="space-y-10">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Category/Sub-Category Name</label>
                            <input type="text" name="name" id="cat_name" required placeholder="e.g. Shirt or Pant" class="matrix-input w-full outline-none">
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Select Parent (Hierarchy)</label>
                            <select name="parent_id" class="matrix-input w-full outline-none appearance-none cursor-pointer">
                                <option value="0">ROOT (Main Category)</option>
                                <?php displayCategoryOptions($conn); ?>
                            </select>
                            <p class="text-[8px] text-rose-400 font-bold uppercase mt-2 italic">* Select 'Men' to add 'Shirt' inside it</p>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Neural Slug (Auto)</label>
                            <input type="text" name="slug" id="cat_slug" readonly class="matrix-input w-full bg-black/50 text-gray-500 border-dashed">
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Node Status</label>
                            <select name="status" class="matrix-input w-full outline-none">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="add_category" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-6 rounded-[2rem] shadow-xl transition-all uppercase tracking-[0.4em] text-[10px]">
                        <i class="fas fa-network-wired mr-2"></i> Sync Into Matrix
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    document.getElementById('cat_name').addEventListener('input', function() {
        let slug = this.value.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
        document.getElementById('cat_slug').value = slug;
    });
</script>
<?php include 'includes/footer.php'; ?>