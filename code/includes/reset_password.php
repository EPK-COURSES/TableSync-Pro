<?php
session_start();
require __DIR__ . '/../config/db.php';

$token = $_GET['token'] ?? '';
$error=''; $success='';

$stmt = $pdo->prepare("SELECT user_id, expires_at FROM Password_Reset_Tokens WHERE token=?");
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    $error = 'Invalid or expired token.';
} elseif (strtotime($row['expires_at']) < time()) {
    $error = 'This token has expired.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p1 = $_POST['password'] ?? '';
    $p2 = $_POST['password2'] ?? '';

    if (strlen($p1) < 6) $error = 'Password must be at least 6 characters.';
    elseif ($p1 !== $p2) $error = 'Passwords do not match.';
    else {
        $hash = password_hash($p1, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE Users SET password=? WHERE user_id=?")->execute([$hash, (int)$row['user_id']]);
        $pdo->prepare("DELETE FROM Password_Reset_Tokens WHERE user_id=?")->execute([(int)$row['user_id']]);
        $success = 'Password updated! You can now login.';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password - TableSync Pro</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>Set new password</h1>

      <?php if ($error): ?>
        <div class="alert err"><?php echo htmlspecialchars($error); ?></div>
        <a class="btn secondary" href="../pages/login.php">Back</a>
      <?php elseif ($success): ?>
        <div class="alert ok"><?php echo htmlspecialchars($success); ?></div>
        <a class="btn" href="../pages/login.php">Go to login</a>
      <?php else: ?>
        <form method="post" class="form-row">
          <div class="span-6"><label>New password</label><input type="password" name="password" required></div>
          <div class="span-6"><label>Confirm password</label><input type="password" name="password2" required></div>
          <div class="span-12"><button class="btn" type="submit">Update password</button></div>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <script src="../assets/app.js"></script>
</body>
</html>
