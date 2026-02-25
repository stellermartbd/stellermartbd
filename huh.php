<?php 
// ১. পাথ এবং ডাটাবেস কানেকশন (লজিক অপরিবর্তিত)
require_once __DIR__ . '/core/db.php'; 
include __DIR__ . '/templates/header.php'; 

// ২. ডাইনামিক ফিল্টারিং ও সার্চ লজিক
$order_by = "id DESC"; 
$search_condition = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_condition = " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

if (isset($_GET['sort'])) {
    $sort = $_GET['sort'];
    switch ($sort) {
        case 'low-high': $order_by = "price ASC"; break;
        case 'high-low': $order_by = "price DESC"; break;
        default: $order_by = "id DESC";
    }
}

// ৩. ডাটাবেস কুয়েরি
$query = "SELECT * FROM products WHERE status = 'Live' $search_condition ORDER BY $order_by";
$result = $conn->query($query);
?>

<style>
    /* Contrast Fix: টেক্সট কালার আরও গাঢ় করা হয়েছে যেন সহজে পড়া যায় */
    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; color: #1a202c; }
    
    /* হেডার এবং টাইটেল কালার ফিক্স */
    .section-title { color: #0f172a !important; font-weight: 800; }
    .product-count { color: #334155 !important; font-weight: 600; } 

    /* প্রোডাক্ট কার্ড স্টাইল */
    .product-card {
        background: #fff;
        border: 1px solid #cbd5e0; /* Border contrast fix */
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    
    .img-container {
        background: #fdfdfd;
        aspect-ratio: 1/1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
        position: relative;
        border-bottom: 1px solid #e2e8f0;
    }

    /* টেক্সট এবং ব্যাজ কালার অপ্টিমাইজেশন */
    .product-name { color: #1a202c !important; font-weight: 700; }
    .text-muted-custom { color: #334155 !important; font-weight: 700; } /* Contrast Fix for star count */
    .price-main { color: #000000 !important; font-weight: 800; }
    .price-old { color: #64748b !important; text-decoration: line-through; font-weight: 600; } /* Contrast Fix for old price */

    /* Instant Delivery Contrast Fix */
    .delivery-status { color: #9a3412 !important; font-weight: 800; } 

    .btn-add-cart {
        width: 100%;
        padding: 10px;
        background: #f1f5f9;
        border: 1px solid #94a3b8;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        color: #1e293b;
        text-transform: uppercase;
        transition: 0.3s;
    }
    .btn-add-cart:hover { background: #e11d48; color: white; border-color: #e11d48; }
</style>

<nav class="top-nav" style="background-color: #1a202c; color: #cbd5e0; font-size: 13px; padding: 12px 0;">
    <div class="container mx-auto px-4 flex items-center overflow-x-auto whitespace-nowrap scrollbar-hide">
        <a href="#" style="margin: 0 12px;">All Categories</a>
        <a href="#" style="margin: 0 12px;">AI Tools</a>
        <a href="#" style="margin: 0 12px;">Creative Tools</a>
        <a href="#" style="margin: 0 12px;">Developer Tools</a>
    </div>
</nav>

<main class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-2">
            <span class="text-orange-600 text-xl" aria-hidden="true">✦</span>
            <h2 class="text-xl font-bold section-title">
                All Products <span class="product-count text-sm font-normal ml-1"><?php echo ($result) ? $result->num_rows : 0; ?> products</span>
            </h2>
        </div>
        
        <div class="flex items-center gap-2">
            <form method="GET" id="sortForm">
                <select name="sort" aria-label="Sort products" onchange="this.form.submit()" class="bg-white border border-gray-400 text-gray-800 text-xs font-bold px-4 py-2 rounded-md outline-none cursor-pointer">
                    <option value="featured">Featured</option>
                    <option value="low-high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'low-high') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="high-low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'high-low') ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
            </form>
            <button class="bg-white border border-gray-400 p-2 rounded-md text-gray-700" aria-label="Filter products">
                <i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">
        <?php if($result && $result->num_rows > 0): ?>
            <?php 
            $count = 0;
            while($row = $result->fetch_assoc()): 
                $count++;
            ?>
                <div class="product-card group">
                    <a href="product-details.php?id=<?php echo $row['id']; ?>" class="block">
                        <div class="img-container">
                            <?php if(!empty($row['discount_price']) && $row['discount_price'] > $row['price']): 
                                $savings = round((($row['discount_price'] - $row['price']) / $row['discount_price']) * 100); ?>
                                <div class="discount-badge" style="background:#e11d48; color:white; font-size:10px; font-weight:bold; padding:2px 6px; border-radius:4px; position:absolute; top:10px; left:10px; z-index:10;">-<?php echo $savings; ?>%</div>
                            <?php endif; ?>

                            <img src="https://primeproductsbd.gamer.gd/public/uploads/<?php echo $row['image']; ?>" 
                                 class="w-full h-full object-contain transition-transform group-hover:scale-105" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>"
                                 <?php echo ($count <= 6) ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                                 onerror="this.src='https://placehold.co/200x200?text=No+Image'">
                        </div>
                    </a>

                    <div class="p-3 flex-grow">
                        <p class="text-[10px] text-orange-800 font-extrabold uppercase mb-1">Software</p>
                        <h3 class="text-xs product-name truncate mb-1">
                            <a href="product-details.php?id=<?php echo $row['id']; ?>" class="hover:text-rose-600">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        </h3>

                        <div class="flex items-center gap-1 mb-2">
                            <div class="flex text-orange-600 text-[9px]">
                                <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i>
                            </div>
                            <span class="text-[10px] text-muted-custom font-bold">(<?php echo rand(200, 400); ?>)</span>
                        </div>

                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-sm price-main">$<?php echo number_format($row['price'], 2); ?></span>
                            <?php if(!empty($row['discount_price'])): ?>
                                <span class="text-[10px] price-old">$<?php echo number_format($row['discount_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-1 text-[9px] delivery-status mb-3">
                            <i class="fas fa-bolt animate-pulse" aria-hidden="true"></i> Instant Delivery
                        </div>

                        <button onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', <?php echo $row['price']; ?>, '<?php echo $row['image']; ?>')" 
                                class="btn-add-cart">
                            Add to Cart
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20">
                <p class="text-gray-900 text-sm font-bold uppercase tracking-widest">No products found</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

<script>
function addToCart(id, name, price, image) {
    let formData = new URLSearchParams();
    formData.append('action', 'add'); 
    formData.append('id', id);
    formData.append('name', name); 
    formData.append('price', price); 
    formData.append('image', image);

    fetch('core/cart.php', { 
        method: 'POST', 
        body: formData.toString(), 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' } 
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const cartCount = document.getElementById('cart-count');
            if(cartCount) cartCount.innerText = data.total_items;

            const cartTotal = document.getElementById('cart-total');
            if(cartTotal) cartTotal.innerText = '$' + data.total_price;

            const cartItemsContainer = document.getElementById('cart-items');
            if(cartItemsContainer && data.cart_html) {
                cartItemsContainer.innerHTML = data.cart_html;
            }

            Swal.fire({ 
                title: 'Success!', 
                text: name + ' added to cart', 
                icon: 'success', 
                toast: true, 
                position: 'top-end', 
                showConfirmButton: false, 
                timer: 2000 
            });
        }
    })
    .catch(err => console.error('Error adding to cart:', err));
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>