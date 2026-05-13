<?php
session_start();
require __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($password === '') {
        $error = 'Please enter your password.';
    } else {
        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.password, u.account_status, r.role_name
                FROM Users u JOIN Roles r ON r.role_id = u.role_id
                WHERE u.email = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'No account found for that email.';
        } elseif ($user['account_status'] !== 'Active') {
            $error = 'Your account is not active. Please contact manager.';
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Incorrect password.';
        } else {
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            $role = strtolower(trim($user['role_name']));
            if (!in_array($role, ['customer','waiter','manager'])) $role = 'customer';
            $_SESSION['role'] = $role;

            header('Location: dashboard_' . $role . '.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — TableSync Pro</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f4f3ef;
      font-family: system-ui, -apple-system, sans-serif;
      padding: 20px;
    }

    .login-wrap {
      width: 100%;
      max-width: 420px;
    }

    /* Brand header */
    .login-brand {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-brand .dot {
      display: inline-block;
      width: 10px;
      height: 10px;
      background: #01696f;
      border-radius: 50%;
      margin-right: 6px;
      vertical-align: middle;
    }
    .login-brand h1 {
      display: inline;
      font-size: 1.35rem;
      font-weight: 700;
      color: #1a1a18;
      vertical-align: middle;
      letter-spacing: -0.01em;
    }
    .login-brand p {
      margin-top: 6px;
      font-size: 0.85rem;
      color: #7a7974;
    }

    /* Card */
    .login-card {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #dcd9d5;
      box-shadow: 0 4px 20px rgba(0,0,0,0.07);
      padding: 32px 28px;
    }

    /* Error alert */
    .login-error {
      background: #fdf0f3;
      border: 1px solid #f2c4cc;
      border-radius: 8px;
      color: #a12c3a;
      font-size: 0.85rem;
      padding: 10px 14px;
      margin-bottom: 20px;
    }

    /* Form fields */
    .field {
      margin-bottom: 16px;
    }
    .field label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: #3a3935;
      margin-bottom: 5px;
      letter-spacing: 0.01em;
    }
    .field input {
      width: 100%;
      padding: 9px 12px;
      font-size: 0.95rem;
      border: 1px solid #d4d1ca;
      border-radius: 8px;
      background: #fafaf8;
      color: #1a1a18;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .field input:focus {
      border-color: #01696f;
      box-shadow: 0 0 0 3px rgba(1,105,111,0.12);
      background: #fff;
    }

    /* Primary login button */
    .btn-login {
      width: 100%;
      padding: 11px;
      background: #01696f;
      color: #fff;
      font-size: 0.95rem;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.15s;
      margin-top: 4px;
    }
    .btn-login:hover { background: #0c4e54; }
    .btn-login:active { background: #0f3638; }

    /* Bottom links */
    .login-links {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-top: 16px;
    }
    .login-links a {
      font-size: 0.82rem;
      color: #01696f;
      text-decoration: none;
    }
    .login-links a:hover { text-decoration: underline; }

    /* Quick-login demo section */
    .demo-section {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid #edeae5;
    }
    .demo-label {
      text-align: center;
      font-size: 0.75rem;
      font-weight: 600;
      color: #bab9b4;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      margin-bottom: 12px;
    }
    .demo-buttons {
      display: flex;
      gap: 8px;
    }
    .demo-btn {
      flex: 1;
      padding: 8px 6px;
      border: 1px solid #d4d1ca;
      border-radius: 8px;
      background: #fafaf8;
      cursor: pointer;
      text-align: center;
      transition: border-color 0.15s, background 0.15s;
      font-size: 0;
    }
    .demo-btn:hover {
      border-color: #01696f;
      background: #f0f7f7;
    }
    .demo-btn .role-icon {
      display: block;
      font-size: 1.3rem;
      margin-bottom: 4px;
      line-height: 1;
    }
    .demo-btn .role-name {
      display: block;
      font-size: 0.72rem;
      font-weight: 600;
      color: #3a3935;
      letter-spacing: 0.02em;
    }
    .demo-btn .role-email {
      display: block;
      font-size: 0.67rem;
      color: #7a7974;
      margin-top: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>
<body>
  <div class="login-wrap">

    <div class="login-brand">
      <span class="dot"></span><h1>TableSync Pro</h1>
      <p>Restaurant management system</p>
    </div>

    <div class="login-card">

      <?php if ($error): ?>
        <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" id="loginForm">
        <div class="field">
          <label for="email">Email address</label>
          <input type="email" id="email" name="email"
                 value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                 autocomplete="username" placeholder="you@example.com" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
                 autocomplete="current-password" placeholder="••••••••" required>
        </div>
        <button class="btn-login" type="submit">Sign in</button>
      </form>

      <div class="login-links">
        <a href="register.php">Create account</a>
        <a href="../includes/forgot_password.php">Forgot password?</a>
      </div>

      <!-- Quick demo login buttons -->
      <div class="demo-section">
        <div class="demo-label">Quick demo login</div>
        <div class="demo-buttons">
          <button class="demo-btn" type="button"
                  onclick="autofill('customer@demo.local','demo123')">
            <span class="role-icon">🙋</span>
            <span class="role-name">Customer</span>
            <span class="role-email">customer@demo.local</span>
          </button>
          <button class="demo-btn" type="button"
                  onclick="autofill('waiter@demo.local','demo123')">
            <span class="role-icon">🍽️</span>
            <span class="role-name">Waiter</span>
            <span class="role-email">waiter@demo.local</span>
          </button>
          <button class="demo-btn" type="button"
                  onclick="autofill('manager@demo.local','demo123')">
            <span class="role-icon">📊</span>
            <span class="role-name">Manager</span>
            <span class="role-email">manager@demo.local</span>
          </button>
        </div>
      </div>

    </div>
  </div>

  <script>
    function autofill(email, password) {
      document.getElementById('email').value = email;
      document.getElementById('password').value = password;
      document.getElementById('loginForm').submit();
    }
  </script>
  <script src="../assets/app.js"></script>
</body>
</html>
