<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manager Dashboard - TableSync Pro</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="../includes/logout.php">Logout</a>
  </div>
</div>
  <div class="container">
    <h1>Manager Dashboard</h1>
    <p class="small">Hi <?php echo htmlspecialchars($_SESSION['first_name']); ?> &mdash; choose a quick action.</p>
    <div class="grid">
      
        <div class="col-4">
          <div class="tile">
            <div class="title">Menu Management</div>
            <div class="small">Add/edit/delete menu items.</div>
            <div class="actions"><a class="btn" href="menu_manage.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Reports</div>
            <div class="small">Revenue and summaries.</div>
            <div class="actions"><a class="btn secondary" href="reports.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Turnover Prediction</div>
            <div class="small">Predict table turnover.</div>
            <div class="actions"><a class="btn secondary" href="../includes/turnover_prediction.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Settings</div>
            <div class="small">Tables and restaurant settings.</div>
            <div class="actions"><a class="btn" href="settings.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Backup / Export</div>
            <div class="small">Download CSV backups.</div>
            <div class="actions"><a class="btn secondary" href="../includes/backup.php">Open</a></div>
          </div>
        </div>
        
    </div>
  </div>
  <script src="../assets/app.js"></script>
</body>
</html>
