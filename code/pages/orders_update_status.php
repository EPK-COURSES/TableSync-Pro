<?php
require __DIR__ . '/../includes/auth.php';
if(($_SESSION['role'] ?? '')!=='waiter' && ($_SESSION['role'] ?? '')!=='manager') die('Access denied.');
require __DIR__ . '/../config/db.php';
$active='orders';

$orderId=(int)($_GET['order_id'] ?? 0);
$error=''; $success='';

$stmt=$pdo->prepare("SELECT * FROM Orders WHERE order_id=?");
$stmt->execute([$orderId]);
$order=$stmt->fetch();
if(!$order) die('Order not found.');

$next=[
  'Pending'=>['Preparing','Cancelled'],
  'Preparing'=>['Served'],
  'Served'=>[],
  'Cancelled'=>[]
];

if($_SERVER['REQUEST_METHOD']==='POST'){
  $new=$_POST['status'] ?? '';
  $cur=$order['status'];
  if(!isset($next[$cur]) || !in_array($new,$next[$cur],true)) $error='Invalid status change.';
  else {
    $pdo->prepare("UPDATE Orders SET status=? WHERE order_id=?")->execute([$new,$orderId]);
    $success='Status updated.';
    $stmt->execute([$orderId]);
    $order=$stmt->fetch();
  }
}

function badge_class(string $s): string {
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
<title>Order Status</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="../includes/logout.php">Logout</a>
  </div>
</div>
<div class="container"><div class="card">
  <h1>Order #<?php echo $orderId; ?> Status</h1>
  <p class="small">Current: <span class="badge <?php echo badge_class($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></p>

  <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

  <form method="post" class="form-row">
    <div class="span-6">
      <label>New status</label>
      <select name="status" required>
        <option value="">-- choose --</option>
        <?php foreach(($next[$order['status']] ?? []) as $s): ?>
          <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn" type="submit" <?php echo empty($next[$order['status']])?'disabled':''; ?>>Update</button>
      <a class="btn secondary" href="orders_add_items.php?order_id=<?php echo $orderId; ?>">Back to Order</a>
    </div>
  </form>

  <div class="alert">
    <b>Quick guide:</b>
    Pending &rarr; Preparing (send to kitchen) &rarr; Served.
  </div>
</div></div>
<div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="../includes/invoice_generate.php">Invoice</a>
</div>
<script src="../assets/app.js"></script>
</body></html>
