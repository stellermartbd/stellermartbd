<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<?php
if(!isset($_GET['id'])) { echo "Order not found!"; exit; }
$order_id = $_GET['id'];

// অর্ডারের বিস্তারিত আনা
$order_query = $conn->query("SELECT o.*, c.name, c.email, c.phone, c.address FROM orders o 
                             LEFT JOIN customers c ON o.customer_id = c.id 
                             WHERE o.id = $order_id");
$order = $order_query->fetch_assoc();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <a href="orders.php" class="text-sm font-bold text-gray-400 hover:text-rose-500 transition"><i class="fas fa-arrow-left mr-2"></i> Back to Orders</a>
        <h1 class="text-2xl font-black dark:text-white mt-2">Order #<?php echo $order['order_number']; ?></h1>
    </div>
    <form action="handlers/order-handler.php" method="POST" class="flex gap-2">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <select name="new_status" class="bg-white dark:bg-theme-card border dark:border-theme-border rounded-xl px-4 py-2 text-xs font-bold focus:outline-none">
            <option value="Pending" <?php if($order['order_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
            <option value="Processing" <?php if($order['order_status'] == 'Processing') echo 'selected'; ?>>Processing</option>
            <option value="Shipped" <?php if($order['order_status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
            <option value="Delivered" <?php if($order['order_status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
            <option value="Cancelled" <?php if($order['order_status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
        <button type="submit" name="update_status" class="bg-rose-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-rose-700 transition">Update</button>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="glass-panel p-6 space-y-4">
        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest border-b dark:border-theme-border pb-2">Customer Info</h3>
        <div class="text-sm font-bold">
            <p class="text-gray-400 mb-1 font-medium">Name</p>
            <p><?php echo $order['name']; ?></p>
        </div>
        <div class="text-sm font-bold">
            <p class="text-gray-400 mb-1 font-medium">Contact</p>
            <p><?php echo $order['email']; ?></p>
            <p><?php echo $order['phone']; ?></p>
        </div>
        <div class="text-sm font-bold">
            <p class="text-gray-400 mb-1 font-medium">Shipping Address</p>
            <p class="font-medium text-gray-500"><?php echo $order['address']; ?></p>
        </div>
    </div>

    <div class="lg:col-span-2 glass-panel p-6">
        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest border-b dark:border-theme-border pb-4 mb-4">Ordered Items</h3>
        <table class="w-full text-left">
            <thead class="text-[10px] text-gray-400 uppercase">
                <tr>
                    <th class="pb-3">Product Name</th>
                    <th class="pb-3">Price</th>
                    <th class="pb-3">Qty</th>
                    <th class="pb-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="text-sm font-bold">
                <?php
                $items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
                while($item = $items->fetch_assoc()):
                ?>
                <tr class="border-b dark:border-theme-border">
                    <td class="py-4"><?php echo $item['product_name']; ?></td>
                    <td class="py-4">৳<?php echo number_format($item['price']); ?></td>
                    <td class="py-4">x<?php echo $item['quantity']; ?></td>
                    <td class="py-4 text-right">৳<?php echo number_format($item['price'] * $item['quantity']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-6 flex justify-end">
            <div class="text-right">
                <p class="text-gray-400 text-xs font-bold uppercase">Grand Total</p>
                <h2 class="text-3xl font-black text-rose-600 mt-1">৳<?php echo number_format($order['total_amount']); ?></h2>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>