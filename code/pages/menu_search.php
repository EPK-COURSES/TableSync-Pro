<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');
$params=[]; $where="WHERE 1=1";
if($q!==''){ $where.=" AND name LIKE ?"; $params[]='%'.$q.'%'; }
if($cat!==''){ $where.=" AND category = ?"; $params[]=$cat; }

$cats=$pdo->query("SELECT DISTINCT category FROM Menu_Items WHERE category IS NOT NULL AND category<>'' ORDER BY category")->fetchAll();
$stmt=$pdo->prepare("SELECT * FROM Menu_Items $where ORDER BY category, name");
$stmt->execute($params);
$items=$stmt->fetchAll();
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Menu</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<div class="nav">
  <div class="brand"><span class="dot"></span> TableSync Pro</div>
  <div class="links">
    <span class="pill"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
    <a class="pill" href="dashboard_<?php echo htmlspecialchars($_SESSION['role']); ?>.php">Home</a>
    <a class="pill" href="../includes/logout.php">Logout</a>
  </div>
</div>
<div class="container"><div class="card">
  <h1>Menu</h1>
  <form method="get" class="form-row no-print">
    <div class="span-6"><label>Search</label><input name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="e.g. pizza"></div>
    <div class="span-6"><label>Category</label>
      <select name="category">
        <option value="">All</option>
        <?php foreach($cats as $c): ?>
          <option value="<?php echo htmlspecialchars($c['category']); ?>" <?php echo ($cat===$c['category'])?'selected':''; ?>><?php echo htmlspecialchars($c['category']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn" type="submit">Search</button>
      <a class="btn secondary" href="menu_search.php">Reset</a>
      <?php if(($_SESSION['role'] ?? '')==='manager'): ?><a class="btn secondary" href="menu_manage.php">Manage</a><?php endif; ?>
    </div>
  </form>

  <table class="table">
    <thead><tr><th>Name</th><th>Category</th><th>Description</th><th>Price</th><th>Available</th></tr></thead>
    <tbody>
      <?php foreach($items as $it): ?>
        <tr>
          <td><?php echo htmlspecialchars($it['name']); ?></td>
          <td><?php echo htmlspecialchars($it['category'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($it['description'] ?? ''); ?></td>
          <td><b><?php echo number_format((float)$it['price'],2); ?></b></td>
          <td><?php echo ((int)$it['is_available']===1)?'Yes':'No'; ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$items): ?><tr><td colspan="5" class="small">No menu items found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<script src="../assets/app.js"></script>
</body></html>
