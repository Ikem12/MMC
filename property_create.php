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

$error   = '';
$success = '';

require_once __DIR__ . '/csrf.php';

// Pre-fill case type from query string if coming from property_law.php
$preType = $_GET['type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { die('Invalid CSRF token.'); }

    try {
        $stmt = $pdo->prepare("INSERT INTO property_cases (
            case_type, case_title, case_reference, status,
            client_name, client_email, client_phone, client_address,
            property_address, property_type, tenure,
            party_a_name, party_a_role, party_a_address, party_a_contact,
            party_b_name, party_b_role, party_b_address, party_b_contact,
            rent_amount, deposit_amount, tenancy_start, tenancy_end,
            notice_type, notice_date, notice_period,
            possession_ground, claim_details, defence,
            repairs_issues, covenant_breach,
            court_name, court_date, court_case_number,
            applicable_laws, evidence, outcome, notes,
            lawyer_name, law_firm, created_at
        ) VALUES (
            :case_type,:case_title,:case_reference,:status,
            :client_name,:client_email,:client_phone,:client_address,
            :property_address,:property_type,:tenure,
            :party_a_name,:party_a_role,:party_a_address,:party_a_contact,
            :party_b_name,:party_b_role,:party_b_address,:party_b_contact,
            :rent_amount,:deposit_amount,:tenancy_start,:tenancy_end,
            :notice_type,:notice_date,:notice_period,
            :possession_ground,:claim_details,:defence,
            :repairs_issues,:covenant_breach,
            :court_name,:court_date,:court_case_number,
            :applicable_laws,:evidence,:outcome,:notes,
            :lawyer_name,:law_firm,datetime('now')
        )");
        $stmt->execute([
            ':case_type'        => $_POST['case_type'],
            ':case_title'       => $_POST['case_title'],
            ':case_reference'   => $_POST['case_reference'],
            ':status'           => $_POST['status'],
            ':client_name'      => $_POST['client_name'],
            ':client_email'     => $_POST['client_email'],
            ':client_phone'     => $_POST['client_phone'],
            ':client_address'   => $_POST['client_address'],
            ':property_address' => $_POST['property_address'],
            ':property_type'    => $_POST['property_type'],
            ':tenure'           => $_POST['tenure'],
            ':party_a_name'     => $_POST['party_a_name'],
            ':party_a_role'     => $_POST['party_a_role'],
            ':party_a_address'  => $_POST['party_a_address'],
            ':party_a_contact'  => $_POST['party_a_contact'],
            ':party_b_name'     => $_POST['party_b_name'],
            ':party_b_role'     => $_POST['party_b_role'],
            ':party_b_address'  => $_POST['party_b_address'],
            ':party_b_contact'  => $_POST['party_b_contact'],
            ':rent_amount'      => $_POST['rent_amount'],
            ':deposit_amount'   => $_POST['deposit_amount'],
            ':tenancy_start'    => $_POST['tenancy_start'],
            ':tenancy_end'      => $_POST['tenancy_end'],
            ':notice_type'      => $_POST['notice_type'],
            ':notice_date'      => $_POST['notice_date'],
            ':notice_period'    => $_POST['notice_period'],
            ':possession_ground'=> $_POST['possession_ground'],
            ':claim_details'    => $_POST['claim_details'],
            ':defence'          => $_POST['defence'],
            ':repairs_issues'   => $_POST['repairs_issues'],
            ':covenant_breach'  => $_POST['covenant_breach'],
            ':court_name'       => $_POST['court_name'],
            ':court_date'       => $_POST['court_date'],
            ':court_case_number'=> $_POST['court_case_number'],
            ':applicable_laws'  => $_POST['applicable_laws'],
            ':evidence'         => $_POST['evidence'],
            ':outcome'          => $_POST['outcome'],
            ':notes'            => $_POST['notes'],
            ':lawyer_name'      => $_POST['lawyer_name'],
            ':law_firm'         => $_POST['law_firm'],
        ]);
        $newId = $pdo->lastInsertId();
        header('Location: property_view.php?id=' . $newId);
        exit;
    } catch (Exception $e) {
        $error = 'Error saving case: ' . $e->getMessage();
    }
}

