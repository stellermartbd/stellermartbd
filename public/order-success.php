<?php 
require_once '../core/db.php'; 
include '../templates/header.php'; 

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<script src="https://cdn.tailwindcss.com"></script>
<div class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-[40px] p-10 shadow-2xl text-center border border-slate-100">
        <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-clock text-3xl animate-pulse"></i>
        </div>
        
        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Order Received!</h2>
        <p class="text-gray-500 text-sm mt-2 font-medium">Your Order ID: <span class="text-slate-900 font-bold">#<?php echo $order_id; ?></span></p>
        
        <div class="mt-8 p-6 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 bg-amber-50 px-4 py-2 rounded-full">Status: Pending</span>
            <p class="text-xs text-slate-600 mt-4 leading-relaxed">
                Apnar order ti amader kache poucheche. Ekhon eti <span class="font-bold">Pending</span> obosthay ache. Amader team khub shighroi apnar sathe jogajog kore order ti confirm korbe.
            </p>
        </div>

        <div class="mt-8 space-y-3">
            <a href="profile.php" class="block w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg">View My Orders</a>
            <a href="/../index.php" class="block w-full py-4 border-2 border-slate-100 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all">Back to Home</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>