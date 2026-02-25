<?php 
// ১. পাথ এবং ডাটাবেস কানেকশন
require_once __DIR__ . '/core/db.php'; 

/**
 * SEO Optimization: Dynamic Meta Data [cite: 2026-02-11]
 */
$seo_title = "Kena Kata - Premium Digital Solutions & Fashion in BD";
$seo_description = "Kena Kata - বাংলাদেশের সেরা অনলাইন শপ। প্রিমিয়াম ডিজিটাল সাবস্ক্রিপশন, ফ্যাশন এবং ইলেকট্রনিক্স কিনুন সাশ্রয়ী মূল্যে এবং দ্রুত ডেলিভারিতে।";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_key = htmlspecialchars($_GET['search']);
    $seo_title = "Search results for '$search_key' - Kena Kata";
    $seo_description = "Best deals on $search_key at Kena Kata. Explore our wide range of digital accounts and fashion products.";
}

// templates ফোল্ডার থেকে হেডার ইনক্লুড করা
include __DIR__ . '/templates/header.php';
?>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $seo_title; ?></title>
    <meta name="description" content="<?php echo $seo_description; ?>">
    <meta name="keywords" content="Kena Kata, Online Shopping BD, Premium Digital Accounts, Men Fashion, Turjo Site">
    <link rel="canonical" href="<?php echo (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    
    <meta property="og:title" content="<?php echo $seo_title; ?>">
    <meta property="og:description" content="<?php echo $seo_description; ?>">
    <meta property="og:image" content="<?php echo $base_url; ?>public/uploads/logo.png">
    <meta property="og:url" content="<?php echo $base_url; ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; overflow-x: hidden; }
        :root { --sky-blue: #083b66; }

        .product-card {
            background: #fff;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border: 1px solid #cbd5e1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        @media (max-width: 640px) {
            .product-card { padding: 10px !important; }
            .btn-action { font-size: 11px !important; padding: 10px !important; }
        }

        .product-card:hover {
            box-shadow: 0 12px 24px -10px rgba(0,0,0,0.15);
            transform: translateY(-4px);
        }

        .discount-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #be123c;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 4px;
            z-index: 10;
        }

        .btn-action {
            font-size: 13px;
            font-weight: 800;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            transition: 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-cart-minimal { background-color: #f1f5f9; color: #1e293b; border: 1px solid #94a3b8; }
        .btn-cart-minimal:hover { background-color: #e2e8f0; }
        .btn-order-now { background-color: var(--sky-blue); color: white; margin-top: 6px; }
        
        .text-dark-gray { color: #1e293b !important; font-weight: 800 !important; }
        .text-price-old { color: #4b5563 !important; text-decoration-thickness: 2px; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0; }
    </style>
</head>

<section class="slider-container">
    <div class="container mx-auto px-2 md:px-8">
        <?php 
        if (file_exists(__DIR__ . '/templates/slider.php')) {
            include __DIR__ . '/templates/slider.php';
        } else {
            echo '<div class="py-10 md:py-20 px-4 text-white rounded-xl bg-gradient-to-r from-[#083b66] to-[#111827] text-center">
                    <h1 class="text-3xl md:text-5xl font-extrabold mb-2 tracking-tight uppercase">Explore <span class="text-red-500">Premium</span> Digital</h1>
                    <p class="text-blue-100 text-xs md:text-lg mb-6 opacity-90">Instant delivery & 24/7 Support.</p>
                    <a href="#shop-section" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-black rounded shadow-lg transition uppercase text-xs" aria-label="Start shopping and go to shop section">Start Shopping</a>
                  </div>';
        }
        ?>
    </div>
</section>

<?php 
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
        case 'a-z': $order_by = "name ASC"; break;
        case 'z-a': $order_by = "name DESC"; break;
        default: $order_by = "id DESC";
    }
}
$query = "SELECT * FROM products WHERE status = 'Live' $search_condition ORDER BY $order_by";
$result = $conn->query($query);
$total_products = ($result) ? $result->num_rows : 0; 
?>

<main id="shop-section" class="container mx-auto px-3 md:px-8 py-6">
    <div class="flex flex-col gap-4 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-th-large text-sky-900 text-lg md:text-xl" aria-hidden="true"></i>
                <h2 class="text-sm md:text-2xl font-black text-slate-900 uppercase">
                    Shop <span class="text-slate-700 font-bold text-xs ml-1">(<?php echo $total_products; ?>)</span>
                </h2>
            </div>

            <form method="GET" class="flex items-center">
                <?php if(isset($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                <?php endif; ?>
                <label for="product-sort" class="sr-only">Sort products</label>
                <select id="product-sort" name="sort" aria-label="Sort products by price or name" onchange="this.form.submit()" 
                    class="bg-white border-2 border-slate-500 text-xs md:text-sm px-3 py-2 rounded-lg outline-none focus:ring-2 focus:ring-sky-800 cursor-pointer shadow-sm font-bold text-slate-900">
                    <option value="featured">Sort By</option>
                    <option value="low-high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'low-high') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="high-low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'high-low') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="a-z" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'a-z') ? 'selected' : ''; ?>>Alphabetical: A to Z</option>
                    <option value="z-a" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'z-a') ? 'selected' : ''; ?>>Alphabetical: Z to A</option>
                </select>
            </form>
        </div>
    </div>

    <div id="product-container" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-6">
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <article class="product-card p-4 group">
                    <div class="img-wrapper cursor-pointer" onclick="window.location.href='product-details.php?id=<?php echo $row['id']; ?>'">
                        <?php if(!empty($row['discount_price']) && $row['discount_price'] > $row['price']): 
                             $percentage = round((($row['discount_price'] - $row['price']) / $row['discount_price']) * 100); ?>
                            <div class="discount-badge">-<?php echo $percentage; ?>%</div>
                        <?php endif; ?>
                        
                        <div class="aspect-square flex items-center justify-center p-2 bg-[#f8fafc]">
                            <img src="<?php echo $base_url; ?>public/uploads/<?php echo $row['image']; ?>" 
                                 class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 rounded-lg" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?> - premium shopping">
                        </div>
                    </div>

                    <div class="flex flex-col flex-grow">
                        <h3 class="text-[14px] md:text-[16px] font-black text-slate-900 line-clamp-2 mt-2 group-hover:text-sky-900 transition-colors uppercase leading-tight">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h3>
                        
                        <div class="flex items-center gap-1 mt-2">
                            <span role="img" aria-label="Rated 5 out of 5 stars" class="text-[10px] md:text-[12px] text-orange-700">
                                <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i>
                            </span>
                            <span class="text-[10px] md:text-[12px] text-slate-800 font-black">(<?php echo rand(50, 200); ?>)</span>
                        </div>

                        <div class="flex items-baseline gap-1 md:gap-2 mt-2 mb-4">
                            <span class="text-base md:text-[18px] font-black text-black">৳<?php echo number_format($row['price']); ?></span>
                            <?php if(!empty($row['discount_price'])): ?>
                                <span class="text-[11px] md:text-[13px] text-price-old line-through font-bold">৳<?php echo number_format($row['discount_price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto space-y-2">
                            <button onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')"
                                    aria-label="Add <?php echo htmlspecialchars($row['name']); ?> to shopping cart"
                                    class="btn-action btn-cart-minimal">
                                <i class="fas fa-shopping-basket mr-1" aria-hidden="true"></i> Add to Cart
                            </button>
                            
                            <button onclick="window.location.href='public/order.php?id=<?php echo $row['id']; ?>'"
                                    aria-label="Buy <?php echo htmlspecialchars($row['name']); ?> now"
                                    class="btn-action btn-order-now">
                                <i class="fas fa-bolt mr-1" aria-hidden="true"></i> Buy Now
                            </button>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full py-16 text-center">
                <i class="fas fa-search text-5xl text-slate-500 mb-4" aria-hidden="true"></i>
                <p class="text-slate-800 font-black uppercase text-xs">No products found!</p>
                <a href="index.php" class="text-sky-900 text-xs font-black underline mt-2 inline-block" aria-label="Clear search and show all products">Reset Search</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php include __DIR__ . '/templates/footer.php'; ?>