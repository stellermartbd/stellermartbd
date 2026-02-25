<?php 
require_once '../core/db.php'; 
require_once '../core/functions.php'; // Security & Permission engine load

/**
 * 🔥 Module Level Security:
 * Check if the user has at least 'view' permission for 'product_manage'.
 * If not, they are kicked out to the dashboard.
 */
if (!hasPermission($conn, 'product_manage', 'view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0 transition-all duration-300">
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-theme-dark/80 backdrop-blur-md border-b border-gray-200 dark:border-[#251d33] sticky top-0 z-20 shrink-0">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight">Product Manage</h2>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Inventory Control</p>
        </div>
        
        <div class="flex items-center gap-4">
            <button id="theme-toggle" class="w-10 h-10 rounded-full bg-white dark:bg-theme-card border border-gray-200 dark:border-theme-border text-gray-500 dark:text-yellow-400 hover:text-rose-500 flex items-center justify-center transition shadow-sm">
                <i class="fas fa-moon dark:hidden"></i><i class="fas fa-sun hidden dark:block"></i>
            </button>
            <div class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold shadow-lg uppercase">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'T', 0, 1)); ?>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar">
        <div class="p-8 w-full max-w-[1600px] mx-auto space-y-6">
            
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Products List</h1>
                
                <?php if (hasPermission($conn, 'product_manage', 'add')): ?>
                <a href="add-product.php" class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition shadow-lg shadow-rose-600/20">
                    <i class="fas fa-plus"></i> Add Product
                </a>
                <?php endif; ?>
            </div>

            <div class="glass-panel bg-white dark:bg-theme-card rounded-3xl border border-gray-100 dark:border-theme-border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b dark:border-theme-border bg-gray-50/50 dark:bg-white/5">
                                <th class="p-4 pl-6">Product Info</th>
                                <th class="p-4">Category</th>
                                <th class="p-4">Price</th>
                                <th class="p-4">Stock Status</th>
                                <th class="p-4 text-right pr-6">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-theme-border align-middle">
                            <?php
                            $sql = "SELECT p.*, c.name as cat_name FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC";
                            $res = $conn->query($sql);
                            while($row = $res->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition group">
                                <td class="p-4 pl-6 flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-white/10 p-1.5 border dark:border-transparent">
                                        <img src="../public/uploads/<?php echo $row['image']; ?>" class="w-full h-full object-contain">
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-800 dark:text-gray-200"><?php echo $row['name']; ?></p>
                                        <p class="text-[10px] text-gray-400 font-medium">SKU: <?php echo $row['sku']; ?></p>
                                    </div>
                                </td>
                                <td class="p-4 text-sm font-bold text-gray-500 dark:text-gray-400"><?php echo $row['cat_name']; ?></td>
                                <td class="p-4 font-black text-gray-800 dark:text-white"><?php echo formatPrice($row['price']); ?></td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full <?php echo ($row['stock'] < 5) ? 'bg-rose-500 w-[20%]' : 'bg-green-500 w-[85%]'; ?>"></div>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase <?php echo ($row['stock'] < 5) ? 'text-rose-500' : 'text-gray-400'; ?>">
                                            <?php echo $row['stock']; ?> Units Left
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-right pr-6">
                                    <div class="flex items-center justify-end gap-2">
                                        
                                        <?php if (hasPermission($conn, 'product_manage', 'edit')): ?>
                                        <a href="edit-product.php?id=<?php echo $row['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10 text-gray-400 hover:text-blue-500 transition">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if (hasPermission($conn, 'product_manage', 'delete')): ?>
                                        <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')" 
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10 text-gray-400 hover:text-rose-500 transition">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                        <?php endif; ?>

                                        <?php if (!hasPermission($conn, 'product_manage', 'edit') && !hasPermission($conn, 'product_manage', 'delete')): ?>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase italic">Read Only</span>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete " + name + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#1e162e',
        confirmButtonText: 'Yes, delete it!',
        background: '#161021',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'handlers/product-handler.php?action=delete&id=' + id;
        }
    })
}
</script>

<?php include 'includes/footer.php'; ?>