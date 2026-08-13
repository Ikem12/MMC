<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: company_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM company_cases WHERE id = ?');
$stmt->execute([$id]); $c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: company_list.php'); exit; }
function row($l,$v){if(!$v)return;echo '<tr><th>'.htmlspecialchars($l).'</th><td>'.nl2br(htmlspecialchars($v)).'</td></tr>';}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"/><title>Company: <?php echo htmlspecialchars($c['client_name']);?></title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}.topbar .brand{font-size:1.1rem;font-weight:bold}.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}.hero{background:linear-gradient(135deg,#1abc9c,#16a085);color:#fff;padding:28px 40px;margin-bottom:30px}.hero h1{font-size:1.4rem}.hero p{font-size:0.85rem;opacity:0.85;margin-top:4px}.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:20px}.st{font-size:0.95rem;font-weight:bold;color:#fff;background:#1abc9c;padding:10px 16px;border-radius:6px;margin-bottom:14px}.st.navy{background:#1a3c5e}.st.red{background:#c0392b}.st.orange{background:#e67e22}.st.blue{background:#2980b9}.st.purple{background:#8e44ad}table{width:100%;border-collapse:collapse}th{width:35%;padding:9px 12px;text-align:left;font-size:0.8rem;color:#555;background:#f8f9fa;border-bottom:1px solid #eee;font-weight:bold}td{padding:9px 12px;font-size:0.85rem;border-bottom:1px solid #f0f0f0}.btn-row{display:flex;gap:12px;margin-bottom:20px}.btn{padding:9px 20px;border-radius:4px;text-decoration:none;font-size:0.88rem;font-weight:bold;border:none;cursor:pointer;display:inline-block}.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}.btn-print{background:#27ae60;color:#fff}</style></head>
<body>
<div class="topbar"><div class="brand">&#9878;&#65039; AEP Legal Platform</div><div><a href="dashboard.php">&#127968; Dashboard</a><a href="company_list.php">&#127970; Company</a><a href="logout.php">&#128682; Logout</a></div></div>
<div class="hero"><h1>&#127970; <?php echo htmlspecialchars($c['client_name']);?> &mdash; <?php echo htmlspecialchars($c['company_name']?:'Unknown');?></h1><p>Ref: <?php echo htmlspecialchars($c['case_reference']?:'N/A');?> | <?php echo htmlspecialchars($c['case_type']?:'N/A');?> | Status: <?php echo ucfirst($c['status']);?></p></div>
<div class="container">
<div class="btn-row"><a href="company_list.php" class="btn btn-outline">&#8592; Back</a><a href="company_print.php?id=<?php echo $c['id'];?>" class="btn btn-print">&#128424; Print</a></div>
<div class="card"><div class="st navy">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']);row('Case Reference',$c['case_reference']);row('Status',ucfirst($c['status']));row('Lawyer',$c['lawyer_name']);row('Law Firm',$c['law_firm']);?></table></div>
<div class="card"><div class="st">2. CLIENT DETAILS</div><table><?php row('Full Name',$c['client_name']);row('Email',$c['client_email']);row('Phone',$c['client_phone']);row('Address',$c['client_address']);?></table></div>
<div class="card"><div class="st red">3. COMPANY DETAILS</div><table><?php row('Company Name',$c['company_name']);row('Company Number',$c['company_number']);row('Contact',$c['company_contact']);row('Address',$c['company_address']);?></table></div>
<div class="card"><div class="st orange">4. DISPUTE DETAILS</div><table><?php row('Dispute Date',$c['dispute_date']);row('Shareholder Dispute',$c['shareholder_dispute']);row('Director Dispute',$c['director_dispute']);row('Insolvency',$c['insolvency']);row('Description',$c['dispute_description']);?></table></div>
<div class="card"><div class="st blue">5. COURT &amp; REGULATORY</div><table><?php row('Court Issued',$c['court_issued']);row('Court Name',$c['court_name']);row('Court Date',$c['court_date']);row('Regulatory Body',$c['regulatory_body']);row('Regulatory Ref',$c['regulatory_reference']);?></table></div>
<div class="card"><div class="st purple">6. LEGAL SUBMISSIONS</div><table><?php row('Legal Basis',$c['legal_basis']);row('Evidence',$c['evidence_available']);row('Representations',$c['representations']);row('Settlement Offers',$c['settlement_offers']);row('Without Prejudice',$c['without_prejudice']);row('Outcome',$c['outcome']);?></table></div>
</div></body></html>