<?php
require __DIR__ . '/auth.php';
require_role('manager');
require __DIR__ . '/../config/db.php';

$id=(int)($_GET['id'] ?? 0);
$error=''; $success='';
$stmt=$pdo->prepare("SELECT * FROM Invoices WHERE invoice_id=?");
$stmt->execute([$id]);
$inv=$stmt->fetch();
if(!$inv) die('Invoice not found.');

if($_SERVER['REQUEST_METHOD']==='POST'){
  $discount=(float)($_POST['discount'] ?? 0);
  if($discount<0) $error='Discount cannot be negative.';
  elseif($discount>(float)$inv['subtotal']) $error='Discount cannot be greater than subtotal.';
  else{
    $total=(float)$inv['subtotal'] - $discount;
    $pdo->prepare("UPDATE Invoices SET discount=?, total_amount=? WHERE invoice_id=?")
        ->execute([$discount,$total,$id]);
    $success='Discount applied.';
    $stmt->execute([$id]);
    $inv=$stmt->fetch();
  }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Discount Invoice</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>Apply Discount</h1>
  <p class="small">Invoice #<?php echo (int)$inv['invoice_id']; ?> &bull; Subtotal: <b><?php echo number_format((float)$inv['subtotal'],2); ?></b></p>

  <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

  <form method="post" class="form-row">
    <div class="span-4"><label>Discount amount</label><input type="number" step="0.01" min="0" name="discount" value="<?php echo htmlspecialchars($inv['discount']); ?>" required></div>
    <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn" type="submit">Save</button>
      <a class="btn secondary" href="invoice_print.php?id=<?php echo $id; ?>">Back</a>
    </div>
  </form>

  <div class="alert">New total: <b><?php echo number_format((float)$inv['total_amount'],2); ?></b></div>
</div></div>
<script src="../assets/app.js"></script>
</body></html>
