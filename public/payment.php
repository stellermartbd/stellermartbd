<?php 
/**
 * Project: Turjo Site - Secure Payment Interface (Updated)
 * Features: One-to-Many Order Support, Dynamic Total Calculation
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../core/db.php'; 
include '../templates/header.php'; 

// ১. অর্ডার আইডি ভ্যালিডেশন [cite: 2026-02-11]
if (!isset($_GET['order_id'])) {
    echo "<script>window.location.href = '../index.php';</script>";
    exit;
}

$order_id = (int)$_GET['order_id'];

// ২. মেইন অর্ডার ডিটেইলস আনা (orders টেবিল থেকে)
$order_query = "SELECT * FROM orders WHERE id = $order_id AND status = 'Pending'";
$order_res = $conn->query($order_query);

if ($order_res->num_rows == 0) {
    echo "<script>alert('Invalid Order!'); window.location.href = '../index.php';</script>";
    exit;
}

$order_data = $order_res->fetch_assoc();
$total_payable = $order_data['total_amount']; // ডেলিভারি চার্জ সহ টোটাল
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    .payment-card { background: #ffffff; border-radius: 30px; border: 1px solid #f1f5f9; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
    .method-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: 2px solid #f1f5f9; position: relative; }
    input[type="radio"]:checked + .method-card { border-color: #0f172a; background-color: #f8fafc; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<div class="container mx-auto px-4 py-16 max-w-5xl">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <div class="lg:col-span-7">
            <div class="payment-card p-8 md:p-10">
                <h2 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">Payment Strategy</h2>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-8">Order #<?php echo $order_id; ?> Transaction</p>

                <form action="handlers/payment-handler.php" method="POST" id="paymentForm" class="space-y-6">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

                    <label class="relative block">
                        <input type="radio" name="pay_method" value="COD" checked class="peer absolute opacity-0" onclick="updateUI('COD')">
                        <div class="method-card p-6 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-900 border border-slate-100">
                                    <i class="fas fa-truck text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Cash On Delivery</p>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Advance Shipping Cost Required</p>
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="relative block">
                        <input type="radio" name="pay_method" value="Online" class="peer absolute opacity-0" onclick="updateUI('Online')">
                        <div class="method-card p-6 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-rose-500 border border-slate-100">
                                    <i class="fas fa-mobile-alt text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-sm">Full Online Payment</p>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Secure bKash Transaction</p>
                                </div>
                            </div>
                        </div>
                    </label>

                    <div id="instruction_box">
                        <div id="cod_info" class="p-6 bg-slate-900 rounded-3xl text-white space-y-4 shadow-xl border-l-4 border-rose-500">
                            <p class="text-xs leading-relaxed opacity-90 tracking-tight">
                                অর্ডার কনফার্ম করতে ডেলিভারি চার্জ সেন্ড মানি করুন। [cite: 2026-02-11]
                            </p>
                            <p class="text-sm font-black tracking-widest text-rose-400 uppercase ">bKash (Personal): 01847853867</p>
                        </div>

                        <div id="online_info" class="hidden p-6 bg-rose-600 rounded-3xl text-white space-y-4 shadow-xl">
                            <p class="text-xs leading-relaxed font-bold tracking-tight text-white">
                                ফুল পেমেন্ট <span class="underline">৳<?php echo number_format($total_payable); ?></span> আমাদের বিকাশ নাম্বারে সেন্ড মানি করুন। [cite: 2026-02-11]
                            </p>
                            <p class="text-sm font-black tracking-widest text-white uppercase ">bKash (Personal): 01847853867</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest block mb-2 text-slate-500 ml-1">Transaction ID (TrxID) *</label>
                        <input type="text" name="trx_id" required placeholder="EX: 8N79AS8D" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-5 focus:outline-none focus:border-slate-900 font-mono uppercase text-sm shadow-sm">
                    </div>

                    <button type="submit" name="confirm_payment" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl hover:bg-black transition-all">
                        Confirm Payment
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="payment-card p-8 sticky top-10">
                <h3 class="text-lg font-black text-slate-900 mb-6 border-b pb-4 tracking-tight">Order Items</h3>
                
                <div class="space-y-4 mb-8 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php 
                    // ৩. ডাটাবেস (order_items টেবিল) থেকে প্রোডাক্ট ফেচ করা [cite: 2026-02-11]
                    $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
                    $items_res = $conn->query($items_query);
                    
                    if ($items_res && $items_res->num_rows > 0) {
                        while($item = $items_res->fetch_assoc()):
                    ?>
                    <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-12 h-12 bg-white rounded-xl border p-2 flex items-center justify-center text-gray-400">
                             <i class="fas fa-box-open"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-[11px] font-black text-slate-800 line-clamp-1 uppercase tracking-tight">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </h4>
                            <p class="text-[10px] text-gray-400 font-bold tracking-tight">
                                <?php echo $item['quantity']; ?> x ৳<?php echo number_format($item['price']); ?>
                            </p>
                        </div>
                        <div class="font-bold text-slate-900 text-xs">
                            ৳<?php echo number_format($item['price'] * $item['quantity']); ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    } else {
                        // যদি ডাটাবেসে আইটেম না থাকে তবে এটি দেখাবে
                        echo "<p class='text-xs text-red-500 font-bold'>No items found in database for this order.</p>";
                    }
                    ?>
                </div>

                <div class="space-y-4 pt-4 border-t border-dashed">
                    <div class="flex justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                        <span>Customer</span>
                        <span><?php echo htmlspecialchars($order_data['customer_name']); ?></span>
                    </div>
                    <div class="flex justify-between text-2xl font-black text-slate-900 pt-5 border-t">
                        <span class=" tracking-tighter uppercase">Total Payable</span>
                        <span class="text-rose-600 tracking-tight">৳<?php echo number_format($total_payable); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function updateUI(method) {
    const codInfo = document.getElementById('cod_info');
    const onlineInfo = document.getElementById('online_info');
    if(method === 'Online') {
        onlineInfo.classList.remove('hidden');
        codInfo.classList.add('hidden');
    } else {
        codInfo.classList.remove('hidden');
        onlineInfo.classList.add('hidden');
    }
}
</script>

<?php include '../templates/footer.php'; ?>