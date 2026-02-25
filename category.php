<?php 
// ১. পাথ এবং ডাটাবেস কানেকশন
require_once __DIR__ . '/core/db.php'; 

/**
 * রিকার্সিভ ফাংশন: এটি একটি প্যারেন্ট আইডির আন্ডারে থাকা সকল চাইল্ড আইডি খুঁজে বের করবে
 */
function get_all_child_ids($conn, $parent_id) {
    $ids = [$parent_id];
    $sql = "SELECT id FROM categories WHERE parent_id = $parent_id AND status = 'Active'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // প্রতিটি চাইল্ডের জন্য আবার তার চাইল্ড আছে কি না চেক করবে
            $ids = array_merge($ids, get_all_child_ids($conn, $row['id']));
        }
    }
    return array_unique($ids);
}

// ২. ক্যাটাগরি স্ল্যাগ থেকে আইডি এবং সকল চাইল্ড আইডি বের করার লজিক
$category_slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';
$category_name = "All Products";
$ids_string = "0";

if (!empty($category_slug)) {
    // স্ল্যাগ দিয়ে মেইন ক্যাটাগরি খুঁজে বের করা
    $cat_query = "SELECT id, name FROM categories WHERE slug = '$category_slug' LIMIT 1";
    $cat_result = mysqli_query($conn, $cat_query);
    $category_data = mysqli_fetch_assoc($cat_result);

    if ($category_data) {
        $category_id = $category_data['id'];
        $category_name = $category_data['name'];

        // রিকার্সিভ ফাংশন কল করে সকল সাব-ক্যাটাগরি আইডি নেওয়া
        $all_ids = get_all_child_ids($conn, $category_id);
        $ids_string = implode(',', $all_ids);
    }
}

// ৩. প্রোডাক্ট কুয়েরি (ইন্ডেক্স পেজের মতো সর্টিং সহ)
$order_by = "id DESC"; 
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'low-high': $order_by = "price ASC"; break;
        case 'high-low': $order_by = "price DESC"; break;
        default: $order_by = "id DESC";
    }
}

// IN ($ids_string) ব্যবহার করায় এখন মেইন মেনুতে সব প্রোডাক্ট শো করবে
$query = "SELECT * FROM products WHERE status = 'Live' AND category_id IN ($ids_string) ORDER BY $order_by";
$result = $conn->query($query);
$total_products = ($result) ? $result->num_rows : 0;

