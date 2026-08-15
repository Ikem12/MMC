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

$total    = $pdo->query("SELECT COUNT(*) FROM property_cases")->fetchColumn();
$landlord = $pdo->query("SELECT COUNT(*) FROM property_cases WHERE case_type='Landlord Dispute'")->fetchColumn();
$tenant   = $pdo->query("SELECT COUNT(*) FROM property_cases WHERE case_type='Tenant Dispute'")->fetchColumn();
$lodger   = $pdo->query("SELECT COUNT(*) FROM property_cases WHERE case_type='Lodger Agreement'")->fetchColumn();
$commercial = $pdo->query("SELECT COUNT(*) FROM property_cases WHERE case_type='Commercial Lease'")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Property Law &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#16a085,#1abc9c);color:#fff;padding:36px 40px;margin-bottom:30px}
.hero h1{font-size:1.6rem}
.hero p{font-size:0.92rem;opacity:0.88;margin-top:6px}
.container{max-width:1100px;margin:0 auto;padding:0 28px 40px}
.stats{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:30px}
.stat{background:#fff;border-radius:10px;padding:20px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.07);border-top:4px solid #16a085}
.stat.landlord{border-top-color:#e67e22}
.stat.tenant{border-top-color:#3498db}
.stat.lodger{border-top-color:#8e44ad}
.stat.commercial{border-top-color:#c0392b}
.stat .num{font-size:2rem;font-weight:bold;color:#1a3c5e}
.stat .lbl{font-size:0.75rem;color:#888;margin-top:4px}
.cards{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:30px}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);border-left:6px solid #16a085}
.card.landlord{border-left-color:#e67e22}
.card.tenant{border-left-color:#3498db}
.card.lodger{border-left-color:#8e44ad}
.card.commercial{border-left-color:#c0392b}
.card h3{font-size:1rem;color:#1a3c5e;margin-bottom:8px}
.card p{font-size:0.82rem;color:#666;line-height:1.6;margin-bottom:14px}
.btn{padding:9px 18px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;text-decoration:none;display:inline-block;margin-right:8px}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-teal{background:#16a085;color:#fff}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
.icon{font-size:1.6rem;margin-bottom:8px}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="property_list.php">&#128203; All Cases</a>
    <a href="property_create.php">+ New Case</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#127968; Property Law Module</h1>
  <p>Manage Landlord &amp; Tenant disputes, Lodger agreements, and Commercial lease matters.</p>
</div>
<div class="container">

  <div class="stats">
    <div class="stat">
      <div class="num"><?php echo $total; ?></div>
      <div class="lbl">Total Cases</div>
    </div>
    <div class="stat landlord">
      <div class="num"><?php echo $landlord; ?></div>
      <div class="lbl">Landlord Disputes</div>
    </div>
    <div class="stat tenant">
      <div class="num"><?php echo $tenant; ?></div>
      <div class="lbl">Tenant Disputes</div>
    </div>
    <div class="stat lodger">
      <div class="num"><?php echo $lodger; ?></div>
      <div class="lbl">Lodger Agreements</div>
    </div>
    <div class="stat commercial">
      <div class="num"><?php echo $commercial; ?></div>
      <div class="lbl">Commercial Leases</div>
    </div>
  </div>

  <div class="cards">
    <div class="card landlord">
      <div class="icon">&#127968;</div>
      <h3>Landlord Dispute</h3>
      <p>Possession proceedings, rent arrears, Section 8 / Section 21 notices, property disrepair claims, and other landlord-side matters.</p>
      <a href="property_create.php?type=Landlord+Dispute" class="btn btn-primary">+ New Landlord Case</a>
      <a href="property_list.php?type=Landlord+Dispute" class="btn btn-outline">View All</a>
    </div>

    <div class="card tenant">
      <div class="icon">&#128106;</div>
      <h3>Tenant Dispute</h3>
      <p>Unlawful eviction, deposit disputes, disrepair and habitability claims, and defending possession proceedings on behalf of tenants.</p>
      <a href="property_create.php?type=Tenant+Dispute" class="btn btn-primary">+ New Tenant Case</a>
      <a href="property_list.php?type=Tenant+Dispute" class="btn btn-outline">View All</a>
    </div>

    <div class="card lodger">
      <div class="icon">&#128100;</div>
      <h3>Lodger Agreement</h3>
      <p>Licence agreements, excluded occupancy, notice to quit, lodger rights, and disputes between resident landlords and lodgers.</p>
      <a href="property_create.php?type=Lodger+Agreement" class="btn btn-primary">+ New Lodger Case</a>
      <a href="property_list.php?type=Lodger+Agreement" class="btn btn-outline">View All</a>
    </div>

    <div class="card commercial">
      <div class="icon">&#127970;</div>
      <h3>Commercial Law</h3>
      <p>Commercial lease agreements, rent reviews, break clauses, dilapidations, forfeiture, and business tenancy renewals under the Landlord and Tenant Act 1954.</p>
      <a href="property_create.php?type=Commercial+Lease" class="btn btn-primary">+ New Commercial Case</a>
      <a href="property_list.php?type=Commercial+Lease" class="btn btn-outline">View All</a>
    </div>
  </div>

  <div style="text-align:center;margin-top:10px">
    <a href="property_create.php" class="btn btn-teal" style="font-size:0.95rem;padding:12px 30px;">&#10133; Create New Property Case</a>
    <a href="property_list.php" class="btn btn-outline" style="font-size:0.95rem;padding:12px 30px;">&#128203; View All Cases</a>
  </div>

</div>
</body>
</html>
