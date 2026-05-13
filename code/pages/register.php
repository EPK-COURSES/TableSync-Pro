<?php
session_start();
require __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($first === '' || $last === '') {
        $error = 'First and last name are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif (strlen($pass1) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $hash = password_hash($pass1, PASSWORD_BCRYPT);
            $roleStmt = $pdo->prepare("SELECT role_id FROM Roles WHERE LOWER(role_name)=LOWER('Customer') LIMIT 1");
            $roleStmt->execute();
            $roleRow = $roleStmt->fetch();
            $roleId = $roleRow ? (int)$roleRow['role_id'] : 1;

            $sql = "INSERT INTO Users (first_name,last_name,phone,email,password,role_id) VALUES (?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute([$first,$last,$phone?:null,$email,$hash,$roleId]);
            $success = 'Account created! You can now login.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - TableSync Pro</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>Create Account</h1>
      <p class="small">New accounts default to <b>Customer</b>.</p>

      <?php if ($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

      <form method="post" class="form-row">
        <div class="span-6"><label>First name</label><input name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required></div>
        <div class="span-6"><label>Last name</label><input name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required></div>
        <div class="span-6"><label>Phone</label><input name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="Optional"></div>
        <div class="span-6"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required></div>
        <div class="span-6"><label>Password</label><input type="password" name="password" required></div>
        <div class="span-6"><label>Confirm password</label><input type="password" name="password2" required></div>
        <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn" type="submit">Register</button>
          <a class="btn secondary" href="login.php">Back to login</a>
        </div>
      </form>
    </div>
  </div>
  <script src="../assets/app.js"></script>
</body>
</html>
