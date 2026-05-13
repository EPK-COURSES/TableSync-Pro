<?php
session_start();
require __DIR__ . '/../config/db.php';
$msg=''; $error=''; $resetLink='';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $msg = 'If that email exists, a reset link was created.';

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $pdo->prepare("DELETE FROM Password_Reset_Tokens WHERE user_id=?")->execute([(int)$user['user_id']]);
            $pdo->prepare("INSERT INTO Password_Reset_Tokens (user_id,token,expires_at) VALUES (?,?,?)")
                ->execute([(int)$user['user_id'], $token, $expires]);
            $resetLink = 'reset_password.php?token=' . urlencode($token);
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password - TableSync Pro</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>Reset password</h1>
      <p class="small">Enter your email. (Local demo shows the link on-screen.)</p>

      <?php if ($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <?php if ($msg): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

      <form method="post" class="form-row">
        <div class="span-12"><label>Email</label><input type="email" name="email" required></div>
        <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn" type="submit">Create reset link</button>
          <a class="btn secondary" href="../pages/login.php">Back</a>
        </div>
      </form>

      <?php if ($resetLink): ?>
        <div class="alert"><b>Local demo link:</b><div><a href="<?php echo htmlspecialchars($resetLink); ?>"><?php echo htmlspecialchars($resetLink); ?></a></div></div>
      <?php endif; ?>
    </div>
  </div>
  <script src="../assets/app.js"></script>
</body>
</html>
