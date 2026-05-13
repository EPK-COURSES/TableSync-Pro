<?php
require __DIR__ . '/auth.php';
require_role('manager');
require __DIR__ . '/../config/db.php';

$rows=$pdo->query("SELECT t.table_id, t.capacity, AVG(h.duration_minutes) avg_minutes, COUNT(h.history_id) samples
                   FROM Restaurant_Tables t
                   LEFT JOIN Dining_History h ON h.table_id=t.table_id AND h.duration_minutes IS NOT NULL
                   GROUP BY t.table_id, t.capacity
                   ORDER BY t.table_id")->fetchAll();
$overall=$pdo->query("SELECT AVG(duration_minutes) avg_minutes, COUNT(*) samples
                      FROM Dining_History WHERE duration_minutes IS NOT NULL")->fetch();
$overallAvg=(float)($overall['avg_minutes'] ?? 0);
$overallSamples=(int)($overall['samples'] ?? 0);
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Turnover Prediction</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>Turnover Prediction</h1>
  <p class="small">Overall average duration: <b><?php echo $overallAvg ? number_format($overallAvg,1) : 'N/A'; ?></b> minutes (samples: <?php echo $overallSamples; ?>)</p>
  <table class="table">
    <thead><tr><th>Table</th><th>Capacity</th><th>Avg Duration</th><th>Samples</th><th>Turnovers / Hour</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r):
        $avg=(float)($r['avg_minutes'] ?? 0);
        $turn=$avg>0 ? (60.0/$avg) : 0;
      ?>
        <tr>
          <td>#<?php echo (int)$r['table_id']; ?></td>
          <td><?php echo (int)$r['capacity']; ?></td>
          <td><?php echo $avg>0 ? number_format($avg,1) : 'N/A'; ?></td>
          <td><?php echo (int)$r['samples']; ?></td>
          <td><?php echo $avg>0 ? number_format($turn,2) : 'N/A'; ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?><tr><td colspan="5" class="small">No tables found.</td></tr><?php endif; ?>
    </tbody>
  </table>
  <div class="alert"><b>Tip:</b> Duration is recorded when you mark an invoice as <b>Paid</b>.</div>
</div></div>
<script src="../assets/app.js"></script>
</body></html>
