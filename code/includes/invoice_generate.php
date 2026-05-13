<?php
require __DIR__ . '/auth.php';
if(($_SESSION['role'] ?? '')!=='waiter' && ($_SESSION['role'] ?? '')!=='manager') die('Access denied.');
require __DIR__ . '/../config/db.php';
$active='invoice';

$error='';
$tableId = (int)($_GET['table_id'] ?? 0);

if ($tableId > 0) {
  $_POST['table_id'] = $tableId;
  $_SERVER['REQUEST_METHOD'] = 'POST';
}

$tables=$pdo->query("SELECT table_id FROM Restaurant_Tables WHERE status='Occupied' ORDER BY table_id")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $tableId=(int)($_POST['table_id'] ?? 0);
  if($tableId<=0) $error='Please select a table.';
  else {
    $sql="SELECT SUM(od.quantity*od.price_charged) subtotal
          FROM Orders o JOIN Order_Details od ON od.order_id=o.order_id
          WHERE o.table_id=? AND o.status='Served'";
    $stmt=$pdo->prepare($sql);
    $stmt->execute([$tableId]);
    $subtotal=(float)($stmt->fetch()['subtotal'] ?? 0);

    if($subtotal<=0) $error='No Served orders found for that table. Set orders to Served first.';
    else {
      $discount=0.00; $total=$subtotal-$discount;
      $pdo->prepare("INSERT INTO Invoices (subtotal,discount,total_amount,payment_status,table_id,generated_by)
                     VALUES (?,?,?,'Pending',?,?)")
          ->execute([$subtotal,$discount,$total,$tableId,(int)$_SESSION['user_id']]);
      $invoiceId=(int)$pdo->lastInsertId();
      header('Location: invoice_print.php?id='.$invoiceId);
      exit;
    }
  }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Generate Invoice</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="../pages/dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="logout.php">Logout</a>
  </div>
</div>
<div class="container">
  <h1>Generate Invoice</h1>
  <p class="small">Tap a table to generate invoice from <b>Served</b> orders.</p>

  <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="grid" style="margin-top:10px;">
    <?php foreach($tables as $t): ?>
      <div class="col-3">
        <form method="post" class="tile">
          <div class="title">Table #<?php echo (int)$t['table_id']; ?></div>
          <input type="hidden" name="table_id" value="<?php echo (int)$t['table_id']; ?>">
          <button class="btn" type="submit">Generate</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if(!$tables): ?><div class="alert">No occupied tables.</div><?php endif; ?>
</div>
<?php if(($_SESSION['role'] ?? '')==='waiter'): ?><div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="../pages/tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="../pages/tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="../pages/orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="invoice_generate.php">Invoice</a>
</div><?php endif; ?>
<script src="../assets/app.js"></script>
</body></html>
