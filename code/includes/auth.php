<?php
// auth.php — Session guard
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

function require_role(string $role): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        $target = '../pages/dashboard_' . ($_SESSION['role'] ?? 'customer') . '.php';
        header('Location: ' . $target);
        exit;
    }
}
?>
