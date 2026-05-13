<?php
require __DIR__ . '/../includes/auth.php';
require_role('manager');
require __DIR__ . '/../config/db.php';

$rev=$pdo->query("SELECT DATE(generation_date) day, SUM(total_amount) revenue FROM Invoices
                  WHERE payment_status='Paid' GROUP BY DATE(generation_date) ORDER BY day DESC LIMIT 14")->fetchAll();
$resCount=$pdo->query("SELECT status, COUNT(*) c FROM Reservations GROUP BY status")->fetchAll();
$topMenu=$pdo->query("SELECT mi.name, SUM(od.quantity) qty FROM Order_Details od
                      JOIN Menu_Items mi ON mi.menu_item_id=od.menu_item_id
                      JOIN Orders o ON o.order_id=od.order_id
                      WHERE o.status IN ('Preparing','Served')
                      GROUP BY mi.name ORDER BY qty DESC LIMIT 10")->fetchAll();
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="../includes/logout.php">Logout</a>
  </div>
</div>
<div class="container"><div class="grid">
  <div class="col-6"><div class="card">
    <h2>Revenue (14 days)</h2>
    <table class="table"><thead><tr><th>Date</th><th>Revenue</th></tr></thead><tbody>
      <?php foreach($rev as $r): ?><tr><td><?php echo htmlspecialchars($r['day']); ?></td><td><b><?php echo number_format((float)$r['revenue'],2); ?></b></td></tr><?php endforeach; ?>
      <?php if(!$rev): ?><tr><td colspan="2" class="small">No paid invoices yet.</td></tr><?php endif; ?>
    </tbody></table>
  </div></div>

  <div class="col-6"><div class="card">
    <h2>Reservations Summary</h2>
    <table class="table"><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>
      <?php foreach($resCount as $r): ?><tr><td><?php echo htmlspecialchars($r['status']); ?></td><td><b><?php echo (int)$r['c']; ?></b></td></tr><?php endforeach; ?>
      <?php if(!$resCount): ?><tr><td colspan="2" class="small">No reservations yet.</td></tr><?php endif; ?>
    </tbody></table>
  </div></div>

  <div class="col-12"><div class="card">
    <h2>Top Ordered Items</h2>
    <table class="table"><thead><tr><th>Item</th><th>Quantity</th></tr></thead><tbody>
      <?php foreach($topMenu as $t): ?><tr><td><?php echo htmlspecialchars($t['name']); ?></td><td><b><?php echo (int)$t['qty']; ?></b></td></tr><?php endforeach; ?>
      <?php if(!$topMenu): ?><tr><td colspan="2" class="small">No order history yet.</td></tr><?php endif; ?>
    </tbody></table>
  </div></div>
</div></div>
<script src="../assets/app.js"></script>
</body></html>