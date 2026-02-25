<?php 
/**
 * Project: Turjo Site - Multi-Product Invoice System
 * Version: 4.1 (Tactical Design & Stable QR)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../core/db.php'; 
require_once '../core/functions.php'; 

if (!hasPermission($conn, 'order_manage.view')) {
    header("Location: dashboard.php?error=Access+Denied");
    exit();
}

$order_id = $_GET['id'] ?? null;
if (!$order_id) { header('Location: orders.php'); exit; }

$query = "SELECT o.*, u.username as acc_name, u.email as acc_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE o.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) { header('Location: orders.php'); exit; }

$items_query = "SELECT oi.*, p.image as product_image 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_name = p.name 
                WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($items_query);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    @media print {
        body * { visibility: hidden; }
        #printable-invoice, #printable-invoice * { visibility: visible; }
        #printable-invoice {
            position: absolute; left: 0; top: 0; width: 100%;
            margin: 0; padding: 0 !important; border: none !important;
            box-shadow: none !important; background: white !important;
        }
        .print-hidden { display: none !important; }
        #qrcode img { display: inline-block !important; margin: 0 auto; }
    }
    
    .invoice-table th { background: #f8fafc; font-size: 10px; color: #64748b; padding: 12px 8px; border-bottom: 2px solid #e2e8f0; }
    .invoice-table td { padding: 16px 8px; border-bottom: 1px solid #f1f5f9; }
    #qrcode canvas { display: none; } 
    #qrcode { width: 90px; height: 90px; background: #fff; border: 1px solid #f1f5f9; padding: 4px; border-radius: 8px; }
    .premium-accent { border-left: 4px solid #2563eb; }
</style>

<main class="flex-1 h-screen overflow-hidden bg-gray-50 dark:bg-theme-dark flex flex-col min-w-0 transition-all duration-300">
    
    <header class="h-20 flex items-center justify-between px-8 bg-white/80 dark:bg-theme-dark/80 backdrop-blur-md border-b border-gray-200 dark:border-[#251d33] sticky top-0 z-20 shrink-0 print-hidden">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight">Order Management</h2>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 italic">Tactical Intelligence Hub</p>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="safePrint()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold uppercase transition flex items-center gap-2 shadow-lg">
                <i class="fas fa-print"></i> Print Invoice
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto custom-scrollbar">
        <div class="p-8 w-full max-w-[950px] mx-auto space-y-6 pb-24">
            
            <div class="flex items-center justify-between print-hidden mb-2">
                <a href="orders.php" class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-blue-600 transition group">
                    <div class="h-9 w-9 flex items-center justify-center rounded-xl bg-white dark:bg-theme-card border border-gray-200 dark:border-theme-border group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 group-hover:border-blue-200 shadow-sm">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </div>
                    <span class="uppercase tracking-wider text-[11px]">Back to Orders</span>
                </a>
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Order Reference</p>
                    <p class="text-xs font-bold text-blue-600">#ORD-<?php echo $order['id']; ?></p>
                </div>
            </div>
            
            <div id="printable-invoice" class="bg-white dark:bg-theme-card p-12 rounded-3xl border border-gray-100 dark:border-theme-border shadow-2xl font-sans relative overflow-hidden">
                
                <div class="flex justify-between items-start border-b border-gray-100 pb-10 mb-10">
                    <div>
                        <h1 class="text-3xl font-black text-blue-600 tracking-tighter uppercase leading-none italic mb-2">TURJO SITE.</h1>
                        <p class="text-[10px] text-gray-400 font-bold tracking-[0.3em] uppercase">Tactical Intelligence & Tech Hub</p>
                        <div class="mt-5 text-xs text-gray-500 font-medium space-y-1">
                            <p>Dhanmondi, Dhaka, Bangladesh</p>
                            <p>Support: +880 1XXX-XXXXXX</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div id="qrcode" class="ml-auto mb-4"></div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter italic">INVOICE</h2>
                        <p class="text-xs text-gray-400 font-mono font-bold uppercase mt-1">#ORD-<?php echo $order['id']; ?></p>
                        <p class="text-[11px] text-gray-500 font-bold mt-1 uppercase italic"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-12 mb-12">
                    <div class="premium-accent pl-6 py-2">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mb-4">Customer Details</h4>
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase"><?php echo htmlspecialchars($order['customer_name']); ?></h3>
                            <p class="text-sm text-gray-500 font-medium"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                            <p class="text-sm font-black text-slate-900 dark:text-gray-300 mt-4 italic"><?php echo $order['customer_phone']; ?></p>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/40 p-8 rounded-2xl border border-dashed border-slate-200 dark:border-theme-border">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Payment Info</h4>
                        <div class="space-y-4 text-xs font-bold">
                            <div class="flex justify-between">
                                <span class="text-gray-500 uppercase">Method</span>
                                <span class="text-slate-900 dark:text-white uppercase"><?php echo $order['payment_method']; ?></span>
                            </div>
                            <?php if(!empty($order['transaction_id'])): ?>
                            <div class="flex justify-between border-t border-slate-200 dark:border-theme-border pt-3 border-dashed">
                                <span class="text-gray-500 uppercase">TrxID</span>
                                <span class="text-blue-600 font-mono"><?php echo $order['transaction_id']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-12">
                    <table class="w-full text-left invoice-table">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">#</th>
                                <th>Product Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Price</th>
                                <th class="text-right pr-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            $calculated_subtotal = 0;
                            while($item = $items_result->fetch_assoc()): 
                                $item_subtotal = $item['price'] * $item['quantity'];
                                $calculated_subtotal += $item_subtotal;
                            ?>
                            <tr>
                                <td class="text-center font-mono text-gray-400 text-xs"><?php echo sprintf("%02d", $count++); ?></td>
                                <td class="font-black text-gray-800 dark:text-gray-200 text-sm uppercase"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td class="text-center font-black text-gray-600 dark:text-gray-400 italic"><?php echo $item['quantity']; ?></td>
                                <td class="text-right font-bold text-gray-500">৳<?php echo number_format($item['price']); ?></td>
                                <td class="text-right pr-4 font-black text-gray-900 dark:text-white">৳<?php echo number_format($item_subtotal); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-end">
                    <div class="max-w-[350px]">
                        <div class="p-6 bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl border border-blue-100 dark:border-blue-900/30">
                            <p class="text-[11px] text-gray-600 dark:text-gray-400 font-medium italic"><?php echo !empty($order['order_note']) ? htmlspecialchars($order['order_note']) : 'No tactical notes provided.'; ?></p>
                        </div>
                    </div>
                    <div class="w-72 space-y-3">
                        <div class="flex justify-between text-xs font-bold text-gray-500 uppercase px-2">
                            <span>Subtotal</span>
                            <span>৳<?php echo number_format($calculated_subtotal); ?></span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-gray-500 uppercase px-2">
                            <span>Shipping</span>
                            <span>৳<?php echo number_format($order['shipping_cost']); ?></span>
                        </div>
                        <div class="pt-6 border-t-2 border-slate-900 dark:border-white flex justify-between items-center px-2">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Total</span>
                            <span class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tighter italic leading-none">৳<?php echo number_format($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-24 flex justify-between items-end border-t border-dashed border-gray-200 dark:border-theme-border pt-10">
                    <div class="text-center">
                        <div class="w-40 border-b border-slate-300 dark:border-theme-border mb-2 mx-auto"></div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Customer</p>
                    </div>
                    <div class="text-center">
                        <div class="w-40 border-b-2 border-blue-600 mb-2 mx-auto"></div>
                        <p class="text-[10px] font-black uppercase text-blue-600 italic">Authorized Signature</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript">
    function makeQR() {
        var qrBox = document.getElementById("qrcode");
        if(qrBox) {
            qrBox.innerHTML = ""; 
            var qrText = "ORDER: #<?php echo $order['id']; ?> | TOTAL: ৳<?php echo number_format($order['total_amount']); ?>";
            new QRCode(qrBox, { text: qrText, width: 82, height: 82, colorDark : "#0f172a", correctLevel : QRCode.CorrectLevel.M });
        }
    }

    function safePrint() {
        makeQR();
        setTimeout(window.print, 500); 
    }

    window.onload = function() { makeQR(); setTimeout(makeQR, 2000); };
</script>

<?php include 'includes/footer.php'; ?>