<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS latin_maxims (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  maxim TEXT NOT NULL, meaning TEXT NOT NULL, category TEXT DEFAULT 'General',
  details TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$search   = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';

$sql    = "SELECT * FROM latin_maxims WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (maxim LIKE ? OR meaning LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$maxims = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cats = $pdo->query("SELECT DISTINCT category FROM latin_maxims WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Latin Maxims &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#2c3e50,#4a6a8a);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1200px;margin:0 auto;padding:0 28px 40px}
.toolbar{display:flex;gap:12px;margin-bottom:20px;align-items:center;flex-wrap:wrap}
.toolbar input,.toolbar select{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem}
.toolbar input{flex:1;min-width:200px}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.88rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-green{background:#27ae60;color:#fff}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.07)}
th{background:#2c3e50;color:#fff;padding:12px 16px;text-align:left;font-size:0.82rem}
td{padding:11px 16px;border-bottom:1px solid #f0f0f0;font-size:0.85rem}
tr:hover td{background:#f9f9f9}
.badge-cat{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:bold;background:#e8eef5;color:#1a3c5e}
.action-links a{margin-right:8px;text-decoration:none;font-size:0.8rem;font-weight:bold}
.link-view{color:#1a3c5e}
.link-print{color:#27ae60}
.empty{text-align:center;padding:40px;color:#888}
em{color:#555}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="latin_create.php">+ Add Maxim</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#9878;&#65039; Latin Maxims &amp; Legal Principles</h1>
  <p>Browse and search the legal maxims library.</p>
</div>
<div class="container">
  <div class="toolbar">
    <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap">
      <input type="text" name="search" placeholder="Search maxim or meaning..." value="<?php echo htmlspecialchars($search); ?>"/>
      <select name="category">
        <option value="">All Categories</option>
        <?php foreach ($cats as $cat): ?>
          <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">&#128269; Search</button>
      <a href="latin_list.php" class="btn btn-primary">&#10006; Clear</a>
    </form>
    <a href="latin_create.php" class="btn btn-green">+ Add Maxim</a>
  </div>

  <?php if (empty($maxims)): ?>
    <div class="empty">&#9878;&#65039; No Latin maxims found. <a href="latin_create.php">Add the first one!</a></div>
  <?php else: ?>
  <table>
    <thead>
      <tr><th>#</th><th>Maxim</th><th>Meaning</th><th>Category</th><th>Added</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($maxims as $i => $m): ?>
      <tr>
        <td><?php echo $m['id']; ?></td>
        <td><em><?php echo htmlspecialchars($m['maxim']); ?></em></td>
        <td><?php echo htmlspecialchars($m['meaning']); ?></td>
        <td><span class="badge-cat"><?php echo htmlspecialchars($m['category'] ?? 'General'); ?></span></td>
        <td><?php echo substr($m['created_at'] ?? '', 0, 10); ?></td>
        <td class="action-links">
          <a href="latin_maxims_view.php?id=<?php echo $m['id']; ?>" class="link-view">&#128065; View</a>
          <a href="latin_print.php?id=<?php echo $m['id']; ?>" class="link-print">&#128424; Print</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
</body>
</html>
