<?php
require __DIR__ . '/../includes/auth.php';
require_role('customer');
require __DIR__ . '/../config/db.php';

$id=(int)($_GET['id'] ?? 0);
$error=''; $success='';

$stmt=$pdo->prepare("SELECT * FROM Reservations WHERE reservation_id=? AND user_id=? LIMIT 1");
$stmt->execute([$id,(int)$_SESSION['user_id']]);
$res=$stmt->fetch();
if(!$res) die('Reservation not found.');
if($res['status']!=='Confirmed') die('Only Confirmed reservations can be modified.');

if($_SERVER['REQUEST_METHOD']==='POST'){
  $date=$_POST['reservation_date'] ?? '';
  $time=$_POST['reservation_time'] ?? '';
  $party=(int)($_POST['party_size'] ?? 0);
  $notes=trim($_POST['special_requests'] ?? '');

  if(!$date || !$time) $error='Please choose a date and time.';
  elseif($party<=0) $error='Party size must be greater than 0.';
  else{
    $sql="SELECT t.table_id, t.capacity
          FROM Restaurant_Tables t
          WHERE t.capacity>=?
            AND t.table_id NOT IN (
              SELECT r2.table_id FROM Reservations r2
              WHERE r2.reservation_date=? AND r2.reservation_time=? AND r2.status='Confirmed'
                AND r2.reservation_id<>?
            )
          ORDER BY (t.table_id=?) DESC, t.capacity ASC
          LIMIT 1";
    $stmt2=$pdo->prepare($sql);
    $stmt2->execute([$party,$date,$time,$id,(int)$res['table_id']]);
    $table=$stmt2->fetch();

    if(!$table) $error='No available table found for that new date/time.';
    else{
      $oldTable=(int)$res['table_id'];
      $newTable=(int)$table['table_id'];

      $pdo->prepare("UPDATE Reservations SET reservation_date=?, reservation_time=?, party_size=?, special_requests=?, table_id=?
                     WHERE reservation_id=? AND user_id=?")
          ->execute([$date,$time,$party,$notes?:null,$newTable,$id,(int)$_SESSION['user_id']]);

      $pdo->prepare("UPDATE Restaurant_Tables SET status='Reserved' WHERE table_id=?")
          ->execute([$newTable]);

      if($oldTable!==$newTable){
        $check=$pdo->prepare("SELECT COUNT(*) c FROM Reservations WHERE table_id=? AND status='Confirmed'");
        $check->execute([$oldTable]);
        $count=(int)($check->fetch()['c'] ?? 0);
        if($count===0){
          $pdo->prepare("UPDATE Restaurant_Tables SET status='Available' WHERE table_id=?")
              ->execute([$oldTable]);
        }
      }

      $success='Reservation updated.';
      $stmt->execute([$id,(int)$_SESSION['user_id']]);
      $res=$stmt->fetch();
    }
  }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Update Reservation</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <h1>Update Reservation</h1>
  <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

  <form method="post" class="form-row">
    <div class="span-4"><label>Date</label><input type="date" name="reservation_date" value="<?php echo htmlspecialchars($res['reservation_date']); ?>" required></div>
    <div class="span-4"><label>Time</label><input type="time" name="reservation_time" value="<?php echo htmlspecialchars(substr($res['reservation_time'],0,5)); ?>" required></div>
    <div class="span-4"><label>Party size</label><input type="number" name="party_size" min="1" value="<?php echo (int)$res['party_size']; ?>" required></div>
    <div class="span-12"><label>Special requests</label><textarea name="special_requests"><?php echo htmlspecialchars($res['special_requests'] ?? ''); ?></textarea></div>
    <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn" type="submit">Save</button>
      <a class="btn secondary" href="reservations_history.php">Back</a>
    </div>
  </form>

  <p class="small">Current table: <b>#<?php echo (int)$res['table_id']; ?></b></p>
</div></div>
<script src="../assets/app.js"></script>
</body></html>
