<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS property_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_type TEXT, case_title TEXT, case_reference TEXT, status TEXT DEFAULT 'draft',
  client_name TEXT, client_email TEXT, client_phone TEXT, client_address TEXT,
  property_address TEXT, property_type TEXT, tenure TEXT,
  party_a_name TEXT, party_a_role TEXT, party_a_address TEXT, party_a_contact TEXT,
  party_b_name TEXT, party_b_role TEXT, party_b_address TEXT, party_b_contact TEXT,
  rent_amount TEXT, deposit_amount TEXT, tenancy_start TEXT, tenancy_end TEXT,
  notice_type TEXT, notice_date TEXT, notice_period TEXT,
  possession_ground TEXT, claim_details TEXT, defence TEXT,
  repairs_issues TEXT, covenant_breach TEXT,
  court_name TEXT, court_date TEXT, court_case_number TEXT,
  applicable_laws TEXT, evidence TEXT, outcome TEXT, notes TEXT,
  lawyer_name TEXT, law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

require_once __DIR__ . '/csrf.php';
csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_verify()) { die('Invalid CSRF token.'); }
    $pdo->prepare("DELETE FROM property_cases WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    header('Location: property_list.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$type   = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';

$sql    = "SELECT * FROM property_cases WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (client_name LIKE ? OR case_reference LIKE ? OR case_title LIKE ? OR party_a_name LIKE ? OR party_b_name LIKE ?)";
    $p = "%$search%";
    $params = array_merge($params, [$p,$p,$p,$p,$p]);
}
if ($type)   { $sql .= " AND case_type = ?"; $params[] = $type; }
if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Property Cases &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#16a085,#1abc9c);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.container{max-width:1200px;margin:0 auto;padding:0 28px 40px}
.toolbar{display:flex;gap:12px;margin-bottom:20px;align-items:center;flex-wrap:wrap}
.toolbar input,.toolbar select{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem}
.toolbar input{flex:1;min-width:200px}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.88rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-teal{background:#16a085;color:#fff}
.btn-sm{padding:5px 12px;font-size:0.78rem}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:bold}
.badge-draft{background:#ecf0f1;color:#7f8c8d}
.badge-active{background:#d5f5e3;color:#27ae60}
.badge-court{background:#fde8d8;color:#e67e22}
.badge-settled{background:#d6eaf8;color:#2980b9}
.badge-won{background:#d5f5e3;color:#27ae60}
.badge-lost{background:#fadbd8;color:#c0392b}
.badge-withdrawn{background:#fef9e7;color:#f39c12}
.badge-closed{background:#ecf0f1;color:#7f8c8d}
.type-landlord{background:#fde8d8;color:#e67e22}
.type-tenant{background:#d6eaf8;color:#2980b9}
.type-lodger{background:#e8daef;color:#8e44ad}
.type-commercial{background:#fadbd8;color:#c0392b}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.07)}
th{background:#16a085;color:#fff;padding:12px 16px;text-align:left;font-size:0.82rem}
td{padding:11px 16px;border-bottom:1px solid #f0f0f0;font-size:0.85rem}
tr:hover td{background:#f9f9f9}
.action-links a{margin-right:8px;text-decoration:none;font-size:0.8rem;font-weight:bold}
.link-view{color:#16a085}
.link-print{color:#27ae60}
.empty{text-align:center;padding:40px;color:#888}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="property_law.php">&#127968; Property Law</a>
    <a href="property_create.php">+ New Case</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero"><h1>&#127968; Property Law Cases</h1></div>
<div class="container">
  <div class="toolbar">
    <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap">
      <input type="text" name="search" placeholder="Search client, reference, title, parties..." value="<?php echo htmlspecialchars($search); ?>"/>
      <select name="type">
        <option value="">All Types</option>
        <?php foreach(['Landlord Dispute','Tenant Dispute','Lodger Agreement','Commercial Lease'] as $t): ?>
          <option value="<?php echo $t; ?>" <?php echo $type===$t?'selected':''; ?>><?php echo $t; ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach(['draft','active','court','settled','won','lost','withdrawn','closed'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $status===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">&#128269; Search</button>
      <a href="property_list.php" class="btn btn-primary">&#10006; Clear</a>
    </form>
    <a href="property_create.php" class="btn btn-teal">+ New Property Case</a>
  </div>

  <?php if (empty($cases)): ?>
    <div class="empty">&#127968; No property cases found. <a href="property_create.php">Create the first one!</a></div>
  <?php else: ?>
  <table>
    <thead>
      <tr><th>#</th><th>Reference</th><th>Case Type</th><th>Case Title</th><th>Client</th><th>Property Address</th><th>Status</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($cases as $c): ?>
      <?php
        $typeKey = '';
        if ($c['case_type'] === 'Landlord Dispute') $typeKey = 'landlord';
        elseif ($c['case_type'] === 'Tenant Dispute') $typeKey = 'tenant';
        elseif ($c['case_type'] === 'Lodger Agreement') $typeKey = 'lodger';
        elseif ($c['case_type'] === 'Commercial Lease') $typeKey = 'commercial';
      ?>
      <tr>
        <td><?php echo $c['id']; ?></td>
        <td><?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?></td>
        <td><span class="badge type-<?php echo $typeKey; ?>"><?php echo htmlspecialchars($c['case_type'] ?: 'N/A'); ?></span></td>
        <td><strong><?php echo htmlspecialchars($c['case_title'] ?: 'N/A'); ?></strong></td>
        <td><?php echo htmlspecialchars($c['client_name'] ?: 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($c['property_address'] ?: '—'); ?></td>
        <td><span class="badge badge-<?php echo htmlspecialchars($c['status'] ?? 'draft'); ?>"><?php echo ucfirst(htmlspecialchars($c['status'] ?? 'draft')); ?></span></td>
        <td><?php echo substr($c['created_at'] ?? '', 0, 10); ?></td>
        <td class="action-links">
          <a href="property_view.php?id=<?php echo $c['id']; ?>" class="link-view">&#128065; View</a>
          <a href="property_print.php?id=<?php echo $c['id']; ?>" class="link-print">&#128424; Print</a>
          <form method="POST" style="display:inline" onsubmit="return confirm('Delete this case?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>"/>
            <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>"/>
            <button type="submit" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:0.8rem;font-weight:bold;">&#128465; Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
</body>
</html>
