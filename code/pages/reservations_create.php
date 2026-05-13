<?php
require __DIR__ . '/../includes/auth.php';
require_role('customer');
require __DIR__ . '/../config/db.php';

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $date=$_POST['reservation_date'] ?? '';
  $time=$_POST['reservation_time'] ?? '';
  $party=(int)($_POST['party_size'] ?? 0);
  $notes=trim($_POST['special_requests'] ?? '');

  if(!$date || !$time) $error='Please choose a date and time.';
  elseif($party<=0) $error='Party size must be greater than 0.';
  else {
    $sql="SELECT t.table_id, t.capacity
          FROM Restaurant_Tables t
          WHERE t.capacity>=?
            AND t.status IN ('Available','Reserved')
            AND t.table_id NOT IN (
              SELECT r.table_id FROM Reservations r
              WHERE r.reservation_date=? AND r.reservation_time=? AND r.status='Confirmed'
            )
          ORDER BY t.capacity ASC LIMIT 1";
    $stmt=$pdo->prepare($sql);
    $stmt->execute([$party,$date,$time]);
    $table=$stmt->fetch();

    if(!$table) $error='No available table found for that date/time and party size.';
    else {
      $pdo->prepare("INSERT INTO Reservations (reservation_date,reservation_time,party_size,status,special_requests,user_id,table_id)
                     VALUES (?,?,?,'Confirmed',?,?,?)")
          ->execute([$date,$time,$party,$notes?:null,(int)$_SESSION['user_id'],(int)$table['table_id']]);
      $pdo->prepare("UPDATE Restaurant_Tables SET status='Reserved' WHERE table_id=?")
          ->execute([(int)$table['table_id']]);
      $success='Reservation confirmed! Your table is #' . (int)$table['table_id'];
    }
  }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Reservation</title><link rel="stylesheet" href="../assets/style.css"></head>
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
    <h1>Create Reservation</h1>
    <p class="small">We pick the smallest available table that fits your party.</p>
    <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <form method="post" class="form-row">
      <div class="span-4"><label>Date</label><input type="date" name="reservation_date" required></div>
      <div class="span-4"><label>Time</label><input type="time" name="reservation_time" required></div>
      <div class="span-4"><label>Party size</label><input type="number" name="party_size" min="1" required></div>
      <div class="span-12"><label>Special requests</label><textarea name="special_requests" placeholder="Optional"></textarea></div>
      <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn" type="submit">Confirm</button>
        <a class="btn secondary" href="reservations_history.php">My Reservations</a>
      </div>
    </form>
  </div></div>
  <script src="../assets/app.js"></script>
</body></html>
