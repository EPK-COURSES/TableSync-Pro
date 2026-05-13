<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'customer';
    header('Location: dashboard_' . $role . '.php');
    exit;
}
header('Location: login.php');
exit;
?>
