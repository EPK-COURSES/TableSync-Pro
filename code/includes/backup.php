<?php
require __DIR__ . '/auth.php';
require_role('manager');
require __DIR__ . '/../config/db.php';

$allowed=['Users','Restaurant_Tables','Reservations','Menu_Items','Orders','Order_Details','Invoices','Dining_History'];
$table=$_GET['table'] ?? '';

if(isset($_GET['download']) && in_array($table,$allowed,true)){
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="'.$table.'_backup_'.date('Ymd_His').'.csv"');
  $out=fopen('php://output','w');
  $stmt=$pdo->query("SELECT * FROM $table");
  $first=$stmt->fetch(PDO::FETCH_ASSOC);
  if($first){
    fputcsv($out, array_keys($first));
    fputcsv($out, array_values($first));
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($out, array_values($row));
  }
  fclose($out);
  exit;
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Backup / Export</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>Backup / Export</h1>
  <p class="small">Export a table to CSV. (For full backup use phpMyAdmin export.)</p>
  <form method="get" class="form-row">
    <div class="span-6"><label>Select table</label>
      <select name="table" required>
        <option value="">-- choose --</option>
        <?php foreach($allowed as $t): ?>
          <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($table===$t)?'selected':''; ?>><?php echo htmlspecialchars($t); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="span-12"><button class="btn" name="download" value="1" type="submit">Download CSV</button></div>
  </form>
</div></div>
<script src="../assets/app.js"></script>
</body></html>
