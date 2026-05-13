<?php
require __DIR__ . '/../includes/auth.php';
require_role('waiter');
require __DIR__ . '/../config/db.php';
$active='orders';

$error='';

$prefill = (int)($_GET['table_id'] ?? 0);
if ($prefill > 0) {
  $_POST['table_id'] = $prefill;
  $_SERVER['REQUEST_METHOD'] = 'POST';
}

$tables = $pdo->query("SELECT table_id, capacity, location FROM Restaurant_Tables WHERE status='Occupied' ORDER BY table_id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tableId = (int)($_POST['table_id'] ?? 0);
  if ($tableId <= 0) {
    $error = 'Please select a table.';
  } else {
    $pdo->prepare("INSERT INTO Orders (status,total_price,table_id,processed_by) VALUES ('Pending',0.00,?,?)")
        ->execute([$tableId,(int)$_SESSION['user_id']]);
    $orderId = (int)$pdo->lastInsertId();
    header('Location: orders_add_items.php?order_id=' . $orderId);
    exit;
  }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Order</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>New Order</h1>
  <p class="small">Tap an occupied table to start a new order.</p>

  <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="grid" style="margin-top:10px;">
    <?php foreach($tables as $t): ?>
      <div class="col-3">
        <form method="post" class="tile">
          <div class="title">Table #<?php echo (int)$t['table_id']; ?></div>
          <div class="small">Cap: <b><?php echo (int)$t['capacity']; ?></b> &bull; <?php echo htmlspecialchars($t['location'] ?? ''); ?></div>
          <input type="hidden" name="table_id" value="<?php echo (int)$t['table_id']; ?>">
          <button class="btn" type="submit">Start Order</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if(!$tables): ?>
    <div class="alert">No occupied tables. First <a href="tables_mark_occupied.php">check-in</a> a table.</div>
  <?php endif; ?>
</div>
<div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="../includes/invoice_generate.php">Invoice</a>
</div>
<script src="../assets/app.js"></script>
</body></html>
