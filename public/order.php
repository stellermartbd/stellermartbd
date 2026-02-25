<?php 
/**
 * Project: Turjo Site - Order Page (Stabilized Bridge Edition)
 * Fix: Sync Database Cart to Session for Checkout [cite: 2026-02-11]
 * Feature: Supports both Cart Orders and Direct Buy Now Orders [cite: 2026-02-11]
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../core/db.php'; 

// ১. সিকিউরিটি চেক [cite: 2026-02-11]
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "অর্ডার করতে আগে আপনার অ্যাকাউন্টে লগইন করুন।";
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

/**
 * ২. ব্রীজ লজিক: কার্ট সেকশন থেকে আসলে ডেটাবেস কার্টকে সেশনে সিঙ্ক করা [cite: 2026-02-11]
 * যদি ইউআরএল এ প্রোডাক্ট আইডি না থাকে, তবেই এটি করা হবে।
 */
if (!isset($_GET['id'])) {
    $cart_res = $conn->query("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
    
    $_SESSION['cart'] = []; // সেশন রিসেট করে ফ্রেশ ডেটা নেওয়া হচ্ছে [cite: 2026-02-11]
    if ($cart_res && $cart_res->num_rows > 0) {
        while ($row = $cart_res->fetch_assoc()) {
            $_SESSION['cart'][] = [
                'id'    => $row['product_id'],
                'name'  => $row['name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'qty'   => $row['qty']
            ];
        }
    }
}

// ৩. ডাইনামিক ডাটা সোর্স লজিক (Cart + Direct Buy) [cite: 2026-02-11]
$checkout_items = [];
$base_total = 0;

// লজিক এ: যদি সরাসরি Buy Now থেকে আইডি আসে [cite: 2026-02-11]
if (isset($_GET['id'])) {
    $p_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $p_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {
        $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
        $checkout_items[] = [
            'id'    => $product['id'],
            'name'  => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'qty'   => $qty
        ];
        $base_total = $product['price'] * $qty;
    } else {
        echo "<script>alert('প্রোডাক্ট পাওয়া যায়নি!'); window.location.href='../index.php';</script>";
        exit;
    }
} 
// লজিক বি: সিঙ্ক করা সেশন কার্ট থেকে ডাটা নেওয়া হবে [cite: 2026-02-11]
elseif (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $checkout_items = $_SESSION['cart'];
    foreach ($checkout_items as $item) {
        $base_total += ($item['price'] * $item['qty']);
    }
} 
// যদি দুইটার একটাও না থাকে তবেই কেবল "কার্ট খালি" দেখাবে [cite: 2026-02-11]
else {
    echo "<script>alert('আপনার কার্ট খালি!'); window.location.href='../index.php';</script>";
    exit;
}

include __DIR__ . '/../templates/header.php'; 
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* সব ধরনের ইতালিক স্টাইল বাদ [cite: 2026-02-21] */
    * { font-style: normal !important; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    .order-card { background: #ffffff; border-radius: 24px; border: 1px solid #f1f5f9; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05); }
    .input-field { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; transition: 0.3s; }
    .input-field:focus { border-color: #0f172a; outline: none; background: #ffffff; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<div class="container mx-auto px-4 py-12 max-w-6xl">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        
        <div class="lg:col-span-7">
            <div class="order-card p-8 md:p-10">
                <h2 class="text-2xl font-black text-slate-900 mb-8 uppercase tracking-tight">Shipping Details</h2>

                <form action="handlers/order-handler.php" method="POST" id="orderForm" class="space-y-6">
                    <input type="hidden" name="base_price" value="<?php echo $base_total; ?>">
                    <input type="hidden" id="shippingCostInput" name="shipping_cost" value="0">
                    <input type="hidden" id="discountAmountInput" name="discount_amount" value="0">
                    <input type="hidden" id="totalPriceInput" name="total_price" value="<?php echo $base_total; ?>">
                    
                    <?php if(isset($_GET['id'])): ?>
                        <input type="hidden" name="direct_product_id" value="<?php echo (int)$_GET['id']; ?>">
                        <input type="hidden" name="direct_qty" value="<?php echo (int)$_GET['qty']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Full Name</label>
                            <input type="text" name="customer_name" required class="w-full input-field px-5 py-4 font-semibold text-sm" placeholder="Ex: Siam Ahmed" value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Contact Number</label>
                            <input type="text" name="customer_phone" required class="w-full input-field px-5 py-4 font-semibold text-sm" placeholder="017XXXXXXXX">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Division</label>
                            <select name="division" id="divisionSelect" required onchange="calculateShipping()" class="w-full input-field px-5 py-4 font-semibold text-sm appearance-none cursor-pointer">
                                <option value="">Select Division</option>
                                <option value="Dhaka">Dhaka Division</option>
                                <option value="Chattogram">Chattogram</option>
                                <option value="Rajshahi">Rajshahi</option>
                                <option value="Khulna">Khulna</option>
                                <option value="Barishal">Barishal</option>
                                <option value="Sylhet">Sylhet</option>
                                <option value="Rangpur">Rangpur</option>
                                <option value="Mymensingh">Mymensingh</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase ml-1">District</label>
                            <input type="text" name="district" required placeholder="Type your district" class="w-full input-field px-5 py-4 font-semibold text-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Full Address</label>
                        <textarea name="delivery_address" required rows="2" class="w-full input-field px-5 py-4 font-semibold text-sm" placeholder="House# 12, Road# 05..."></textarea>
                    </div>

                    <div class="space-y-2 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-300">
                        <label class="text-[11px] font-black text-slate-500 uppercase ml-1">Apply Coupon</label>
                        <div class="flex gap-2">
                            <input type="text" id="couponCode" placeholder="Enter code" class="flex-1 input-field px-4 py-3 font-semibold text-sm border-white shadow-sm">
                            <button type="button" onclick="applyCoupon()" class="px-6 bg-slate-800 text-white rounded-xl font-bold text-xs uppercase hover:bg-black transition-colors">Apply</button>
                        </div>
                        <p id="couponMsg" class="text-[10px] font-bold mt-2 hidden"></p>
                    </div>

                    <button type="submit" name="place_order" class="w-full py-5 bg-sky-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl transition-all hover:bg-blue-900 active:scale-[0.98] flex items-center justify-center gap-2">
                        <span>Confirm Order</span> <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="order-card p-8 sticky top-24">
                <h3 class="text-lg font-black text-slate-900 mb-6 border-b pb-4">Order Summary</h3>
                <div class="space-y-4 mb-8 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php foreach ($checkout_items as $item): ?>
                    <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-16 h-16 bg-white rounded-xl overflow-hidden border border-slate-200 flex-shrink-0 p-1">
                            <img src="../public/uploads/<?php echo $item['image']; ?>" alt="Product" class="w-full h-full object-contain">
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800 line-clamp-1 uppercase"><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="text-[11px] text-slate-500 font-bold uppercase">Qty: <?php echo $item['qty']; ?></p>
                            <p class="text-xs font-black text-slate-900 mt-1">৳<?php echo number_format($item['price'] * $item['qty']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pt-4 border-t border-dashed">
                    <div class="flex justify-between text-xl font-black text-slate-900">
                        <span>Total Payable</span>
                        <span>৳<?php echo number_format($base_total); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let baseTotal = <?php echo $base_total; ?>;
    let shippingCost = 0;
    let discount = 0;

    function calculateShipping() {
        const division = document.getElementById('divisionSelect').value;
        shippingCost = (division === 'Dhaka') ? 60 : (division === '') ? 0 : 120;
        document.getElementById('shippingDisplay').innerText = '৳' + shippingCost;
        document.getElementById('shippingCostInput').value = shippingCost;
        updateGrandTotal();
    }

    function applyCoupon() {
        const code = document.getElementById('couponCode').value.trim().toUpperCase();
        const msg = document.getElementById('couponMsg');
        if(code === 'TURJO10') {
            discount = Math.round(baseTotal * 0.10); 
            msg.innerText = "Coupon Applied!";
            msg.className = "text-[10px] font-bold mt-1 text-emerald-600 block";
            document.getElementById('discountRow').classList.remove('hidden');
        } else {
            discount = 0;
            msg.innerText = "Invalid Code!";
            msg.className = "text-[10px] font-bold mt-1 text-rose-500 block";
            document.getElementById('discountRow').classList.add('hidden');
        }
        updateGrandTotal();
    }

    function updateGrandTotal() {
        const grandTotal = (baseTotal + shippingCost) - discount;
        document.getElementById('grandTotalDisplay').innerText = '৳' + grandTotal.toLocaleString();
        document.getElementById('discountAmountInput').value = discount;
        document.getElementById('totalPriceInput').value = grandTotal;
    }
</script>

<?php include '../templates/footer.php'; ?>