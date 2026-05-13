<?php
require __DIR__ . '/../includes/auth.php';
require_role('customer');
require __DIR__ . '/../config/db.php';

$id=(int)($_GET['id'] ?? 0);
$stmt=$pdo->prepare("SELECT reservation_id, table_id, status FROM Reservations WHERE reservation_id=? AND user_id=? LIMIT 1");
$stmt->execute([$id,(int)$_SESSION['user_id']]);
$res=$stmt->fetch();
if(!$res) die('Reservation not found.');

if($res['status']!=='Confirmed') { header('Location: reservations_history.php'); exit; }

$pdo->prepare("UPDATE Reservations SET status='Cancelled' WHERE reservation_id=? AND user_id=?")
    ->execute([$id,(int)$_SESSION['user_id']]);

$tableId=(int)$res['table_id'];
$check=$pdo->prepare("SELECT COUNT(*) c FROM Reservations WHERE table_id=? AND status='Confirmed'");
$check->execute([$tableId]);
$count=(int)($check->fetch()['c'] ?? 0);
if($count===0){
  $pdo->prepare("UPDATE Restaurant_Tables SET status='Available' WHERE table_id=?")->execute([$tableId]);
}

header('Location: reservations_history.php');
exit;
?>
