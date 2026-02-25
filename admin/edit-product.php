<?php
// ১. ডাটাবেস, ফাংশন ও সিকিউরিটি চেক
require_once __DIR__ . '/../core/db.php'; 
require_once __DIR__ . '/../core/functions.php'; 
require_once __DIR__ . '/../core/csrf.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

error_reporting(E_ALL);
ini_set('display_errors', 1);

// লগইন চেক
if (!isset($_SESSION['admin_logged_in'])) { 
    header('Location: login.php'); 
    exit; 
}

// ২. প্রোডাক্ট ডাটা ফেচ করা
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) { 
    header('Location: products.php'); 
    exit; 
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { 
    header('Location: products.php'); 
    exit; 
}

// গ্যালারি ইমেজগুলো ফেচ করা
$gallery_stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
$gallery_stmt->bind_param("i", $id);
$gallery_stmt->execute();
$gallery_images = $gallery_stmt->get_result();

$errors = [];

// ৩. আপডেট হ্যান্ডেলিং
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $discount_price = floatval($_POST['discount_price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $custom_style = $_POST['custom_style'] ?? '';

    if (empty($name)) { 
        $errors[] = 'Product title is required.'; 
    }

    if (empty($errors)) {
        $image = $product['image'];
        $target_dir = "../public/uploads/";

        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $file_name = time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                $image = $file_name;
            }
        }

        $sql = "UPDATE products SET name=?, slug=?, description=?, price=?, discount_price=?, stock=?, image=?, custom_style=? WHERE id=?";
        $up_stmt = $conn->prepare($sql);
        $up_stmt->bind_param("sssddissi", $name, $slug, $desc, $price, $discount_price, $stock, $image, $custom_style, $id);
        
        if($up_stmt->execute()){
            if (!empty($_FILES['gallery_images']['name'][0])) {
                foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['gallery_images']['error'][$key] === 0) {
                        $g_ext = pathinfo($_FILES["gallery_images"]["name"][$key], PATHINFO_EXTENSION);
                        $g_file_name = time() . '_gal_' . uniqid() . '.' . $g_ext;
                        if (move_uploaded_file($tmp_name, $target_dir . $g_file_name)) {
                            $g_sql = "INSERT INTO product_images (product_id, image_url) VALUES (?, ?)";
                            $g_stmt = $conn->prepare($g_sql);
                            $g_stmt->bind_param("is", $id, $g_file_name);
                            $g_stmt->execute();
                        }
                    }
                }
            }
            echo "<script>window.location.href='products.php?success=updated';</script>";
            exit;
        } else {
            $errors[] = "Database Error: " . $conn->error;
        }
    }
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0">
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-theme-dark/80 backdrop-blur-md border-b sticky top-0 z-20 shrink-0">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight">Edit Product</h2>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
        <div class="max-w-[1200px] mx-auto space-y-6">
            <a href="products.php" class="text-xs font-bold text-gray-400 hover:text-rose-500 uppercase tracking-widest">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>

            <?php if (!empty($errors)): ?>
                <div class="bg-rose-100 border-l-4 border-rose-500 p-4 mb-4">
                    <?php foreach($errors as $err) echo "<p class='text-rose-700 font-bold text-xs uppercase'>$err</p>"; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
                <div class="lg:col-span-2 space-y-6">
                    <div class="glass-panel p-8 bg-white dark:bg-theme-card rounded-3xl shadow-sm space-y-5 border dark:border-theme-border">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b dark:border-theme-border pb-4">Product Info</h3>
                        
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Product Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($product['name'] ?? ''); ?>" required class="w-full bg-gray-50 dark:bg-theme-dark border dark:border-theme-border rounded-2xl px-5 py-4 dark:text-white font-bold text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Discounted Price (৳)</label>
                                <input type="number" step="0.01" name="price" value="<?= $product['price']; ?>" required class="w-full bg-gray-50 dark:bg-theme-dark border dark:border-theme-border rounded-2xl px-5 py-4 dark:text-white font-bold text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-rose-500 mb-2 uppercase tracking-widest">Old Price (৳)</label>
                                <input type="number" step="0.01" name="discount_price" value="<?= $product['discount_price']; ?>" class="w-full bg-rose-500/5 border border-rose-500/20 rounded-2xl px-5 py-4 dark:text-white font-bold text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Slug (URL)</label>
                            <input type="text" name="slug" value="<?= htmlspecialchars($product['slug'] ?? ''); ?>" required class="w-full bg-gray-50 dark:bg-theme-dark border dark:border-theme-border rounded-2xl px-5 py-4 dark:text-white font-bold text-sm">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Description</label>
                            <textarea name="description" rows="5" class="w-full bg-gray-50 dark:bg-theme-dark border dark:border-theme-border rounded-2xl px-5 py-4 dark:text-white text-sm"><?= htmlspecialchars($product['description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="glass-panel p-8 bg-white dark:bg-theme-card border dark:border-theme-border rounded-3xl shadow-sm space-y-6">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b dark:border-theme-border pb-4">Gallery Photos</h3>
                        <div class="grid grid-cols-4 gap-4" id="gallery-container">
                            <?php while($g_img = $gallery_images->fetch_assoc()): ?>
                                <div class="relative group aspect-square rounded-xl overflow-hidden border dark:border-theme-border bg-gray-50">
                                    <img src="../public/uploads/<?= $g_img['image_url']; ?>" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-rose-600/80 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                        <button type="button" onclick="deleteGalleryImg(<?= $g_img['id']; ?>, this)" class="text-white text-xs font-black uppercase tracking-tighter">Remove</button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-rose-500 mb-3 uppercase tracking-widest">Add More Photos</label>
                            <input type="file" name="gallery_images[]" multiple class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-rose-600 file:text-white hover:file:bg-rose-700 cursor-pointer">
                        </div>
                    </div>

                    <div class="glass-panel p-8 bg-white dark:bg-theme-card border dark:border-theme-border rounded-3xl shadow-sm">
                        <h3 class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-4">Design Intelligence</h3>
                        <textarea name="custom_style" rows="6" class="w-full bg-gray-900 text-green-400 font-mono text-xs p-5 rounded-2xl"><?= htmlspecialchars($product['custom_style'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="glass-panel p-8 bg-white dark:bg-theme-card border dark:border-theme-border rounded-3xl shadow-sm text-center">
                        <img id="preview" src="../public/uploads/<?= $product['image']; ?>" class="w-full aspect-square object-contain rounded-2xl bg-gray-50 dark:bg-theme-dark p-2 border mb-4">
                        <input type="file" name="image" id="imgInp" class="hidden" accept="image/*">
                        <label for="imgInp" class="bg-rose-600 text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase cursor-pointer transition-all hover:bg-rose-700">Change Photo</label>
                    </div>

                    <div class="glass-panel p-8 bg-white dark:bg-theme-card border dark:border-theme-border rounded-3xl shadow-sm">
                        <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Stock</label>
                        <input type="number" name="stock" value="<?= $product['stock']; ?>" class="w-full bg-gray-50 dark:bg-theme-dark border dark:border-theme-border rounded-2xl px-5 py-4 dark:text-white font-bold text-sm">
                    </div>

                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-5 rounded-2xl shadow-xl uppercase tracking-widest text-xs transition-all">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function deleteGalleryImg(id, btn) {
        if(confirm('Are you sure you want to remove this image?')){
            fetch('handlers/gallery-handler.php?action=delete&id=' + id)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    btn.closest('.relative').remove();
                }
            });
        }
    }

    const imgInp = document.getElementById('imgInp');
    const preview = document.getElementById('preview');
    if (imgInp) {
        imgInp.onchange = evt => {
            const [file] = imgInp.files;
            if (file) { 
                preview.src = URL.createObjectURL(file); 
            }
        }
    }
</script>

<?php include 'includes/footer.php'; ?>