<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/cart.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/csrf.php';
session_start();

$items = cart_items();
if (empty($items)) {
    header('Location: /cart.php');
    exit;
}

// compute total
$total = 0.0;
$lineItems = [];
foreach ($items as $pid => $q) {
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($p) {
        $lineTotal = $p['price'] * $q;
        $total += $lineTotal;
        $lineItems[] = ['product_id'=>$p['id'],'price'=>$p['price'],'qty'=>$q];
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['_csrf'] ?? '')) $errors[] = 'Invalid CSRF token';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid input';
    if (empty($errors)) {
        // create order (simple: no payment, cash on delivery)
        $user = current_user();
        $userId = $user['id'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'pending', NOW())");
        $stmt->execute([$userId, $total]);
        $orderId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price, qty) VALUES (?, ?, ?, ?)");
        foreach ($lineItems as $li) {
            $stmt->execute([$orderId, $li['product_id'], $li['price'], $li['qty']]);
        }
        // clear cart
        cart_clear();
        header('Location: /order-success.php?id=' . $orderId);
        exit;
    }
}

$page_title = 'Checkout';
include __DIR__ . '/../templates/header.php';
?>
<h2>Checkout</h2>
<?php foreach ($errors as $e): ?><div style="color:red;"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

<form method="post">
  <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
  <div class="form-row">
    <label>Name: <input name="name" required></label>
  </div>
  <div class="form-row">
    <label>Email: <input name="email" type="email" required></label>
  </div>
  <div class="form-row">
    <div>Order Total: <strong>৳ <?=number_format($total,2)?></strong></div>
  </div>
  <button class="btn" type="submit">Place Order (Cash on delivery)</button>
</form>

<?php include __DIR__ . '/../templates/footer.php'; ?>