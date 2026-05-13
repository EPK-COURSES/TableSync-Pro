<?php
require __DIR__ . '/auth.php';
if(($_SESSION['role'] ?? '')!=='waiter' && ($_SESSION['role'] ?? '')!=='manager') die('Access denied.');
require __DIR__ . '/../config/db.php';
$active='invoice';

$id=(int)($_GET['id'] ?? 0);
$error=''; $success='';

$stmt=$pdo->prepare("SELECT i.*, u.first_name, u.last_name
                     FROM Invoices i LEFT JOIN Users u ON u.user_id=i.generated_by
                     WHERE i.invoice_id=?");
$stmt->execute([$id]);
$inv=$stmt->fetch();
if(!$inv) die('Invoice not found.');

if(isset($_POST['mark_paid'])){
  $pdo->prepare("UPDATE Invoices SET payment_status='Paid' WHERE invoice_id=?")->execute([$id]);

  $hist=$pdo->prepare("SELECT history_id FROM Dining_History WHERE table_id=? AND check_out_time IS NULL ORDER BY check_in_time DESC LIMIT 1");
  $hist->execute([(int)$inv['table_id']]);
  $h=$hist->fetch();
  if($h){
    $pdo->prepare("UPDATE Dining_History SET check_out_time=NOW(), duration_minutes=TIMESTAMPDIFF(MINUTE, check_in_time, NOW()) WHERE history_id=?")
        ->execute([(int)$h['history_id']]);
  }

  $pdo->prepare("UPDATE Restaurant_Tables SET status='Available' WHERE table_id=?")->execute([(int)$inv['table_id']]);

  $success='Marked Paid. Table is now Available.';
  $stmt->execute([$id]);
  $inv=$stmt->fetch();
}

if(isset($_POST['send_email'])){
  $to=trim($_POST['email'] ?? '');
  if(!filter_var($to, FILTER_VALIDATE_EMAIL)) $error='Please enter a valid email.';
  else {
    $subject='TableSync Pro - Invoice #'.$id;
    $body="Invoice #$id
Total: " . number_format((float)$inv['total_amount'],2) . "
Thank you!";
    $ok=@mail($to,$subject,$body);
    $success=$ok ? 'Email sent.' : 'Email attempted. Configure mail() on your server.';
  }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice #<?php echo $id; ?></title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="../pages/dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="logout.php">Logout</a>
  </div>
</div>
<div class="container"><div class="card">
  <h1>Invoice #<?php echo $id; ?></h1>
  <p class="small">Generated: <?php echo htmlspecialchars($inv['generation_date']); ?> &bull; Table: #<?php echo (int)$inv['table_id']; ?> &bull; By: <?php echo htmlspecialchars(trim(($inv['first_name']??'').' '.($inv['last_name']??''))); ?></p>

  <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

  <table class="table">
    <tbody>
      <tr><th>Subtotal</th><td><?php echo number_format((float)$inv['subtotal'],2); ?></td></tr>
      <tr><th>Discount</th><td><?php echo number_format((float)$inv['discount'],2); ?></td></tr>
      <tr><th>Total</th><td><b><?php echo number_format((float)$inv['total_amount'],2); ?></b></td></tr>
      <tr><th>Payment status</th><td><span class="badge <?php echo ($inv['payment_status']==='Paid')?'green':'orange'; ?>"><?php echo htmlspecialchars($inv['payment_status']); ?></span></td></tr>
    </tbody>
  </table>

  <div class="no-print" style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
    <button class="btn" type="button" onclick="window.print()">Print</button>

    <form method="post" style="display:inline">
      <button class="btn orange" type="submit" name="mark_paid" value="1" <?php echo ($inv['payment_status']==='Paid')?'disabled':''; ?>>Mark Paid</button>
    </form>

    <?php if(($_SESSION['role'] ?? '')==='manager'): ?>
      <a class="btn secondary" href="invoice_discount.php?id=<?php echo $id; ?>">Discount</a>
    <?php endif; ?>

    <form method="post" class="form-row" style="margin:0;">
      <input type="email" name="email" placeholder="customer@email.com" style="max-width:260px;">
      <button class="btn secondary" type="submit" name="send_email" value="1">Send Email</button>
    </form>
  </div>

  <p class="small">Email uses PHP <code>mail()</code> (configure server for localhost).</p>
</div></div>
<?php if(($_SESSION['role'] ?? '')==='waiter'): ?><div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="../pages/tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="../pages/tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="../pages/orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="invoice_generate.php">Invoice</a>
</div><?php endif; ?>
<script src="../assets/app.js"></script>
</body></html>
