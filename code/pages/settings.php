<?php
require __DIR__ . '/../includes/auth.php';
require_role('manager');
require __DIR__ . '/../config/db.php';

$error=''; $success='';

if(isset($_POST['save_table'])){
  $tableId=(int)($_POST['table_id'] ?? 0);
  $cap=(int)($_POST['capacity'] ?? 0);
  $loc=trim($_POST['location'] ?? '');

  if($cap<=0) $error='Capacity must be greater than 0.';
  else{
    if($tableId>0){
      $pdo->prepare("UPDATE Restaurant_Tables SET capacity=?, location=? WHERE table_id=?")
          ->execute([$cap,$loc?:null,$tableId]);
      $success='Table updated.';
    } else {
      $pdo->prepare("INSERT INTO Restaurant_Tables (capacity,status,location) VALUES (?, 'Available', ?)")
          ->execute([$cap,$loc?:null]);
      $success='Table added.';
    }
  }
}

if(isset($_GET['delete_table'])){
  $delId=(int)$_GET['delete_table'];
  $pdo->prepare("DELETE FROM Restaurant_Tables WHERE table_id=?")->execute([$delId]);
  header('Location: settings.php'); exit;
}

if(isset($_POST['save_settings'])){
  $pairs=[
    'restaurant_name'=>trim($_POST['restaurant_name'] ?? ''),
    'opening_hours'=>trim($_POST['opening_hours'] ?? ''),
    'tax_rate'=>trim($_POST['tax_rate'] ?? ''),
  ];
  foreach($pairs as $k=>$v){
    $pdo->prepare("INSERT INTO Settings (setting_key, setting_value) VALUES (?,?)
                   ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
        ->execute([$k,$v]);
  }
  $success='Settings saved.';
}

$tables=$pdo->query("SELECT * FROM Restaurant_Tables ORDER BY table_id")->fetchAll();
$srows=$pdo->query("SELECT setting_key, setting_value FROM Settings")->fetchAll();
$settings=[]; foreach($srows as $r){$settings[$r['setting_key']]=$r['setting_value'];}

$editTable=null;
if(isset($_GET['edit_table'])){
  $eid=(int)$_GET['edit_table'];
  foreach($tables as $t){ if((int)$t['table_id']===$eid){$editTable=$t; break;} }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Settings</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="../includes/logout.php">Logout</a>
  </div>
</div>
<div class="container"><div class="grid">
  <div class="col-12"><div class="card">
    <h1>Settings</h1>
    <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <h3>Restaurant</h3>
    <form method="post" class="form-row">
      <input type="hidden" name="save_settings" value="1">
      <div class="span-6"><label>Name</label><input name="restaurant_name" value="<?php echo htmlspecialchars($settings['restaurant_name'] ?? ''); ?>"></div>
      <div class="span-6"><label>Opening Hours</label><input name="opening_hours" value="<?php echo htmlspecialchars($settings['opening_hours'] ?? ''); ?>" placeholder="10:00 - 22:00"></div>
      <div class="span-3"><label>Tax Rate (%)</label><input name="tax_rate" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? ''); ?>" placeholder="20"></div>
      <div class="span-12"><button class="btn" type="submit">Save Settings</button></div>
    </form>
  </div></div>

  <div class="col-12"><div class="card">
    <h3><?php echo $editTable ? 'Edit Table' : 'Add Table'; ?></h3>
    <form method="post" class="form-row">
      <input type="hidden" name="save_table" value="1">
      <input type="hidden" name="table_id" value="<?php echo (int)($editTable['table_id'] ?? 0); ?>">
      <div class="span-3"><label>Capacity</label><input type="number" min="1" name="capacity" value="<?php echo htmlspecialchars($editTable['capacity'] ?? ''); ?>" required></div>
      <div class="span-6"><label>Location</label><input name="location" value="<?php echo htmlspecialchars($editTable['location'] ?? ''); ?>"></div>
      <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn" type="submit">Save Table</button>
        <a class="btn secondary" href="settings.php">Clear</a>
      </div>
    </form>

    <h3 style="margin-top:16px;">All Tables</h3>
    <table class="table">
      <thead><tr><th>Table</th><th>Capacity</th><th>Status</th><th>Location</th><th class="no-print">Actions</th></tr></thead>
      <tbody>
        <?php foreach($tables as $t): ?>
          <tr>
            <td>#<?php echo (int)$t['table_id']; ?></td>
            <td><?php echo (int)$t['capacity']; ?></td>
            <td><?php echo htmlspecialchars($t['status']); ?></td>
            <td><?php echo htmlspecialchars($t['location'] ?? ''); ?></td>
            <td class="no-print" style="display:flex; gap:10px; flex-wrap:wrap;">
              <a class="btn secondary" href="settings.php?edit_table=<?php echo (int)$t['table_id']; ?>">Edit</a>
              <a class="btn danger" href="settings.php?delete_table=<?php echo (int)$t['table_id']; ?>" onclick="return confirm('Delete this table?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$tables): ?><tr><td colspan="5" class="small">No tables yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div></div>
<script src="../assets/app.js"></script>
</body></html>