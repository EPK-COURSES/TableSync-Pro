<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
$role = $_SESSION['role'] ?? 'customer';
$active = 'tables';

$tables = $pdo->query("SELECT table_id, capacity, status, location FROM Restaurant_Tables ORDER BY table_id ASC")->fetchAll();

function badge_class(string $s): string {
  if($s==='Available') return 'green';
  if($s==='Reserved') return 'orange';
  if($s==='Occupied') return 'red';
  return 'grey';
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tables</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>Tables</h1>
  <div class="small">Tap a table to start an action (waiter)</div>

  <div class="grid" style="margin-top:10px;">
    <?php foreach($tables as $t):
      $s=$t['status'];
      $href='';
      if($role==='waiter'){
        if($s==='Available' || $s==='Reserved') $href='tables_mark_occupied.php?quick='.(int)$t['table_id'];
        if($s==='Occupied') $href='orders_new.php?table_id='.(int)$t['table_id'];
      }
    ?>
      <div class="col-3">
        <div class="tile clickable" <?php echo $href?('data-href="'.htmlspecialchars($href).'"'):''; ?>>
          <div class="title">Table #<?php echo (int)$t['table_id']; ?></div>
          <div class="meta">
            <span class="badge <?php echo badge_class($t['status']); ?>"><?php echo htmlspecialchars($t['status']); ?></span>
            <span class="pill">Cap: <?php echo (int)$t['capacity']; ?></span>
          </div>
          <div class="small"><?php echo htmlspecialchars($t['location'] ?? ''); ?></div>
          <?php if($href): ?><div class="actions"><span class="btn secondary">Tap to open</span></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card" style="margin-top:16px;">
    <h3>List view</h3>
    <table class="table">
      <thead><tr><th>Table</th><th>Capacity</th><th>Location</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($tables as $t): ?>
          <tr>
            <td>#<?php echo (int)$t['table_id']; ?></td>
            <td><?php echo (int)$t['capacity']; ?></td>
            <td><?php echo htmlspecialchars($t['location'] ?? ''); ?></td>
            <td><span class="badge <?php echo badge_class($t['status']); ?>"><?php echo htmlspecialchars($t['status']); ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$tables): ?><tr><td colspan="4" class="small">No tables found. Manager can add in Settings.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if($role==='waiter'): ?>
<div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="../includes/invoice_generate.php">Invoice</a>
</div>
<?php endif; ?>
<script src="../assets/app.js"></script>
</body></html>
