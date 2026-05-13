<?php
require __DIR__ . '/../includes/auth.php';
if(($_SESSION['role'] ?? '')!=='waiter' && ($_SESSION['role'] ?? '')!=='manager') die('Access denied.');
require __DIR__ . '/../config/db.php';
$active='orders';

$rows=$pdo->query("SELECT o.*, u.first_name, u.last_name
                   FROM Orders o LEFT JOIN Users u ON u.user_id=o.processed_by
                   WHERE o.status IN ('Pending','Preparing')
                   ORDER BY o.order_datetime DESC")->fetchAll();

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
<title>Active Orders</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>Active Orders</h1>
  <p class="small">Tap any row to open it.</p>

  <div class="card">
    <table class="table">
      <thead><tr><th>Order</th><th>Table</th><th>Status</th><th>Total</th><th class="no-print">Actions</th></tr></thead>
      <tbody>
        <?php foreach($rows as $o): $href='orders_add_items.php?order_id='.(int)$o['order_id']; ?>
          <tr class="rowlink" data-href="<?php echo htmlspecialchars($href); ?>">
            <td>#<?php echo (int)$o['order_id']; ?><div class="small"><?php echo htmlspecialchars($o['order_datetime']); ?></div></td>
            <td>#<?php echo (int)$o['table_id']; ?><div class="small"><?php echo htmlspecialchars(trim(($o['first_name']??'').' '.($o['last_name']??''))); ?></div></td>
            <td><span class="badge <?php echo badge_class($o['status']); ?>"><?php echo htmlspecialchars($o['status']); ?></span></td>
            <td><b><?php echo number_format((float)$o['total_price'],2); ?></b></td>
            <td class="no-print" style="display:flex; gap:10px; flex-wrap:wrap;">
              <a class="btn secondary" href="<?php echo htmlspecialchars($href); ?>">Open</a>
              <a class="btn secondary" href="orders_update_status.php?order_id=<?php echo (int)$o['order_id']; ?>">Status</a>
              <?php if(($_SESSION['role'] ?? '')==='waiter' && $o['status']==='Pending'): ?>
                <a class="btn danger" href="orders_cancel.php?order_id=<?php echo (int)$o['order_id']; ?>" onclick="return confirm('Cancel this order?')">Cancel</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="5" class="small">No active orders.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php if(($_SESSION['role'] ?? '')==='waiter'): ?><div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="../includes/invoice_generate.php">Invoice</a>
</div><?php endif; ?>
<script src="../assets/app.js"></script>
</body></html>
