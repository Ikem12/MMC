<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO immigration_cases (
            case_reference, status, case_type,
            client_name, client_dob, client_address, client_email, client_phone,
            client_nationality, client_passport, client_entry_date, client_visa_type,
            client_visa_expiry, client_leave_type,
            sponsor_name, sponsor_address, sponsor_licence,
            home_office_reference, decision_date, decision_description,
            appeal_lodged, appeal_date, appeal_tribunal, appeal_reference,
            removal_date, detention_centre,
            legal_basis, evidence_available, representations,
            lawyer_name, law_firm
        ) VALUES (
            :case_reference, :status, :case_type,
            :client_name, :client_dob, :client_address, :client_email, :client_phone,
            :client_nationality, :client_passport, :client_entry_date, :client_visa_type,
            :client_visa_expiry, :client_leave_type,
            :sponsor_name, :sponsor_address, :sponsor_licence,
            :home_office_reference, :decision_date, :decision_description,
            :appeal_lodged, :appeal_date, :appeal_tribunal, :appeal_reference,
            :removal_date, :detention_centre,
            :legal_basis, :evidence_available, :representations,
            :lawyer_name, :law_firm
        )");
        $stmt->execute([
            ':case_reference'      => $_POST['case_reference'],
            ':status'              => $_POST['status'],
            ':case_type'           => $_POST['case_type'],
            ':client_name'         => $_POST['client_name'],
            ':client_dob'          => $_POST['client_dob'],
            ':client_address'      => $_POST['client_address'],
            ':client_email'        => $_POST['client_email'],
            ':client_phone'        => $_POST['client_phone'],
            ':client_nationality'  => $_POST['client_nationality'],
            ':client_passport'     => $_POST['client_passport'],
            ':client_entry_date'   => $_POST['client_entry_date'],
            ':client_visa_type'    => $_POST['client_visa_type'],
            ':client_visa_expiry'  => $_POST['client_visa_expiry'],
            ':client_leave_type'   => $_POST['client_leave_type'],
            ':sponsor_name'        => $_POST['sponsor_name'],
            ':sponsor_address'     => $_POST['sponsor_address'],
            ':sponsor_licence'     => $_POST['sponsor_licence'],
            ':home_office_reference' => $_POST['home_office_reference'],
            ':decision_date'       => $_POST['decision_date'],
            ':decision_description'=> $_POST['decision_description'],
            ':appeal_lodged'       => $_POST['appeal_lodged'],
            ':appeal_date'         => $_POST['appeal_date'],
            ':appeal_tribunal'     => $_POST['appeal_tribunal'],
            ':appeal_reference'    => $_POST['appeal_reference'],
            ':removal_date'        => $_POST['removal_date'],
            ':detention_centre'    => $_POST['detention_centre'],
            ':legal_basis'         => $_POST['legal_basis'],
            ':evidence_available'  => $_POST['evidence_available'],
            ':representations'     => $_POST['representations'],
            ':lawyer_name'         => $_POST['lawyer_name'],
            ':law_firm'            => $_POST['law_firm'],
        ]);
        $newId = $pdo->lastInsertId();
        header('Location: immigration_view.php?id=' . $newId);
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
<title>New Immigration Case &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#2980b9,#3498db);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem;background:#f8d7da;color:#721c24}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#2980b9;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.navy{background:#1a3c5e}
.section-title.green{background:#27ae60}
.section-title.red{background:#c0392b}
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
.btn-primary{background:#2980b9;color:#fff}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div><a href="dashboard.php">&#127968; Dashboard</a><a href="immigration_list.php">&#9992;&#65039; Immigration</a><a href="logout.php">&#128682; Logout</a></div>
</div>
<div class="hero"><h1>&#9992;&#65039; New Immigration Case</h1><p>Complete all sections to create a new immigration case record.</p></div>
<div class="container">
  <?php if ($error): ?><div class="alert">&#10060; <?php echo $error; ?></div><?php endif; ?>
  <form method="POST">
    <div class="card"><div class="section-title navy">1. CASE DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Case Type</label><select name="case_type"><option value="">-- Select --</option><option>Visa Application</option><option>Leave to Remain</option><option>Indefinite Leave to Remain</option><option>British Citizenship</option><option>Asylum</option><option>Humanitarian Protection</option><option>Deportation Defence</option><option>Removal Challenge</option><option>Family Reunion</option><option>EEA / Settled Status</option><option>Points-Based System</option><option>Student Visa</option><option>Work Visa</option><option>Spouse / Partner Visa</option><option>Other</option></select></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="appeal">On Appeal</option><option value="tribunal">At Tribunal</option><option value="won">Won</option><option value="lost">Lost</option><option value="withdrawn">Withdrawn</option><option value="closed">Closed</option></select></div>
        <div class="form-group"><label>Case Reference</label><input type="text" name="case_reference" placeholder="e.g. AEP/IMM/2026/001"/></div>
        <div class="form-group"><label>Home Office Reference</label><input type="text" name="home_office_reference" placeholder="HO Reference number"/></div>
        <div class="form-group"><label>Lawyer Name</label><input type="text" name="lawyer_name"/></div>
        <div class="form-group"><label>Law Firm</label><input type="text" name="law_firm" value="AEP Legal Consultancy"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title">2. CLIENT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Full Name</label><input type="text" name="client_name"/></div>
        <div class="form-group"><label>Date of Birth</label><input type="date" name="client_dob"/></div>
        <div class="form-group"><label>Nationality</label><input type="text" name="client_nationality" placeholder="Country of nationality"/></div>
        <div class="form-group"><label>Passport Number</label><input type="text" name="client_passport"/></div>
        <div class="form-group"><label>Email</label><input type="email" name="client_email"/></div>
        <div class="form-group"><label>Phone</label><input type="text" name="client_phone"/></div>
        <div class="form-group full"><label>Address</label><textarea name="client_address"></textarea></div>
        <div class="form-group"><label>Date of Entry to UK</label><input type="date" name="client_entry_date"/></div>
        <div class="form-group"><label>Current Visa Type</label><input type="text" name="client_visa_type" placeholder="e.g. Tier 2, Student"/></div>
        <div class="form-group"><label>Visa Expiry Date</label><input type="date" name="client_visa_expiry"/></div>
        <div class="form-group"><label>Leave Type</label><input type="text" name="client_leave_type" placeholder="e.g. Limited, Indefinite"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title orange">3. SPONSOR DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Sponsor Name</label><input type="text" name="sponsor_name"/></div>
        <div class="form-group"><label>Sponsor Licence Number</label><input type="text" name="sponsor_licence"/></div>
        <div class="form-group full"><label>Sponsor Address</label><textarea name="sponsor_address"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title red">4. HOME OFFICE DECISION</div>
      <div class="form-grid">
        <div class="form-group"><label>Decision Date</label><input type="date" name="decision_date"/></div>
        <div class="form-group full"><label>Decision Description</label><textarea name="decision_description" placeholder="Describe the Home Office decision..."></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title green">5. APPEAL DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Appeal Lodged?</label><select name="appeal_lodged"><option value="">-- Select --</option><option>Yes</option><option>No</option><option>Pending</option></select></div>
        <div class="form-group"><label>Appeal Date</label><input type="date" name="appeal_date"/></div>
        <div class="form-group"><label>Appeal Tribunal</label><input type="text" name="appeal_tribunal" placeholder="e.g. First-tier Tribunal (IAC)"/></div>
        <div class="form-group"><label>Appeal Reference</label><input type="text" name="appeal_reference"/></div>
        <div class="form-group"><label>Removal Date (if applicable)</label><input type="date" name="removal_date"/></div>
        <div class="form-group"><label>Detention Centre</label><input type="text" name="detention_centre" placeholder="If detained"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title teal">6. LEGAL SUBMISSIONS &amp; EVIDENCE</div>
      <div class="form-grid">
        <div class="form-group full"><label>Legal Basis</label><textarea name="legal_basis" placeholder="e.g. Immigration Act 1971, NIAA 2002, Human Rights Act 1998..."></textarea></div>
        <div class="form-group full"><label>Evidence Available</label><textarea name="evidence_available"></textarea></div>
        <div class="form-group full"><label>Representations</label><textarea name="representations"></textarea></div>
      </div>
    </div>
    <div class="btn-row">
      <a href="immigration_list.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">&#128190; Save Immigration Case</button>
    </div>
  </form>
</div>
</body>
</html>