// ৪. হেডার ইনক্লুড করা
include __DIR__ . '/templates/header.php';
?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
    :root { --sky-blue: #083b66; }
    .product-card { background: #fff; border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; border: 1px solid #edf2f7; }
    .product-card:hover { box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1); transform: translateY(-4px); }
    .img-wrapper { position: relative; overflow: hidden; border-radius: 12px; margin-bottom: 0.75rem; cursor: pointer; }
    .discount-badge { position: absolute; top: 10px; left: 10px; background: #ef4444; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; z-index: 10; }
    .btn-action { font-size: 10px; font-weight: 700; border-radius: 8px; padding: 8px; width: 100%; transition: 0.2s; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-cart-minimal { background-color: #f1f5f9; color: var(--sky-blue); border: 1px solid #e2e8f0; }
    .btn-cart-minimal:hover { background-color: #e2e8f0; }
    .btn-order-now { background-color: var(--sky-blue); color: white; margin-top: 6px; }
    .btn-order-now:hover { background-color: #052a4a; }
</style>

<main class="container mx-auto px-4 md:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div class="flex flex-col border-l-4 border-sky-800 pl-4">
            <h2 class="text-2xl font-extrabold text-gray-800 uppercase leading-none"><?php echo htmlspecialchars($category_name); ?></h2>
            <span class="text-gray-400 font-medium text-xs mt-1"><?php echo $total_products; ?> Items Found</span>
        </div>
        <form method="GET" class="hidden md:block">
            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($category_slug); ?>">
            <select name="sort" onchange="this.form.submit()" class="bg-white border border-gray-200 text-sm px-4 py-2 rounded-lg outline-none focus:ring-1 focus:ring-sky-800">
                <option value="featured">Featured</option>
                <option value="low-high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'low-high') ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="high-low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'high-low') ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </form>
    </div>

    <div id="product-container" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-5">
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="product-card p-3 flex flex-col h-full group">
                    <div class="img-wrapper" onclick="window.location.href='product-details.php?id=<?php echo $row['id']; ?>'">
                        <?php if(!empty($row['discount_price']) && $row['discount_price'] > $row['price']): 
                             $percentage = round((($row['discount_price'] - $row['price']) / $row['discount_price']) * 100); ?>
                            <div class="discount-badge">-<?php echo $percentage; ?>%</div>
                        <?php endif; ?>
                        
                        <div class="aspect-square flex items-center justify-center p-2 bg-[#f9fafb]">
                            <img src="<?php echo $base_url; ?>public/uploads/<?php echo $row['image']; ?>" 
                                 class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 rounded-lg" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </div>
                    </div>

                    <div class="flex flex-col flex-grow">
                        <h3 class="text-xs md:text-[13px] font-bold text-gray-800 line-clamp-2 mt-1 group-hover:text-sky-800 transition-colors">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h3>
                        
                        <div class="flex items-center gap-1 mt-1">
                            <div class="text-[10px] text-orange-400">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <span class="text-[10px] text-gray-400">(<?php echo rand(1, 400); ?>)</span>
                        </div>

                        <div class="flex items-center gap-2 mt-2 mb-4">
                            <span class="text-[16px] font-black text-gray-900">৳<?php echo number_format($row['price']); ?></span>
                            <?php if(!empty($row['discount_price'])): ?>
                                <span class="text-[11px] text-gray-400 line-through">৳<?php echo number_format($row['discount_price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto space-y-2">
                            <button onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')"
                                    class="btn-action btn-cart-minimal">
                                <i class="fas fa-shopping-basket mr-1"></i> Add to Cart
                            </button>
                            
                            <button onclick="window.location.href='public/order.php?id=<?php echo $row['id']; ?>'"
                                    class="btn-action btn-order-now">
                                <i class="fas fa-bolt mr-1"></i> Order Now
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full py-24 text-center">
                <i class="fas fa-search text-5xl text-gray-100 mb-4"></i>
                <p class="text-gray-400 font-bold tracking-widest uppercase">No products found in this category.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /**
     * addToCart Function
     * fixes: Refresh charai real-time UI update trigger [cite: 2026-02-11]
     */
    function addToCart(id, name) {
        let formData = new URLSearchParams();
        formData.append('action', 'add');
        formData.append('id', id);
        formData.append('qty', 1);

        fetch('core/cart.php', {
            method: 'POST',
            body: formData.toString(),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // ১. ব্যাজ আপডেট [cite: 2026-02-11]
                const badge = document.getElementById('cart-count');
                if (badge) badge.innerText = data.total_items;
                
                // ২. প্রাইজ আপডেট [cite: 2026-02-11]
                const formattedPrice = '৳' + new Intl.NumberFormat().format(data.total_price);
                const sidebarTotal = document.getElementById('cart-total');
                const headerTotal = document.getElementById('cart-total-header');
                
                if (sidebarTotal) sidebarTotal.innerText = formattedPrice;
                if (headerTotal) headerTotal.innerText = formattedPrice;

                // ৩. লিস্ট কন্টেইনার আপডেট (Refresh chara list dekhabe) [cite: 2026-01-20]
                const cartItemsContainer = document.getElementById('cart-items');
                if (cartItemsContainer && data.cart_html) {
                    cartItemsContainer.innerHTML = data.cart_html;
                }

                // ৪. নোটিফিকেশন [cite: 2026-02-11]
                Swal.fire({
                    title: 'SUCCESSFULLY ADDED',
                    text: name + ' is now in your cart!',
                    icon: 'success',
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 2500,
                    background: '#fff',
                    color: '#083b66'
                });
            } else {
                Swal.fire({ title: 'FAILED', text: data.message, icon: 'error' });
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>