<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$sql = "SELECT * FROM admin_law_cases WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (client_name LIKE ? OR case_reference LIKE ? OR public_body_name LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Administrative Law Cases &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#6c3483,#9b59b6);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.container{max-width:1200px;margin:0 auto;padding:0 28px 40px}
.toolbar{display:flex;gap:12px;margin-bottom:20px;align-items:center;flex-wrap:wrap}
.toolbar input,.toolbar select{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem}
.toolbar input{flex:1;min-width:200px}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.88rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-purple{background:#6c3483;color:#fff}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:bold}
.badge-draft{background:#ecf0f1;color:#7f8c8d}
.badge-active{background:#d5f5e3;color:#27ae60}
.badge-court{background:#fde8d8;color:#e67e22}
.badge-tribunal{background:#e8daef;color:#6c3483}
.badge-won{background:#d5f5e3;color:#27ae60}
.badge-lost{background:#fadbd8;color:#c0392b}
.badge-settled{background:#d6eaf8;color:#2980b9}
.badge-withdrawn{background:#fef9e7;color:#f39c12}
.badge-closed{background:#ecf0f1;color:#7f8c8d}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.07)}
th{background:#1a3c5e;color:#fff;padding:12px 16px;text-align:left;font-size:0.82rem}
td{padding:11px 16px;border-bottom:1px solid #f0f0f0;font-size:0.85rem}
tr:hover td{background:#f9f9f9}
.action-links a{margin-right:8px;text-decoration:none;font-size:0.8rem;font-weight:bold}
.link-view{color:#6c3483}
.link-print{color:#27ae60}
.empty{text-align:center;padding:40px;color:#888}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="admin_law_create.php">+ New Case</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero"><h1>&#127963; Administrative Law Cases</h1></div>
<div class="container">
  <div class="toolbar">
    <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap">
      <input type="text" name="search" placeholder="Search by client, reference, public body..." value="<?php echo htmlspecialchars($search); ?>"/>
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach(['draft','active','court','tribunal','won','lost','settled','withdrawn','closed'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $status===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">&#128269; Search</button>
      <a href="admin_law_list.php" class="btn btn-primary">&#10006; Clear</a>
    </form>
    <a href="admin_law_create.php" class="btn btn-purple">+ New Admin Law Case</a>
  </div>
  <?php if (empty($cases)): ?>
    <div class="empty">&#127963; No administrative law cases found. <a href="admin_law_create.php">Create the first one!</a></div>
  <?php else: ?>
  <table>
    <thead><tr><th>#</th><th>Case Reference</th><th>Client</th><th>Public Body</th><th>Case Type</th><th>Status</th><th>JR Hearing</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($cases as $c): ?>
      <tr>
        <td><?php echo $c['id']; ?></td>
        <td><?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?></td>
        <td><strong><?php echo htmlspecialchars($c['client_name']); ?></strong></td>
        <td><?php echo htmlspecialchars($c['public_body_name'] ?: 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($c['case_type'] ?: 'N/A'); ?></td>
        <td><span class="badge badge-<?php echo htmlspecialchars(); ?>"><?php echo ucfirst(htmlspecialchars()); ?></span></td>
        <td><?php echo $c['jr_hearing_date'] ? date('d/m/Y', strtotime($c['jr_hearing_date'])) : 'N/A'; ?></td>
        <td class="action-links">
          <a href="admin_law_view.php?id=<?php echo $c['id']; ?>" class="link-view">&#128065; View</a>
          <a href="admin_law_print.php?id=<?php echo $c['id']; ?>" class="link-print">&#128424; Print</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
</body>
</html>