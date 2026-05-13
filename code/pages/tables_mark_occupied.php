<?php
require __DIR__ . '/../includes/auth.php';
require_role('waiter');
require __DIR__ . '/../config/db.php';
$active = 'checkin';

$error=''; $success='';

$quick = (int)($_GET['quick'] ?? 0);
if ($quick > 0) {
  $_POST['table_id'] = $quick;
  $_SERVER['REQUEST_METHOD'] = 'POST';
}

$tables = $pdo->query("SELECT table_id, capacity, status, location FROM Restaurant_Tables
                       WHERE status IN ('Reserved','Available') ORDER BY table_id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tableId = (int)($_POST['table_id'] ?? 0);
  if ($tableId <= 0) {
    $error = 'Please choose a table.';
  } else {
    $pdo->prepare("UPDATE Restaurant_Tables SET status='Occupied' WHERE table_id=?")
        ->execute([$tableId]);

    $today = date('Y-m-d');
    $rStmt = $pdo->prepare("SELECT reservation_id FROM Reservations
                            WHERE table_id=? AND reservation_date=? AND status='Confirmed'
                            ORDER BY reservation_time ASC LIMIT 1");
    $rStmt->execute([$tableId, $today]);
    $res = $rStmt->fetch();
    $reservationId = $res ? (int)$res['reservation_id'] : null;

    $pdo->prepare("INSERT INTO Dining_History (check_in_time, table_id, reservation_id) VALUES (NOW(), ?, ?)")
        ->execute([$tableId, $reservationId]);

    $success = 'Checked in Table #' . $tableId . ' (now Occupied).';

    $tables = $pdo->query("SELECT table_id, capacity, status, location FROM Restaurant_Tables
                           WHERE status IN ('Reserved','Available') ORDER BY table_id")->fetchAll();
  }
}

function badge_class(string $s): string {
  if($s==='Available') return 'green';
  if($s==='Reserved') return 'orange';
  return 'grey';
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Check&#8209;In (Mark Occupied)</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>Check&#8209;In</h1>
  <p class="small">Tap a table to mark it <b>Occupied</b>.</p>

  <?php if ($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

  <div class="grid" style="margin-top:10px;">
    <?php foreach($tables as $t): ?>
      <div class="col-3">
        <form method="post" class="tile" style="gap:10px;">
          <div class="title">Table #<?php echo (int)$t['table_id']; ?></div>
          <div class="meta">
            <span class="badge <?php echo badge_class($t['status']); ?>"><?php echo htmlspecialchars($t['status']); ?></span>
            <span class="pill">Cap: <?php echo (int)$t['capacity']; ?></span>
          </div>
          <div class="small"><?php echo htmlspecialchars($t['location'] ?? ''); ?></div>
          <input type="hidden" name="table_id" value="<?php echo (int)$t['table_id']; ?>">
          <button class="btn orange" type="submit">Mark Occupied</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if(!$tables): ?>
    <div class="alert">No Available/Reserved tables. Everything is occupied.</div>
  <?php endif; ?>

  <div class="alert"><b>Next:</b> Start an order from <a href="orders_new.php">New Order</a>.</div>
</div>
<div class="pos-footer">
  <a class="<?php echo ($active==='tables')?'active':''; ?>" href="tables_status.php">Tables</a>
  <a class="<?php echo ($active==='checkin')?'active':''; ?>" href="tables_mark_occupied.php">Check&#8209;In</a>
  <a class="<?php echo ($active==='orders')?'active':''; ?>" href="orders_active.php">Orders</a>
  <a class="<?php echo ($active==='invoice')?'active':''; ?>" href="../includes/invoice_generate.php">Invoice</a>
</div>
<script src="../assets/app.js"></script>
</body></html>
