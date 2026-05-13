<?php
require __DIR__ . '/../includes/auth.php';
require_role('waiter');
require __DIR__ . '/../config/db.php';

$orderId=(int)($_GET['order_id'] ?? 0);
$stmt=$pdo->prepare("SELECT status FROM Orders WHERE order_id=?");
$stmt->execute([$orderId]);
$order=$stmt->fetch();
if($order && $order['status']==='Pending'){
  $pdo->prepare("UPDATE Orders SET status='Cancelled' WHERE order_id=?")->execute([$orderId]);
}
header('Location: orders_active.php');
exit;
?>
