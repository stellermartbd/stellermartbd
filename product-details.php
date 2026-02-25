<?php 
/**
 * Project: Kena Kata - Premium Product UI
 * Updated: Rounded corners, Dynamic Size/Color Section & 5-column Related Products
 */
require_once 'core/db.php'; 
include 'templates/header.php'; 

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<script>window.location.href='index.php';</script>"; exit;
    }
}

// গ্যালারি এবং রিভিউ ফেচিং লজিক (অপরিবর্তিত)
$gallery_stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
$gallery_stmt->bind_param("i", $id);
$gallery_stmt->execute();
$gallery_res = $gallery_stmt->get_result();

$review_stmt = $conn->prepare("SELECT * FROM product_reviews WHERE product_id = ? AND status = 'approved' ORDER BY created_at DESC");
$review_stmt->bind_param("i", $id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();

$raw_desc = $product['description'] ?? ''; 
$clean_desc = html_entity_decode($raw_desc, ENT_QUOTES, 'UTF-8');
$smart_desc = preg_replace("/(\r\n|\n|\r){3,}/", "\n\n", $clean_desc);
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    :root { --sky-blue: #083b66; }

    /* ইমেজের কোণা রাউন্ড করার জন্য স্পেশাল ক্লাস */
    .main-img-wrapper { border-radius: 30px; overflow: hidden; border: 1px solid #f1f5f9; background: #fff; }
    .main-img-wrapper img { border-radius: 20px; width: 100%; height: auto; object-fit: contain; }

    /* সিলেকশন চিপস ডিজাইন */
    .selection-option { padding: 10px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.3s; }
    .selection-option:hover { border-color: #3b82f6; color: #3b82f6; }
    .selection-option.active { border-color: #3b82f6; background-color: #eff6ff; color: #3b82f6; }

    .product-card { border-radius: 20px; transition: 0.3s; border: 1px solid #f1f5f9; background: #fff; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
</style>

<div class="container mx-auto px-4 py-10 max-w-7xl">
    <nav class="flex text-[13px] text-gray-400 mb-8 font-medium">
        <a href="index.php" class="hover:text-blue-600">Home</a>
        <span class="mx-3">/</span> <span>Product</span>
        <span class="mx-3">/</span> <span class="text-slate-900"><?php echo htmlspecialchars($product['name'] ?? ''); ?></span>
    </nav>

    <div class="bg-white rounded-[32px] p-6 lg:p-12 shadow-sm border border-gray-50 mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <div class="lg:col-span-5">
                <div class="main-img-wrapper p-6 flex items-center justify-center">
                    <img src="public/uploads/<?php echo $product['image']; ?>" id="mainImage" alt="Product Image">
                </div>
                <div class="flex gap-4 mt-6 overflow-x-auto pb-2 scrollbar-hide">
                    <div class="w-20 h-20 border-2 border-blue-600 rounded-2xl p-1 cursor-pointer bg-white thumb-img" onclick="changeImage('public/uploads/<?php echo $product['image']; ?>', this)">
                        <img src="public/uploads/<?php echo $product['image']; ?>" class="w-full h-full object-contain rounded-xl">
                    </div>
                    <?php while($img = $gallery_res->fetch_assoc()): ?>
                    <div class="w-20 h-20 border border-gray-100 rounded-2xl p-1 cursor-pointer hover:border-blue-400 bg-white thumb-img" onclick="changeImage('public/uploads/<?php echo $img['image_url']; ?>', this)">
                        <img src="public/uploads/<?php echo $img['image_url']; ?>" class="w-full h-full object-contain rounded-xl">
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="lg:col-span-7">
                <h1 class="text-4xl font-black text-slate-900 mb-4 tracking-tight"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h1>
                
                <div class="flex items-center gap-2 mb-8">
                    <div class="flex text-yellow-400 text-sm">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <span class="text-[12px] font-bold text-blue-600 uppercase tracking-widest"><?php echo $reviews->num_rows; ?> Customer Reviews</span>
                </div>

                <?php if(!empty($product['available_sizes']) || !empty($product['available_colors'])): ?>
                <div class="mb-10 p-8 bg-slate-50 rounded-[24px] border border-slate-100 space-y-8">
                    <?php if(!empty($product['available_sizes'])): ?>
                    <div>
                        <h3 class="text-[11px] font-black uppercase tracking-[2px] text-slate-400 mb-4">Available Sizes</h3>
                        <div class="flex flex-wrap gap-3">
                            <?php 
                            $sizes = explode(',', $product['available_sizes']);
                            foreach($sizes as $size): ?>
                                <div class="selection-option bg-white"><?php echo trim($size); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($product['available_colors'])): ?>
                    <div>
                        <h3 class="text-[11px] font-black uppercase tracking-[2px] text-slate-400 mb-4">Available Colors</h3>
                        <div class="flex flex-wrap gap-3">
                            <?php 
                            $colors = explode(',', $product['available_colors']);
                            foreach($colors as $color): ?>
                                <div class="selection-option bg-white"><?php echo trim($color); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <div class="border-2 border-blue-600 rounded-[20px] p-6 bg-blue-50/30">
                        <p class="text-4xl font-black text-slate-900">৳<?php echo number_format($product['price']); ?></p>
                        <p class="text-[11px] text-blue-600 font-black uppercase tracking-widest mt-2">Special Cash Price</p>
                    </div>
                    <div class="border border-slate-100 rounded-[20px] p-6 bg-slate-50/50">
                        <p class="text-4xl font-black text-slate-300 line-through">৳<?php echo number_format($product['price'] + 250); ?></p>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mt-2">Regular Price</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center border-2 border-slate-100 rounded-2xl h-16 bg-white px-3">
                        <button onclick="changeQty(-1)" class="w-10 h-10 font-bold text-slate-400 hover:text-blue-600">-</button>
                        <span id="qtyVal" class="px-8 font-black text-xl text-slate-800">1</span>
                        <button onclick="changeQty(1)" class="w-10 h-10 font-bold text-slate-400 hover:text-blue-600">+</button>
                    </div>
                    <button onclick="addToBag()" class="h-16 w-16 border-2 border-blue-600 text-blue-600 rounded-2xl hover:bg-blue-50 transition flex items-center justify-center shadow-sm">
                        <i class="fas fa-shopping-bag text-xl"></i>
                    </button>
                    <button onclick="directCheckout()" class="bg-[#083b66] h-16 text-white px-12 rounded-2xl font-black uppercase text-[12px] flex-1 hover:bg-slate-900 shadow-xl transition-all hover:-translate-y-1">
                        Buy Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-20">
        <div class="flex items-center justify-between mb-10 border-b border-slate-100 pb-6">
            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Related Products</h2>
            <a href="index.php" class="text-blue-600 text-xs font-black uppercase tracking-widest border-b-2 border-blue-600 pb-1">View All</a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
            <?php 
            // ৫টি রিলেটেড প্রোডাক্ট দেখানো হচ্ছে
            $rel_query = "SELECT * FROM products WHERE id != $id AND status = 'Live' LIMIT 5";
            $rel_res = $conn->query($rel_query);
            if($rel_res && $rel_res->num_rows > 0):
                while($row = $rel_res->fetch_assoc()):
            ?>
                <article class="product-card p-4 flex flex-col h-full group">
                    <div class="img-wrapper cursor-pointer aspect-square bg-slate-50 rounded-2xl flex items-center justify-center p-4 mb-4" onclick="window.location.href='product-details.php?id=<?php echo $row['id']; ?>'">
                        <img src="public/uploads/<?php echo $row['image']; ?>" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="flex flex-col flex-grow">
                        <h3 class="text-[13px] font-bold text-slate-800 line-clamp-2 mb-3 group-hover:text-blue-600 uppercase leading-tight"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="mt-auto">
                            <p class="text-[18px] font-black text-slate-900 mb-4">৳<?php echo number_format($row['price']); ?></p>
                            <button onclick="window.location.href='product-details.php?id=<?php echo $row['id']; ?>'" class="w-full py-3 bg-slate-100 text-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                View Details
                            </button>
                        </div>
                    </div>
                </article>
            <?php endwhile; endif; ?>
        </div>
    </div>
</div>

<script>
    function changeQty(v) {
        let q = parseInt(document.getElementById('qtyVal').innerText);
        if(q + v > 0) document.getElementById('qtyVal').innerText = q + v;
    }

    function changeImage(src, el) {
        const main = document.getElementById('mainImage');
        main.style.opacity = '0';
        setTimeout(() => { main.src = src; main.style.opacity = '1'; }, 200);
        document.querySelectorAll('.thumb-img').forEach(item => { 
            item.classList.remove('border-blue-600'); item.classList.add('border-gray-100'); 
        });
        el.classList.add('border-blue-600'); el.classList.remove('border-gray-100');
    }

    function directCheckout() {
        const qty = document.getElementById('qtyVal').innerText;
        window.location.href = `public/order.php?id=<?php echo $id; ?>&qty=${qty}`;
    }
</script>

<?php include 'templates/footer.php'; ?>