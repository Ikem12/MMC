<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO tort_cases (
            case_reference, status, case_type,
            claimant_name, claimant_dob, claimant_address, claimant_email, claimant_phone,
            defendant_name, defendant_address, defendant_contact,
            incident_date, incident_location, incident_description,
            injury_description, medical_treatment, prognosis,
            damages_claimed, special_damages, general_damages,
            court_issued, court_name, court_date,
            liability_admitted, contributory_negligence,
            legal_basis, evidence_available, representations,
            settlement_offers, without_prejudice, outcome,
            lawyer_name, law_firm
        ) VALUES (
            :case_reference, :status, :case_type,
            :claimant_name, :claimant_dob, :claimant_address, :claimant_email, :claimant_phone,
            :defendant_name, :defendant_address, :defendant_contact,
            :incident_date, :incident_location, :incident_description,
            :injury_description, :medical_treatment, :prognosis,
            :damages_claimed, :special_damages, :general_damages,
            :court_issued, :court_name, :court_date,
            :liability_admitted, :contributory_negligence,
            :legal_basis, :evidence_available, :representations,
            :settlement_offers, :without_prejudice, :outcome,
            :lawyer_name, :law_firm
        )");
        $stmt->execute([
            ':case_reference'        => $_POST['case_reference'],
            ':status'                => $_POST['status'],
            ':case_type'             => $_POST['case_type'],
            ':claimant_name'         => $_POST['claimant_name'],
            ':claimant_dob'          => $_POST['claimant_dob'],
            ':claimant_address'      => $_POST['claimant_address'],
            ':claimant_email'        => $_POST['claimant_email'],
            ':claimant_phone'        => $_POST['claimant_phone'],
            ':defendant_name'        => $_POST['defendant_name'],
            ':defendant_address'     => $_POST['defendant_address'],
            ':defendant_contact'     => $_POST['defendant_contact'],
            ':incident_date'         => $_POST['incident_date'],
            ':incident_location'     => $_POST['incident_location'],
            ':incident_description'  => $_POST['incident_description'],
            ':injury_description'    => $_POST['injury_description'],
            ':medical_treatment'     => $_POST['medical_treatment'],
            ':prognosis'             => $_POST['prognosis'],
            ':damages_claimed'       => $_POST['damages_claimed'],
            ':special_damages'       => $_POST['special_damages'],
            ':general_damages'       => $_POST['general_damages'],
            ':court_issued'          => $_POST['court_issued'],
            ':court_name'            => $_POST['court_name'],
            ':court_date'            => $_POST['court_date'],
            ':liability_admitted'    => $_POST['liability_admitted'],
            ':contributory_negligence' => $_POST['contributory_negligence'],
            ':legal_basis'           => $_POST['legal_basis'],
            ':evidence_available'    => $_POST['evidence_available'],
            ':representations'       => $_POST['representations'],
            ':settlement_offers'     => $_POST['settlement_offers'],
            ':without_prejudice'     => $_POST['without_prejudice'],
            ':outcome'               => $_POST['outcome'],
            ':lawyer_name'           => $_POST['lawyer_name'],
            ':law_firm'              => $_POST['law_firm'],
        ]);
        header('Location: tort_view.php?id=' . $pdo->lastInsertId());
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
<title>New Tort Case &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#8e44ad,#9b59b6);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem;background:#f8d7da;color:#721c24}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#8e44ad;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.navy{background:#1a3c5e}
.section-title.red{background:#c0392b}
.section-title.green{background:#27ae60}
.section-title.orange{background:#e67e22}
.section-title.teal{background:#16a085}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
label{font-size:0.8rem;font-weight:bold;color:#555}
input,select,textarea{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem;width:100%}
textarea{resize:vertical;min-height:80px}
.btn-row{display:flex;gap:12px;justify-content:flex-end;margin-top:10px}
.btn{padding:10px 24px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#8e44ad;color:#fff}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div><a href="dashboard.php">&#127968; Dashboard</a><a href="tort_list.php">&#9878;&#65039; Tort</a><a href="logout.php">&#128682; Logout</a></div>
</div>
<div class="hero"><h1>&#9878;&#65039; New Tort Law Case</h1><p>Complete all sections to create a new tort law case record.</p></div>
<div class="container">
  <?php if ($error): ?><div class="alert">&#10060; <?php echo $error; ?></div><?php endif; ?>
  <form method="POST">
    <div class="card"><div class="section-title navy">1. CASE DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Case Type</label><select name="case_type"><option value="">-- Select --</option><option>Personal Injury</option><option>Negligence</option><option>Occupiers Liability</option><option>Product Liability</option><option>Defamation / Libel</option><option>Nuisance</option><option>Trespass</option><option>Employer Liability</option><option>Clinical Negligence</option><option>Road Traffic Accident</option><option>Psychiatric Injury</option><option>Other</option></select></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="court">At Court</option><option value="settled">Settled</option><option value="won">Won</option><option value="lost">Lost</option><option value="withdrawn">Withdrawn</option><option value="closed">Closed</option></select></div>
        <div class="form-group"><label>Case Reference</label><input type="text" name="case_reference" placeholder="e.g. AEP/TORT/2026/001"/></div>
        <div class="form-group"><label>Lawyer Name</label><input type="text" name="lawyer_name"/></div>
        <div class="form-group"><label>Law Firm</label><input type="text" name="law_firm" value="AEP Legal Consultancy"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title">2. CLAIMANT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Full Name</label><input type="text" name="claimant_name"/></div>
        <div class="form-group"><label>Date of Birth</label><input type="date" name="claimant_dob"/></div>
        <div class="form-group"><label>Email</label><input type="email" name="claimant_email"/></div>
        <div class="form-group"><label>Phone</label><input type="text" name="claimant_phone"/></div>
        <div class="form-group full"><label>Address</label><textarea name="claimant_address"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title red">3. DEFENDANT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Defendant Name</label><input type="text" name="defendant_name"/></div>
        <div class="form-group"><label>Contact Person</label><input type="text" name="defendant_contact"/></div>
        <div class="form-group full"><label>Defendant Address</label><textarea name="defendant_address"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title orange">4. INCIDENT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Incident Date</label><input type="date" name="incident_date"/></div>
        <div class="form-group"><label>Incident Location</label><input type="text" name="incident_location"/></div>
        <div class="form-group full"><label>Incident Description</label><textarea name="incident_description"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title">5. INJURY &amp; DAMAGES</div>
      <div class="form-grid">
        <div class="form-group full"><label>Injury Description</label><textarea name="injury_description"></textarea></div>
        <div class="form-group full"><label>Medical Treatment</label><textarea name="medical_treatment"></textarea></div>
        <div class="form-group full"><label>Prognosis</label><textarea name="prognosis"></textarea></div>
        <div class="form-group"><label>Total Damages Claimed</label><input type="text" name="damages_claimed" placeholder="e.g. £50,000"/></div>
        <div class="form-group"><label>Special Damages</label><input type="text" name="special_damages" placeholder="e.g. £15,000"/></div>
        <div class="form-group"><label>General Damages</label><input type="text" name="general_damages" placeholder="e.g. £35,000"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title green">6. LIABILITY &amp; COURT</div>
      <div class="form-grid">
        <div class="form-group"><label>Liability Admitted?</label><select name="liability_admitted"><option value="">-- Select --</option><option>Yes</option><option>No</option><option>Partial</option></select></div>
        <div class="form-group full"><label>Contributory Negligence</label><textarea name="contributory_negligence" placeholder="Any contributory negligence by claimant?"></textarea></div>
        <div class="form-group"><label>Court Proceedings Issued?</label><select name="court_issued"><option value="">-- Select --</option><option>Yes</option><option>No</option><option>Pending</option></select></div>
        <div class="form-group"><label>Court Name</label><input type="text" name="court_name" placeholder="e.g. County Court"/></div>
        <div class="form-group"><label>Court Date</label><input type="date" name="court_date"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title teal">7. LEGAL SUBMISSIONS &amp; OUTCOME</div>
      <div class="form-grid">
        <div class="form-group full"><label>Legal Basis</label><textarea name="legal_basis" placeholder="e.g. Occupiers Liability Act 1957, Donoghue v Stevenson..."></textarea></div>
        <div class="form-group full"><label>Evidence Available</label><textarea name="evidence_available"></textarea></div>
        <div class="form-group full"><label>Representations</label><textarea name="representations"></textarea></div>
        <div class="form-group full"><label>Settlement Offers</label><textarea name="settlement_offers"></textarea></div>
        <div class="form-group full"><label>Without Prejudice</label><textarea name="without_prejudice"></textarea></div>
        <div class="form-group full"><label>Outcome / Notes</label><textarea name="outcome"></textarea></div>
      </div>
    </div>
    <div class="btn-row">
      <a href="tort_list.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">&#128190; Save Tort Case</button>
    </div>
  </form>
</div>
</body>
</html>