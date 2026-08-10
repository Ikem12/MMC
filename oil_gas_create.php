<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO oil_gas_cases (
            case_reference, status, case_type,
            client_name, client_address, client_email, client_phone,
            opponent_name, opponent_address, opponent_contact,
            licence_number, licence_type, licence_area,
            contract_date, contract_value, contract_description,
            dispute_date, dispute_description, regulatory_body,
            court_issued, court_name, court_date,
            legal_basis, evidence_available, representations,
            settlement_offers, without_prejudice, outcome,
            lawyer_name, law_firm
        ) VALUES (
            :case_reference, :status, :case_type,
            :client_name, :client_address, :client_email, :client_phone,
            :opponent_name, :opponent_address, :opponent_contact,
            :licence_number, :licence_type, :licence_area,
            :contract_date, :contract_value, :contract_description,
            :dispute_date, :dispute_description, :regulatory_body,
            :court_issued, :court_name, :court_date,
            :legal_basis, :evidence_available, :representations,
            :settlement_offers, :without_prejudice, :outcome,
            :lawyer_name, :law_firm
        )");
        $stmt->execute([
            ':case_reference'      => $_POST['case_reference'],
            ':status'              => $_POST['status'],
            ':case_type'           => $_POST['case_type'],
            ':client_name'         => $_POST['client_name'],
            ':client_address'      => $_POST['client_address'],
            ':client_email'        => $_POST['client_email'],
            ':client_phone'        => $_POST['client_phone'],
            ':opponent_name'       => $_POST['opponent_name'],
            ':opponent_address'    => $_POST['opponent_address'],
            ':opponent_contact'    => $_POST['opponent_contact'],
            ':licence_number'      => $_POST['licence_number'],
            ':licence_type'        => $_POST['licence_type'],
            ':licence_area'        => $_POST['licence_area'],
            ':contract_date'       => $_POST['contract_date'],
            ':contract_value'      => $_POST['contract_value'],
            ':contract_description'=> $_POST['contract_description'],
            ':dispute_date'        => $_POST['dispute_date'],
            ':dispute_description' => $_POST['dispute_description'],
            ':regulatory_body'     => $_POST['regulatory_body'],
            ':court_issued'        => $_POST['court_issued'],
            ':court_name'          => $_POST['court_name'],
            ':court_date'          => $_POST['court_date'],
            ':legal_basis'         => $_POST['legal_basis'],
            ':evidence_available'  => $_POST['evidence_available'],
            ':representations'     => $_POST['representations'],
            ':settlement_offers'   => $_POST['settlement_offers'],
            ':without_prejudice'   => $_POST['without_prejudice'],
            ':outcome'             => $_POST['outcome'],
            ':lawyer_name'         => $_POST['lawyer_name'],
            ':law_firm'            => $_POST['law_firm'],
        ]);
        header('Location: oil_gas_view.php?id=' . $pdo->lastInsertId());
        exit;
    } catch (Exception $e) {
        $error = 'Error saving case: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>New Oil &amp; Gas Case &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#16a085,#1abc9c);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem;background:#f8d7da;color:#721c24}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#16a085;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.navy{background:#1a3c5e}
.section-title.red{background:#c0392b}
.section-title.orange{background:#e67e22}
.section-title.blue{background:#2980b9}
.section-title.purple{background:#8e44ad}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
label{font-size:0.8rem;font-weight:bold;color:#555}
input,select,textarea{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem;width:100%}
textarea{resize:vertical;min-height:80px}
.btn-row{display:flex;gap:12px;justify-content:flex-end;margin-top:10px}
.btn{padding:10px 24px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#16a085;color:#fff}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div><a href="dashboard.php">&#127968; Dashboard</a><a href="oil_gas_list.php">&#128167; Oil &amp; Gas</a><a href="logout.php">&#128682; Logout</a></div>
</div>
<div class="hero"><h1>&#128167; New Oil &amp; Gas Law Case</h1><p>Complete all sections to create a new oil &amp; gas law case record.</p></div>
<div class="container">
  <?php if ($error): ?><div class="alert">&#10060; <?php echo $error; ?></div><?php endif; ?>
  <form method="POST">
    <div class="card"><div class="section-title navy">1. CASE DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Case Type</label><select name="case_type"><option value="">-- Select --</option><option>Licence Dispute</option><option>Exploration Contract</option><option>Production Sharing Agreement</option><option>Pipeline Dispute</option><option>Environmental Liability</option><option>Regulatory Breach</option><option>Joint Venture Dispute</option><option>Farm-in / Farm-out</option><option>Decommissioning</option><option>Other</option></select></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="court">At Court</option><option value="arbitration">Arbitration</option><option value="settled">Settled</option><option value="won">Won</option><option value="lost">Lost</option><option value="closed">Closed</option></select></div>
        <div class="form-group"><label>Case Reference</label><input type="text" name="case_reference" placeholder="e.g. AEP/OIL/2026/001"/></div>
        <div class="form-group"><label>Lawyer Name</label><input type="text" name="lawyer_name"/></div>
        <div class="form-group"><label>Law Firm</label><input type="text" name="law_firm" value="AEP Legal Consultancy"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title">2. CLIENT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Full Name / Company</label><input type="text" name="client_name"/></div>
        <div class="form-group"><label>Email</label><input type="email" name="client_email"/></div>
        <div class="form-group"><label>Phone</label><input type="text" name="client_phone"/></div>
        <div class="form-group full"><label>Address</label><textarea name="client_address"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title red">3. OPPONENT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Opponent / Company</label><input type="text" name="opponent_name"/></div>
        <div class="form-group"><label>Contact Person</label><input type="text" name="opponent_contact"/></div>
        <div class="form-group full"><label>Address</label><textarea name="opponent_address"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title orange">4. LICENCE &amp; CONTRACT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Licence Number</label><input type="text" name="licence_number"/></div>
        <div class="form-group"><label>Licence Type</label><input type="text" name="licence_type" placeholder="e.g. PEDL, Production Licence"/></div>
        <div class="form-group full"><label>Licence Area / Block</label><input type="text" name="licence_area" placeholder="e.g. Block 22/30, North Sea"/></div>
        <div class="form-group"><label>Contract Date</label><input type="date" name="contract_date"/></div>
        <div class="form-group"><label>Contract Value</label><input type="text" name="contract_value" placeholder="e.g. $10,000,000"/></div>
        <div class="form-group full"><label>Contract Description</label><textarea name="contract_description"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title blue">5. DISPUTE DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Dispute Date</label><input type="date" name="dispute_date"/></div>
        <div class="form-group"><label>Regulatory Body</label><input type="text" name="regulatory_body" placeholder="e.g. NSTA, Environment Agency"/></div>
        <div class="form-group full"><label>Dispute Description</label><textarea name="dispute_description"></textarea></div>
        <div class="form-group"><label>Court / Arbitration Issued?</label><select name="court_issued"><option value="">-- Select --</option><option>Yes</option><option>No</option><option>Pending</option></select></div>
        <div class="form-group"><label>Court / Arbitration Name</label><input type="text" name="court_name"/></div>
        <div class="form-group"><label>Hearing Date</label><input type="date" name="court_date"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title purple">6. LEGAL SUBMISSIONS &amp; OUTCOME</div>
      <div class="form-grid">
        <div class="form-group full"><label>Legal Basis</label><textarea name="legal_basis" placeholder="e.g. Petroleum Act 1998, Energy Act 2016..."></textarea></div>
        <div class="form-group full"><label>Evidence Available</label><textarea name="evidence_available"></textarea></div>
        <div class="form-group full"><label>Representations</label><textarea name="representations"></textarea></div>
        <div class="form-group full"><label>Settlement Offers</label><textarea name="settlement_offers"></textarea></div>
        <div class="form-group full"><label>Without Prejudice</label><textarea name="without_prejudice"></textarea></div>
        <div class="form-group full"><label>Outcome / Notes</label><textarea name="outcome"></textarea></div>
      </div>
    </div>
    <div class="btn-row">
      <a href="oil_gas_list.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">&#128190; Save Oil &amp; Gas Case</button>
    </div>
  </form>
</div>
</body>
</html>