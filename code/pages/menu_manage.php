<?php
require __DIR__ . '/../includes/auth.php';
require_role('manager');
require __DIR__ . '/../config/db.php';

$action=$_GET['action'] ?? '';
$id=(int)($_GET['id'] ?? 0);
$error=''; $success='';

if($action==='delete' && $id>0){
  $pdo->prepare("DELETE FROM Menu_Items WHERE menu_item_id=?")->execute([$id]);
  header('Location: menu_manage.php'); exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name'] ?? '');
  $desc=trim($_POST['description'] ?? '');
  $cat=trim($_POST['category'] ?? '');
  $price=(float)($_POST['price'] ?? 0);
  $avail=(int)($_POST['is_available'] ?? 1);

  if($name==='') $error='Name is required.';
  elseif($price<=0) $error='Price must be greater than 0.';
  else{
    $mid=(int)($_POST['menu_item_id'] ?? 0);
    if($mid>0){
      $pdo->prepare("UPDATE Menu_Items SET name=?, description=?, category=?, price=?, is_available=? WHERE menu_item_id=?")
          ->execute([$name,$desc?:null,$cat?:null,$price,$avail,$mid]);
      $success='Menu item updated.';
    } else {
      $pdo->prepare("INSERT INTO Menu_Items (name,description,category,price,is_available) VALUES (?,?,?,?,?)")
          ->execute([$name,$desc?:null,$cat?:null,$price,$avail]);
      $success='Menu item added.';
    }
  }
}

$item=null;
if($action==='edit' && $id>0){
  $st=$pdo->prepare("SELECT * FROM Menu_Items WHERE menu_item_id=?");
  $st->execute([$id]);
  $item=$st->fetch();
}
$items=$pdo->query("SELECT * FROM Menu_Items ORDER BY category, name")->fetchAll();
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Menu Management</title><link rel="stylesheet" href="../assets/style.css"></head>
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
  <div class="grid">
    <div class="col-12"><div class="card">
      <h1>Menu Management</h1>
      <?php if($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <?php if($success): ?><div class="alert ok" data-autohide="1"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

      <h3><?php echo $item ? 'Edit Item' : 'Add Item'; ?></h3>
      <form method="post" class="form-row">
        <input type="hidden" name="menu_item_id" value="<?php echo (int)($item['menu_item_id'] ?? 0); ?>">
        <div class="span-6"><label>Name</label><input name="name" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>" required></div>
        <div class="span-6"><label>Category</label><input name="category" value="<?php echo htmlspecialchars($item['category'] ?? ''); ?>" placeholder="e.g. Drinks"></div>
        <div class="span-12"><label>Description</label><textarea name="description"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea></div>
        <div class="span-3"><label>Price</label><input type="number" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($item['price'] ?? ''); ?>" required></div>
        <div class="span-3"><label>Available?</label>
          <select name="is_available">
            <option value="1" <?php echo ((int)($item['is_available'] ?? 1)===1)?'selected':''; ?>>Yes</option>
            <option value="0" <?php echo ((int)($item['is_available'] ?? 1)===0)?'selected':''; ?>>No</option>
          </select>
        </div>
        <div class="span-12" style="display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn" type="submit">Save</button>
          <a class="btn secondary" href="menu_manage.php">Clear</a>
          <a class="btn secondary" href="menu_search.php">Back to Menu</a>
        </div>
      </form>
    </div></div>

    <div class="col-12"><div class="card">
      <h3>All Items</h3>
      <table class="table">
        <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Available</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
          <?php foreach($items as $it): ?>
            <tr>
              <td><?php echo htmlspecialchars($it['name']); ?></td>
              <td><?php echo htmlspecialchars($it['category'] ?? ''); ?></td>
              <td><b><?php echo number_format((float)$it['price'],2); ?></b></td>
              <td><?php echo ((int)$it['is_available']===1)?'Yes':'No'; ?></td>
              <td class="no-print" style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn secondary" href="menu_manage.php?action=edit&id=<?php echo (int)$it['menu_item_id']; ?>">Edit</a>
                <a class="btn danger" href="menu_manage.php?action=delete&id=<?php echo (int)$it['menu_item_id']; ?>" onclick="return confirm('Delete this item?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$items): ?><tr><td colspan="5" class="small">No items yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div>
<script src="../assets/app.js"></script>
</body></html>
