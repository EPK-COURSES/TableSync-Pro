<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer Dashboard - TableSync Pro</title>
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
    <h1>Customer Dashboard</h1>
    <p class="small">Hi <?php echo htmlspecialchars($_SESSION['first_name']); ?> &mdash; choose a quick action.</p>
    <div class="grid">
      
        <div class="col-4">
          <div class="tile">
            <div class="title">Create Reservation</div>
            <div class="small">Book a table and avoid conflicts.</div>
            <div class="actions"><a class="btn" href="reservations_create.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">My Reservations</div>
            <div class="small">Update/cancel your bookings.</div>
            <div class="actions"><a class="btn secondary" href="reservations_history.php">Open</a></div>
          </div>
        </div>
        

        <div class="col-4">
          <div class="tile">
            <div class="title">Browse Menu</div>
            <div class="small">Search menu items.</div>
            <div class="actions"><a class="btn secondary" href="menu_search.php">Open</a></div>
          </div>
        </div>
        
    </div>
  </div>
  <script src="../assets/app.js"></script>
</body>
</html>