$token = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>New Property Case &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#16a085,#1abc9c);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.4rem}
.container{max-width:900px;margin:0 auto;padding:0 28px 40px}
.card{background:#fff;border-radius:10px;padding:26px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:22px}
.section-title{font-size:0.92rem;font-weight:bold;color:#fff;background:#16a085;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.navy{background:#1a3c5e}
.section-title.orange{background:#e67e22}
.section-title.blue{background:#2980b9}
.section-title.purple{background:#8e44ad}
.section-title.red{background:#c0392b}
.section-title.gray{background:#7f8c8d}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:16px}
label{display:block;font-size:0.82rem;font-weight:bold;color:#555;margin-bottom:5px}
input,select,textarea{width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem;font-family:inherit}
textarea{resize:vertical;min-height:80px}
.btn{padding:10px 24px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-teal{background:#16a085;color:#fff}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
.alert-error{background:#fde8d8;color:#c0392b;padding:12px 16px;border-radius:4px;margin-bottom:16px;font-size:0.9rem}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="property_list.php">&#128203; Property Cases</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero"><h1>&#127968; New Property Law Case</h1></div>
<div class="container">
  <?php if ($error): ?><div class="alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token); ?>"/>

    <div class="card">
      <div class="section-title navy">1. CASE OVERVIEW</div>
      <div class="grid2">
        <div class="form-group">
          <label>Case Type *</label>
          <select name="case_type" required>
            <option value="">Select type...</option>
            <?php foreach(['Landlord Dispute','Tenant Dispute','Lodger Agreement','Commercial Lease'] as $t): ?>
              <option value="<?php echo $t; ?>" <?php echo ($preType===$t||($_POST['case_type']??'')===$t)?'selected':''; ?>><?php echo $t; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <?php foreach(['draft','active','court','settled','won','lost','withdrawn','closed'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo ($_POST['status']??'draft')===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Case Title</label>
          <input type="text" name="case_title" value="<?php echo htmlspecialchars($_POST['case_title']??''); ?>" placeholder="e.g. Smith v Jones"/>
        </div>
        <div class="form-group">
          <label>Case Reference</label>
          <input type="text" name="case_reference" value="<?php echo htmlspecialchars($_POST['case_reference']??''); ?>" placeholder="e.g. PROP/2025/001"/>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="section-title">2. CLIENT DETAILS</div>
      <div class="grid2">
        <div class="form-group"><label>Client Name *</label><input type="text" name="client_name" required value="<?php echo htmlspecialchars($_POST['client_name']??''); ?>"/></div>
        <div class="form-group"><label>Client Email</label><input type="email" name="client_email" value="<?php echo htmlspecialchars($_POST['client_email']??''); ?>"/></div>
        <div class="form-group"><label>Client Phone</label><input type="text" name="client_phone" value="<?php echo htmlspecialchars($_POST['client_phone']??''); ?>"/></div>
        <div class="form-group"><label>Client Address</label><input type="text" name="client_address" value="<?php echo htmlspecialchars($_POST['client_address']??''); ?>"/></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title orange">3. PROPERTY DETAILS</div>
      <div class="grid2">
        <div class="form-group"><label>Property Address</label><input type="text" name="property_address" value="<?php echo htmlspecialchars($_POST['property_address']??''); ?>" placeholder="Full address of the property"/></div>
        <div class="form-group">
          <label>Property Type</label>
          <select name="property_type">
            <option value="">Select...</option>
            <?php foreach(['House','Flat / Apartment','HMO','Commercial Premises','Land','Other'] as $pt): ?>
              <option value="<?php echo $pt; ?>" <?php echo ($_POST['property_type']??'')===$pt?'selected':''; ?>><?php echo $pt; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Tenure</label>
          <select name="tenure">
            <option value="">Select...</option>
            <?php foreach(['Freehold','Leasehold','Commonhold','Licence'] as $ten): ?>
              <option value="<?php echo $ten; ?>" <?php echo ($_POST['tenure']??'')===$ten?'selected':''; ?>><?php echo $ten; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="section-title blue">4. PARTY A (e.g. Landlord / Licensor)</div>
      <div class="grid2">
        <div class="form-group"><label>Name</label><input type="text" name="party_a_name" value="<?php echo htmlspecialchars($_POST['party_a_name']??''); ?>"/></div>
        <div class="form-group"><label>Role</label><input type="text" name="party_a_role" value="<?php echo htmlspecialchars($_POST['party_a_role']??''); ?>" placeholder="e.g. Landlord, Licensor, Freeholder"/></div>
        <div class="form-group"><label>Address</label><input type="text" name="party_a_address" value="<?php echo htmlspecialchars($_POST['party_a_address']??''); ?>"/></div>
        <div class="form-group"><label>Contact</label><input type="text" name="party_a_contact" value="<?php echo htmlspecialchars($_POST['party_a_contact']??''); ?>"/></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title purple">5. PARTY B (e.g. Tenant / Licensee / Lodger)</div>
      <div class="grid2">
        <div class="form-group"><label>Name</label><input type="text" name="party_b_name" value="<?php echo htmlspecialchars($_POST['party_b_name']??''); ?>"/></div>
        <div class="form-group"><label>Role</label><input type="text" name="party_b_role" value="<?php echo htmlspecialchars($_POST['party_b_role']??''); ?>" placeholder="e.g. Tenant, Licensee, Lodger, Lessee"/></div>
        <div class="form-group"><label>Address</label><input type="text" name="party_b_address" value="<?php echo htmlspecialchars($_POST['party_b_address']??''); ?>"/></div>
        <div class="form-group"><label>Contact</label><input type="text" name="party_b_contact" value="<?php echo htmlspecialchars($_POST['party_b_contact']??''); ?>"/></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title">6. TENANCY / LEASE TERMS</div>
      <div class="grid2">
        <div class="form-group"><label>Rent Amount</label><input type="text" name="rent_amount" value="<?php echo htmlspecialchars($_POST['rent_amount']??''); ?>" placeholder="e.g. £1,200 pcm"/></div>
        <div class="form-group"><label>Deposit Amount</label><input type="text" name="deposit_amount" value="<?php echo htmlspecialchars($_POST['deposit_amount']??''); ?>" placeholder="e.g. £1,400"/></div>
        <div class="form-group"><label>Tenancy Start</label><input type="date" name="tenancy_start" value="<?php echo htmlspecialchars($_POST['tenancy_start']??''); ?>"/></div>
        <div class="form-group"><label>Tenancy End</label><input type="date" name="tenancy_end" value="<?php echo htmlspecialchars($_POST['tenancy_end']??''); ?>"/></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title red">7. NOTICE &amp; PROCEEDINGS</div>
      <div class="grid2">
        <div class="form-group">
          <label>Notice Type</label>
          <select name="notice_type">
            <option value="">N/A</option>
            <?php foreach(['Section 8 Notice','Section 21 Notice','Notice to Quit','Notice of Seeking Possession','Forfeiture Notice','Break Notice','Other'] as $nt): ?>
              <option value="<?php echo $nt; ?>" <?php echo ($_POST['notice_type']??'')===$nt?'selected':''; ?>><?php echo $nt; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Notice Date</label><input type="date" name="notice_date" value="<?php echo htmlspecialchars($_POST['notice_date']??''); ?>"/></div>
        <div class="form-group"><label>Notice Period</label><input type="text" name="notice_period" value="<?php echo htmlspecialchars($_POST['notice_period']??''); ?>" placeholder="e.g. 14 days, 2 months"/></div>
        <div class="form-group"><label>Ground for Possession</label><input type="text" name="possession_ground" value="<?php echo htmlspecialchars($_POST['possession_ground']??''); ?>" placeholder="e.g. Ground 8 – Rent Arrears"/></div>
      </div>
      <div class="form-group"><label>Claim Details</label><textarea name="claim_details"><?php echo htmlspecialchars($_POST['claim_details']??''); ?></textarea></div>
      <div class="form-group"><label>Defence / Response</label><textarea name="defence"><?php echo htmlspecialchars($_POST['defence']??''); ?></textarea></div>
      <div class="grid2">
        <div class="form-group"><label>Repairs / Disrepair Issues</label><textarea name="repairs_issues"><?php echo htmlspecialchars($_POST['repairs_issues']??''); ?></textarea></div>
        <div class="form-group"><label>Covenant Breach</label><textarea name="covenant_breach"><?php echo htmlspecialchars($_POST['covenant_breach']??''); ?></textarea></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title gray">8. COURT &amp; LEGAL</div>
      <div class="grid2">
        <div class="form-group"><label>Court Name</label><input type="text" name="court_name" value="<?php echo htmlspecialchars($_POST['court_name']??''); ?>"/></div>
        <div class="form-group"><label>Court Date</label><input type="date" name="court_date" value="<?php echo htmlspecialchars($_POST['court_date']??''); ?>"/></div>
        <div class="form-group"><label>Court Case Number</label><input type="text" name="court_case_number" value="<?php echo htmlspecialchars($_POST['court_case_number']??''); ?>"/></div>
      </div>
      <div class="form-group"><label>Applicable Laws / Statutes</label><textarea name="applicable_laws" placeholder="e.g. Housing Act 1988, Landlord and Tenant Act 1985, Defective Premises Act 1972..."><?php echo htmlspecialchars($_POST['applicable_laws']??''); ?></textarea></div>
      <div class="form-group"><label>Evidence Available</label><textarea name="evidence"><?php echo htmlspecialchars($_POST['evidence']??''); ?></textarea></div>
      <div class="form-group"><label>Outcome / Result</label><textarea name="outcome"><?php echo htmlspecialchars($_POST['outcome']??''); ?></textarea></div>
      <div class="form-group"><label>Notes</label><textarea name="notes"><?php echo htmlspecialchars($_POST['notes']??''); ?></textarea></div>
      <div class="grid2">
        <div class="form-group"><label>Lawyer Name</label><input type="text" name="lawyer_name" value="<?php echo htmlspecialchars($_POST['lawyer_name']??''); ?>"/></div>
        <div class="form-group"><label>Law Firm</label><input type="text" name="law_firm" value="<?php echo htmlspecialchars($_POST['law_firm']??''); ?>"/></div>
      </div>
    </div>

    <div style="display:flex;gap:12px">
      <button type="submit" class="btn btn-teal">&#128190; Save Case</button>
      <a href="property_list.php" class="btn btn-outline">&#10006; Cancel</a>
    </div>
  </form>
</div>
</body>
</html>
