<?php 

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../core/db.php';



// ১. ডাটাবেস সেটিংস ফেচ [cite: 2026-02-11]

$settings_query = $conn->query("SELECT * FROM settings WHERE id = 1");

$site_settings = ($settings_query) ? $settings_query->fetch_assoc() : null;



$site_name = $site_settings['site_name'] ?? 'Kena Kata';

$contact_phone = $site_settings['whatsapp_order_number'] ?? ($site_settings['phone'] ?? '8801847853867'); 



// ২. কার্ট ক্যালকুলেশন [cite: 2026-02-11]

$cart_count = 0; $cart_total = 0;

$db_cart_items = [];



if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id']; 

    $cart_res = $conn->query("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");

    if ($cart_res && $cart_res->num_rows > 0) {

        while ($row = $cart_res->fetch_assoc()) {

            $db_cart_items[] = $row;

            $cart_count += $row['qty'];

            $cart_total += ($row['price'] * $row['qty']);

        }

    }

}



$base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/";

$seo_desc = "Kena Kata - বাংলাদেশের সেরা অনলাইন শপ। প্রিমিয়াম ডিজিটাল সাবস্ক্রিপশন, ফ্যাশন এবং ইলেকট্রনিক্স কিনুন সাশ্রয়ী মূল্যে এবং দ্রুত ডেলিভারিতে।";

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    

    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <link rel="preconnect" href="https://cdn.tailwindcss.com">

    <link rel="preconnect" href="https://fonts.googleapis.com">



    <title><?php echo htmlspecialchars($site_name); ?> - Best Online Shopping in Bangladesh</title>

    <meta name="description" content="<?php echo $seo_desc; ?>">

    <meta name="keywords" content="Kena Kata, Online Shopping BD, Premium Digital Accounts, Men Fashion, Turjo Site">

    <meta name="author" content="Turjo Site">

    <link rel="canonical" href="<?php echo $base_url . basename($_SERVER['PHP_SELF']); ?>">

    

    <meta property="og:title" content="<?php echo htmlspecialchars($site_name); ?>">

    <meta property="og:description" content="<?php echo $seo_desc; ?>">

    <meta property="og:image" content="<?php echo $base_url; ?>public/uploads/logo.png">

    <meta property="og:url" content="<?php echo $base_url; ?>">

    <meta name="twitter:card" content="summary_large_image">



    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" media="print" onload="this.media='all'">

    

    <script src="<?php echo $base_url; ?>public/assets/js/main.js?v=<?php echo time(); ?>" defer></script>

    

    <style>

        :root { --sky-blue: #083b66; }

        body { background-color: #f2f4f8; color: #1a202c; margin: 0; padding: 0; font-family: sans-serif; overflow-x: hidden; }

        .bg-sky { background-color: var(--sky-blue); }

        .text-sky { color: var(--sky-blue); }

        .border-sky { border-color: var(--sky-blue); }

        .search-box:focus-within { border-color: #ef4444; }

        #cart-sidebar { transition: transform 0.3s ease-in-out; z-index: 1050; }

        .text-dark-gray { color: #4b5563 !important; }

        @keyframes shake {

            0% { transform: rotate(0deg); }

            25% { transform: rotate(10deg); }

            50% { transform: rotate(0deg); }

            75% { transform: rotate(-10deg); }

            100% { transform: rotate(0deg); }

        }

        .group:hover .group-hover\:shake { animation: shake 0.5s ease-in-out infinite; }

    </style>

</head>

<body>



<div id="cart-overlay" onclick="toggleCart()" class="fixed inset-0 bg-black/50 hidden z-[1001]"></div>



<section id="cart-sidebar" class="fixed top-0 right-0 h-full w-[320px] bg-white translate-x-full shadow-2xl flex flex-col z-[1050]" aria-labelledby="cart-heading">

    <div class="p-4 border-b flex justify-between items-center bg-gray-50">

        <h2 id="cart-heading" class="font-bold text-gray-800 uppercase text-sm tracking-tighter">Shopping Cart</h2>

        <button onclick="toggleCart()" class="text-gray-700 hover:text-red-500 p-2" aria-label="Close Shopping Cart">

            <i class="fas fa-times" aria-hidden="true"></i>

        </button>

    </div>



    <div id="cart-items" class="flex-1 overflow-y-auto p-4 flex flex-col gap-3">

        <?php if ($cart_count > 0): ?>

            <?php foreach ($db_cart_items as $item): ?>

            <div class="flex gap-3 border-b pb-3 items-center" id="cart-row-<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price']; ?>" data-qty="<?php echo $item['qty']; ?>">

                <img src="<?php echo $base_url; ?>public/uploads/<?php echo $item['image']; ?>" class="w-12 h-12 object-cover rounded border" alt="<?php echo htmlspecialchars($item['name']); ?>" loading="lazy" width="48" height="48">

                <div class="flex-1">

                    <h4 class="text-[11px] font-bold text-gray-900 leading-tight uppercase"><?php echo htmlspecialchars($item['name']); ?></h4>

                    <p class="text-[10px] text-dark-gray mt-1 font-bold">QTY: <?php echo $item['qty']; ?></p>

                    <p class="text-xs font-bold text-red-700">৳<?php echo number_format($item['price'] * $item['qty']); ?></p>

                </div>

                <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="text-gray-500 hover:text-red-600 transition p-2" aria-label="Remove <?php echo htmlspecialchars($item['name']); ?> from cart">

                    <i class="fas fa-trash-alt text-xs" aria-hidden="true"></i>

                </button>

            </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="flex flex-col items-center justify-center h-full opacity-40">

                <i class="fas fa-shopping-basket text-5xl mb-2" aria-hidden="true"></i>

                <p class="text-xs font-bold uppercase text-dark-gray"><?php echo isset($_SESSION['user_id']) ? 'Cart is empty' : 'Login to see cart'; ?></p>

            </div>

        <?php endif; ?>

    </div>



    <div class="p-4 border-t bg-gray-50 shadow-inner">

        <div class="flex justify-between items-center mb-4">

            <span class="text-xs font-bold text-dark-gray uppercase">Total Amount:</span>

            <span id="cart-total" class="font-black text-xl text-red-700">৳<?php echo number_format($cart_total); ?></span>

        </div>

        <button onclick="checkoutViaWhatsApp()" aria-label="Complete order via WhatsApp" class="w-full bg-green-600 text-white py-3 rounded font-bold uppercase text-xs hover:bg-green-700 transition flex items-center justify-center gap-2 mb-3">

            <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i> Buy via WhatsApp

        </button>

        <a href="<?php echo $base_url; ?>public/order.php" aria-label="Proceed to checkout page" class="w-full bg-sky text-white py-3 rounded font-bold uppercase text-xs hover:bg-blue-900 transition flex items-center justify-center gap-2">

            <i class="fas fa-shopping-cart text-lg" aria-hidden="true"></i> Order Now

        </a>

    </div>

</section>



<header class="bg-white shadow-sm border-b sticky top-0 z-[1000]">

    <div class="container mx-auto px-4 py-4 flex items-center justify-between gap-4">

        <a href="<?php echo $base_url; ?>" class="shrink-0 group" aria-label="<?php echo $site_name; ?> Home">

            <span class="text-2xl font-black text-sky italic uppercase leading-none">KENA<span class="text-red-600 group-hover:text-sky transition-colors"> KATA</span></span>

            <p class="text-[9px] uppercase tracking-tighter font-bold text-gray-700 mt-1">Your Trusted Online Shop</p>

        </a>



        <form action="<?php echo $base_url; ?>index.php" method="GET" class="hidden md:flex flex-1 max-w-2xl border-2 border-sky rounded overflow-hidden search-box transition-all">

            <input type="text" name="search" placeholder="Khoj: The Search" aria-label="Search for products" class="w-full px-4 py-2 outline-none text-sm font-medium">

            <button type="submit" aria-label="Submit Search" class="bg-sky text-white px-6 hover:bg-blue-900 transition-colors">

                <i class="fas fa-search" aria-hidden="true"></i>

            </button>

        </form>



        <nav class="flex items-center gap-5">

            <a href="https://wa.me/<?php echo $contact_phone; ?>" target="_blank" class="flex flex-col items-center text-gray-700 hover:text-green-600 transition-colors group" aria-label="Get WhatsApp Support">

                <i class="fas fa-headset text-xl group-hover:scale-110 transition-transform" aria-hidden="true"></i>

                <span class="text-[9px] font-bold mt-1 uppercase">Support</span>

            </a>



            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="<?php echo $base_url; ?>public/profile.php" class="flex flex-col items-center text-emerald-700 hover:text-sky transition-colors group" aria-label="View User Account">

                    <div class="relative">

                        <i class="fas fa-user-check text-xl group-hover:scale-110 transition-transform" aria-hidden="true"></i>

                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-600 border-2 border-white rounded-full animate-pulse"></span>

                    </div>

                    <span class="text-[9px] font-black mt-1 uppercase tracking-tighter">Account</span>

                </a>

            <?php else: ?>

                <a href="<?php echo $base_url; ?>public/login.php" class="flex flex-col items-center text-gray-700 hover:text-sky transition-colors group" aria-label="Login to your account">

                    <i class="fas fa-user text-xl group-hover:scale-110 transition-transform" aria-hidden="true"></i>

                    <span class="text-[9px] font-bold mt-1 uppercase tracking-tighter">Login</span>

                </a>

            <?php endif; ?>



            <button onclick="toggleCart()" aria-label="Open Shopping Cart, current items: <?php echo $cart_count; ?>" class="bg-sky text-white flex items-center gap-3 px-4 py-2 rounded cursor-pointer hover:bg-blue-900 transition-all shadow-md group">

                <div class="relative">

                    <i class="fas fa-shopping-bag text-xl group-hover:shake" aria-hidden="true"></i>

                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-600 text-white text-[9px] px-1.5 py-0.5 rounded-full border border-sky font-bold">

                        <?php echo $cart_count; ?>

                    </span>

                </div>

                <div class="hidden sm:block leading-none">

                    <p id="header-cart-count-text" class="text-[9px] font-bold text-blue-100 uppercase mb-0.5"><?php echo $cart_count; ?> Item(s)</p>

                    <p class="text-xs font-black" id="cart-total-header">৳<?php echo number_format($cart_total); ?></p>

                </div>

            </button>

        </nav>

    </div>

    <?php include __DIR__ . '/category.php'; ?>

</header>



<script>

    const waContactNumber = "<?php echo $contact_phone; ?>";

    const siteBrandName = "<?php echo $site_name; ?>";

    const userName = "<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>";

</script>

</body>

</html>