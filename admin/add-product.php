<?php
/**
 * Prime Beast - Professional Product Matrix (V5.0)
 * Project: Turjo Site | Products Hub BD
 * Features: Multi-Image Gallery, Real-time Preview, Beast Mode UI [cite: 2026-02-11]
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../core/db.php'; 
require_once '../core/functions.php'; 

// 🛡️ Admin Access Guard
if (!hasPermission($conn, 'product.manage')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

// Generate CSRF Token for Secure Deployment
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
    :root { --accent: #e11d48; --panel: #110c1d; --bg: #0a0514; }
    * { font-style: normal !important; }
    .glass-panel { background: rgba(17, 12, 29, 0.7); backdrop-filter: blur(20px); border-radius: 3rem; border: 1px solid rgba(255, 255, 255, 0.05); }
    .matrix-input { background: #000 !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-radius: 1.2rem !important; padding: 15px 25px !important; font-size: 13px; font-weight: 700; transition: 0.3s; }
    .matrix-input:focus { border-color: var(--accent) !important; box-shadow: 0 0 20px rgba(225, 29, 72, 0.2); }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
    .gallery-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-[#0a0514] flex flex-col p-8 transition-all duration-500">
    <header class="h-24 flex items-center justify-between px-10 bg-[#110c1d]/90 backdrop-blur-xl border-b border-white/5 sticky top-0 z-30 shrink-0 rounded-3xl mb-8">
        <div class="flex items-center gap-6">
            <div class="p-4 bg-white/5 rounded-2xl border border-white/10 shadow-lg"><i class="fas fa-plus-circle text-rose-500 text-2xl animate-pulse"></i></div>
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter">Add Product</h2>
                <p class="text-[9px] text-gray-500 font-black uppercase tracking-[0.4em] mt-3">Neural Inventory Sync v5.0</p>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <span class="block text-sm font-black text-white uppercase tracking-tight">Turjo Admin</span>
                <span class="block text-[9px] text-rose-500 font-bold uppercase tracking-widest">Supreme Entity</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-purple-600 p-[2px]">
                <div class="w-full h-full bg-[#0a0514] rounded-[14px] flex items-center justify-center font-black text-white"><?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?></div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar pr-4">
        <div class="w-full max-w-[1300px] mx-auto pb-20">
            <form action="handlers/product-handler.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="lg:col-span-8 space-y-10">
                    <div class="glass-panel p-10 space-y-10 shadow-2xl">
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.3em] border-b border-white/5 pb-6">Basic Information</h3>
                        <div class="space-y-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">Product Name</label>
                                <input type="text" name="name" required placeholder="Enter Product Name" class="matrix-input w-full outline-none">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">Regular Price (BDT)</label>
                                    <input type="number" name="price" required placeholder="0.00" class="matrix-input w-full outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">Old Price</label>
                                    <input type="number" name="discount_price" placeholder="0.00" class="matrix-input w-full outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">Full Description</label>
                                <textarea name="description" rows="8" class="matrix-input w-full outline-none custom-scrollbar"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel p-10 space-y-8 shadow-2xl">
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.3em] border-b border-white/5 pb-6">Inventory Matrix</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">Stock Unit (QTY)</label>
                                <input type="number" name="stock" value="10" class="matrix-input w-full outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">SKU Identity</label>
                                <input type="text" name="sku" placeholder="PRIME-NODE-001" class="matrix-input w-full outline-none uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-10">
                    <div class="glass-panel p-10 space-y-8 shadow-2xl">
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.3em] border-b border-white/5 pb-6">Organization</h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">Category Node</label>
                                <select name="category_id" required class="matrix-input w-full outline-none category-select">
                                    <option value="">Select Category</option>
                                    <?php $cats = $conn->query("SELECT * FROM categories WHERE status = 'Active'"); while($c = $cats->fetch_assoc()) { echo "<option value='{$c['id']}'>{$c['name']}</option>"; } ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 mb-3 uppercase tracking-widest">Product Type</label>
                                <select name="is_digital" class="matrix-input w-full outline-none">
                                    <option value="0">📦 Physical Goods</option>
                                    <option value="1">🔐 Digital Product</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel p-10 space-y-6 shadow-2xl">
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.3em] border-b border-white/5 pb-6">Media Matrix</h3>
                        
                        <div class="border-2 border-dashed border-white/5 hover:border-rose-500/50 rounded-3xl p-4 text-center transition-all bg-black/40 group">
                            <input type="file" name="image" id="imgInp" required class="hidden" accept="image/*">
                            <label for="imgInp" class="cursor-pointer block">
                                <img id="preview" src="https://via.placeholder.com/300?text=Main+Image" class="w-full aspect-square object-contain rounded-2xl bg-white/5 mb-3">
                                <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest">Set Main Image</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Gallery Matrix (Multiple)</label>
                            <div class="matrix-input flex items-center gap-3">
                                <input type="file" name="gallery_images[]" id="galleryInp" multiple class="hidden" accept="image/*">
                                <label for="galleryInp" class="cursor-pointer flex items-center gap-2 text-rose-500">
                                    <i class="fas fa-images"></i> <span class="text-[11px]">Select Photos</span>
                                </label>
                            </div>
                            <div id="gallery-preview" class="gallery-preview-grid mt-4"></div>
                        </div>
                    </div>

                    <button type="submit" name="add_product" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-6 rounded-[2rem] shadow-2xl transition-all flex items-center justify-center gap-4 uppercase tracking-[0.4em] text-[10px]">
                        <i class="fas fa-check-circle text-lg"></i> Upload Product 
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // মেইন ইমেজ প্রিভিউ [cite: 2026-02-11]
    document.getElementById('imgInp').onchange = evt => {
        const [file] = evt.target.files;
        if (file) document.getElementById('preview').src = URL.createObjectURL(file);
    }

    // গ্যালারি প্রিভিউ লজিক [cite: 2026-02-21]
    document.getElementById('galleryInp').onchange = function() {
        const preview = document.getElementById('gallery-preview');
        preview.innerHTML = '';
        if (this.files) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = "w-full aspect-square rounded-xl bg-white/5 border border-white/10 overflow-hidden";
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }
</script>

<?php include 'includes/footer.php'; ?>