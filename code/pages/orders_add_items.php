<?php
require __DIR__ . '/../includes/auth.php';
require_role('waiter');
require __DIR__ . '/../config/db.php';
$active='orders';

$orderId = (int)($_GET['order_id'] ?? 0);
$error=''; $success='';

$stmt=$pdo->prepare("SELECT * FROM Orders WHERE order_id=? LIMIT 1");
$stmt->execute([$orderId]);
$order=$stmt->fetch();
if(!$order) die('Order not found.');

if($_SERVER['REQUEST_METHOD']==='POST'){
  $menuId=(int)($_POST['menu_item_id'] ?? 0);
  $qty=(int)($_POST['quantity'] ?? 1);

  if($order['status']!=='Pending') $error='You can only add items while order is Pending.';
  elseif($menuId<=0) $error='Choose a menu item.';
  elseif($qty<=0) $error='Quantity must be at least 1.';
  else {
    $m=$pdo->prepare("SELECT price FROM Menu_Items WHERE menu_item_id=? AND is_available=1 LIMIT 1");
    $m->execute([$menuId]);
    $menu=$m->fetch();
    if(!$menu) $error='Menu item not available.';
    else {
      $price=(float)$menu['price'];
      $pdo->prepare("INSERT INTO Order_Details (quantity,price_charged,order_id,menu_item_id) VALUES (?,?,?,?)")
          ->execute([$qty,$price,$orderId,$menuId]);

      $sum=$pdo->prepare("SELECT SUM(quantity*price_charged) total FROM Order_Details WHERE order_id=?");
      $sum->execute([$orderId]);
      $total=(float)($sum->fetch()['total'] ?? 0);
      $pdo->prepare("UPDATE Orders SET total_price=? WHERE order_id=?")->execute([$total,$orderId]);

      $success='Added!';
      $stmt->execute([$orderId]);
      $order=$stmt->fetch();
    }
  }
}

$menuItems=$pdo->query("SELECT menu_item_id, name, description, category, price FROM Menu_Items WHERE is_available=1 ORDER BY category, name")->fetchAll();
$details=$pdo->prepare("SELECT od.*, mi.name FROM Order_Details od JOIN Menu_Items mi ON mi.menu_item_id=od.menu_item_id WHERE od.order_id=?");
$details->execute([$orderId]);
$rows=$details->fetchAll();

function obadge(string $s): string {
  if($s==='Pending') return 'orange';
  if($s==='Preparing') return 'orange';
  if($s==='Served') return 'green';
  if($s==='Cancelled') return 'red';
  return 'grey';
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order #<?php echo $orderId; ?></title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="../includes/logout.php">Logout</a>
  </div>
</div>
<div class="container">
  <div class="card" style="margin-bottom:14px;">
    <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center; justify-content:space-between;">
      <div>
        <h1 style="margin-bottom:6px;">Order #<?php echo $orderId; ?> &mdash; Table #<?php echo (int)$order['table_id']; ?></h1>
        <div class="small">Status: <span class="badge <?php echo obadge($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span> &bull; Total: <b><?php echo number_format((float)$order['total_price'],2); ?></b></div>
      </div>
      <div class="no-print" style="display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn secondary" href="orders_update_status.php?order_id=<?php echo $orderId; ?>">Status</a>
        <a class="btn" href="../includes/invoice_generate.php?table_id=<?php echo (int)$order['table_id']; ?>">Invoice</a>
      </div>
    </div>

    <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  </div>

  <div class="pos-split">
    <div class="card">
      <h2>Menu (tap to add)</h2>
      <div class="small">Quick add: choose quantity then tap <b>Add</b>. (Order must be Pending.)</div>

      <div class="menu-grid" style="margin-top:12px;">
        <?php foreach($menuItems as $m): ?>
          <div class="menu-card">
            <div class="name"><?php echo htmlspecialchars($m['name']); ?></div>
            <div class="desc"><?php echo htmlspecialchars($m['description'] ?? ''); ?></div>
            <div class="small"><b><?php echo htmlspecialchars($m['category'] ?? ''); ?></b></div>
            <div class="price"><?php echo number_format((float)$m['price'],2); ?></div>

            <form method="post" class="qty">
              <input type="hidden" name="menu_item_id" value="<?php echo (int)$m['menu_item_id']; ?>">
              <label class="small" style="margin:0;">Qty</label>
              <input type="number" name="quantity" min="1" value="1" <?php echo ($order['status']!=='Pending')?'disabled':''; ?> >
              <button class="btn" type="submit" <?php echo ($order['status']!=='Pending')?'disabled':''; ?>>Add</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if(!$menuItems): ?><div class="alert">No available menu items. Manager can add items.</div><?php endif; ?>
    </div>

    <div class="card">
      <h2>Current Items</h2>
      <table class="table">
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['name']); ?></td>
              <td><?php echo (int)$r['quantity']; ?></td>
              <td><?php echo number_format((float)$r['price_charged'],2); ?></td>
              <td><?php echo number_format((float)$r['quantity']*(float)$r['price_charged'],2); ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?><tr><td colspan="4" class="small">No items yet. Add from menu.</td></tr><?php endif; ?>
        </tbody>
      </table>

      <div class="no-print" style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn secondary" href="orders_active.php">Back to Orders</a>
        <?php if($order['status']==='Pending'): ?>
          <a class="btn orange" href="orders_update_status.php?order_id=<?php echo $orderId; ?>">Send to Kitchen</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="../includes/invoice_generate.php">Invoice</a>
</div>
<script src="../assets/app.js"></script>
</body></html>
