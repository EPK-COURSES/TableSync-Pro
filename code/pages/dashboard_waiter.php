<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Waiter Dashboard - TableSync Pro</title>
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
    <h1>Waiter Dashboard </h1>
    <p class="small">Hi <?php echo htmlspecialchars($_SESSION['first_name']); ?> &mdash; choose a quick action.</p>
    <div class="grid">
      
        <div class="col-4">
          <div class="tile">
            <div class="title">Check&#8209;In Customer</div>
            <div class="small">Mark a table as occupied (fast).</div>
            <div class="actions"><a class="btn orange" href="tables_mark_occupied.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">New Order</div>
            <div class="small">Start a new order for an occupied table.</div>
            <div class="actions"><a class="btn" href="orders_new.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Active Orders</div>
            <div class="small">Update kitchen/served status.</div>
            <div class="actions"><a class="btn secondary" href="orders_active.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Generate Invoice</div>
            <div class="small">Create invoice for a table.</div>
            <div class="actions"><a class="btn" href="../includes/invoice_generate.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Tables</div>
            <div class="small">Quick availability view.</div>
            <div class="actions"><a class="btn secondary" href="tables_status.php">Open</a></div>
          </div>
        </div>
        
    </div>
  </div>
  <script src="../assets/app.js"></script>
</body>
</html>
