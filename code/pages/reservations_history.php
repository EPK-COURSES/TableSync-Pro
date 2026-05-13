<?php
require __DIR__ . '/../includes/auth.php';
require_role('customer');
require __DIR__ . '/../config/db.php';

$stmt=$pdo->prepare("SELECT r.*, t.capacity FROM Reservations r
                     JOIN Restaurant_Tables t ON t.table_id=r.table_id
                     WHERE r.user_id=?
                     ORDER BY r.reservation_date DESC, r.reservation_time DESC");
$stmt->execute([(int)$_SESSION['user_id']]);
$rows=$stmt->fetchAll();

function badge_class(string $s): string {
  if ($s==='Confirmed') return 'green';
  if ($s==='Cancelled') return 'red';
  return 'grey';
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Reservations</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>My Reservations</h1>
  <div class="small">Tap a row to update if it is Confirmed.</div>
  <table class="table">
    <thead><tr><th>Date</th><th>Time</th><th>Party</th><th>Table</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r):
        $href = ($r['status']==='Confirmed') ? ('reservations_update.php?id='.(int)$r['reservation_id']) : '';
      ?>
        <tr class="rowlink" <?php echo $href?('data-href="'.htmlspecialchars($href).'"'):''; ?> >
          <td><?php echo htmlspecialchars($r['reservation_date']); ?></td>
          <td><?php echo htmlspecialchars(substr($r['reservation_time'],0,5)); ?></td>
          <td><?php echo (int)$r['party_size']; ?></td>
          <td>#<?php echo (int)$r['table_id']; ?> (<?php echo (int)$r['capacity']; ?>)</td>
          <td><span class="badge <?php echo badge_class($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
          <td class="no-print">
            <?php if($r['status']==='Confirmed'): ?>
              <a class="btn secondary" href="reservations_update.php?id=<?php echo (int)$r['reservation_id']; ?>">Update</a>
              <a class="btn danger" href="reservations_cancel.php?id=<?php echo (int)$r['reservation_id']; ?>" onclick="return confirm('Cancel this reservation?')">Cancel</a>
            <?php else: ?>&mdash;<?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?><tr><td colspan="6" class="small">No reservations yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<script src="../assets/app.js"></script>
</body></html>
