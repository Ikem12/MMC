<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO criminal_cases (
            case_reference, status, case_type,
            defendant_name, defendant_dob, defendant_address, defendant_email, defendant_phone,
            defendant_nationality, defendant_custody,
            offence_date, offence_description, offence_location,
            charge_date, charges, plea,
            court_name, court_reference, hearing_date, trial_date,
            judge_name, prosecution_name,
            bail_status, bail_conditions,
            legal_aid, legal_aid_reference,
            legal_basis, evidence_available, representations,
            sentence, outcome,
            lawyer_name, law_firm
        ) VALUES (
            :case_reference, :status, :case_type,
            :defendant_name, :defendant_dob, :defendant_address, :defendant_email, :defendant_phone,
            :defendant_nationality, :defendant_custody,
            :offence_date, :offence_description, :offence_location,
            :charge_date, :charges, :plea,
            :court_name, :court_reference, :hearing_date, :trial_date,
            :judge_name, :prosecution_name,
            :bail_status, :bail_conditions,
            :legal_aid, :legal_aid_reference,
            :legal_basis, :evidence_available, :representations,
            :sentence, :outcome,
            :lawyer_name, :law_firm
        )");
        $stmt->execute([
            ':case_reference'     => $_POST['case_reference'],
            ':status'             => $_POST['status'],
            ':case_type'          => $_POST['case_type'],
            ':defendant_name'     => $_POST['defendant_name'],
            ':defendant_dob'      => $_POST['defendant_dob'],
            ':defendant_address'  => $_POST['defendant_address'],
            ':defendant_email'    => $_POST['defendant_email'],
            ':defendant_phone'    => $_POST['defendant_phone'],
            ':defendant_nationality' => $_POST['defendant_nationality'],
            ':defendant_custody'  => $_POST['defendant_custody'],
            ':offence_date'       => $_POST['offence_date'],
            ':offence_description'=> $_POST['offence_description'],
            ':offence_location'   => $_POST['offence_location'],
            ':charge_date'        => $_POST['charge_date'],
            ':charges'            => $_POST['charges'],
            ':plea'               => $_POST['plea'],
            ':court_name'         => $_POST['court_name'],
            ':court_reference'    => $_POST['court_reference'],
            ':hearing_date'       => $_POST['hearing_date'],
            ':trial_date'         => $_POST['trial_date'],
            ':judge_name'         => $_POST['judge_name'],
            ':prosecution_name'   => $_POST['prosecution_name'],
            ':bail_status'        => $_POST['bail_status'],
            ':bail_conditions'    => $_POST['bail_conditions'],
            ':legal_aid'          => $_POST['legal_aid'],
            ':legal_aid_reference'=> $_POST['legal_aid_reference'],
            ':legal_basis'        => $_POST['legal_basis'],
            ':evidence_available' => $_POST['evidence_available'],
            ':representations'    => $_POST['representations'],
            ':sentence'           => $_POST['sentence'],
            ':outcome'            => $_POST['outcome'],
            ':lawyer_name'        => $_POST['lawyer_name'],
            ':law_firm'           => $_POST['law_firm'],
        ]);
        $newId = $pdo->lastInsertId();
        header('Location: criminal_view.php?id=' . $newId);
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
<title>New Criminal Case &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#2c3e50,#4a4a4a);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem;background:#f8d7da;color:#721c24}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#2c3e50;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.red{background:#c0392b}
.section-title.orange{background:#e67e22}
.section-title.green{background:#27ae60}
.section-title.blue{background:#2980b9}
.section-title.teal{background:#16a085}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
label{font-size:0.8rem;font-weight:bold;color:#555}
input,select,textarea{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem;width:100%}
textarea{resize:vertical;min-height:80px}
.btn-row{display:flex;gap:12px;justify-content:flex-end;margin-top:10px}
.btn{padding:10px 24px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#2c3e50;color:#fff}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div><a href="dashboard.php">&#127968; Dashboard</a><a href="criminal_list.php">&#9878; Criminal</a><a href="logout.php">&#128682; Logout</a></div>
</div>
<div class="hero"><h1>&#9878; New Criminal Law Case</h1><p>Complete all sections to create a new criminal case record.</p></div>
<div class="container">
  <?php if ($error): ?><div class="alert">&#10060; <?php echo $error; ?></div><?php endif; ?>
  <form method="POST">
    <div class="card"><div class="section-title">1. CASE DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Case Type</label><select name="case_type"><option value="">-- Select --</option><option>Murder / Manslaughter</option><option>Assault / GBH / ABH</option><option>Sexual Offence</option><option>Robbery / Burglary</option><option>Fraud / Financial Crime</option><option>Drug Offence</option><option>Driving Offence</option><option>Public Order</option><option>Domestic Violence</option><option>Cybercrime</option><option>Terrorism</option><option>Money Laundering</option><option>Other</option></select></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="draft">Draft</option><option value="active">Active</option><option value="trial">At Trial</option><option value="appeal">On Appeal</option><option value="acquitted">Acquitted</option><option value="convicted">Convicted</option><option value="sentenced">Sentenced</option><option value="closed">Closed</option></select></div>
        <div class="form-group"><label>Case Reference</label><input type="text" name="case_reference" placeholder="e.g. AEP/CRIM/2026/001"/></div>
        <div class="form-group"><label>Court Reference</label><input type="text" name="court_reference" placeholder="Court case number"/></div>
        <div class="form-group"><label>Lawyer Name</label><input type="text" name="lawyer_name"/></div>
        <div class="form-group"><label>Law Firm</label><input type="text" name="law_firm" value="AEP Legal Consultancy"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title red">2. DEFENDANT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Full Name</label><input type="text" name="defendant_name"/></div>
        <div class="form-group"><label>Date of Birth</label><input type="date" name="defendant_dob"/></div>
        <div class="form-group"><label>Nationality</label><input type="text" name="defendant_nationality"/></div>
        <div class="form-group"><label>Custody Status</label><select name="defendant_custody"><option value="">-- Select --</option><option>On Bail</option><option>Remand</option><option>Custodial Sentence</option><option>Released</option></select></div>
        <div class="form-group"><label>Email</label><input type="email" name="defendant_email"/></div>
        <div class="form-group"><label>Phone</label><input type="text" name="defendant_phone"/></div>
        <div class="form-group full"><label>Address</label><textarea name="defendant_address"></textarea></div>
      </div>
    </div>
    <div class="card"><div class="section-title orange">3. OFFENCE DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Offence Date</label><input type="date" name="offence_date"/></div>
        <div class="form-group"><label>Offence Location</label><input type="text" name="offence_location"/></div>
        <div class="form-group full"><label>Offence Description</label><textarea name="offence_description"></textarea></div>
        <div class="form-group"><label>Charge Date</label><input type="date" name="charge_date"/></div>
        <div class="form-group full"><label>Charges</label><textarea name="charges" placeholder="List all charges..."></textarea></div>
        <div class="form-group"><label>Plea</label><select name="plea"><option value="">-- Select --</option><option>Guilty</option><option>Not Guilty</option><option>No Plea Yet</option></select></div>
      </div>
    </div>
    <div class="card"><div class="section-title blue">4. COURT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Court Name</label><input type="text" name="court_name" placeholder="e.g. Old Bailey, Crown Court"/></div>
        <div class="form-group"><label>Hearing Date</label><input type="date" name="hearing_date"/></div>
        <div class="form-group"><label>Trial Date</label><input type="date" name="trial_date"/></div>
        <div class="form-group"><label>Judge Name</label><input type="text" name="judge_name"/></div>
        <div class="form-group"><label>Prosecution</label><input type="text" name="prosecution_name" placeholder="e.g. CPS"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title green">5. BAIL &amp; LEGAL AID</div>
      <div class="form-grid">
        <div class="form-group"><label>Bail Status</label><select name="bail_status"><option value="">-- Select --</option><option>Granted</option><option>Refused</option><option>Conditional</option><option>N/A</option></select></div>
        <div class="form-group full"><label>Bail Conditions</label><textarea name="bail_conditions"></textarea></div>
        <div class="form-group"><label>Legal Aid Granted?</label><select name="legal_aid"><option value="">-- Select --</option><option>Yes</option><option>No</option><option>Pending</option></select></div>
        <div class="form-group"><label>Legal Aid Reference</label><input type="text" name="legal_aid_reference"/></div>
      </div>
    </div>
    <div class="card"><div class="section-title teal">6. LEGAL SUBMISSIONS &amp; OUTCOME</div>
      <div class="form-grid">
        <div class="form-group full"><label>Legal Basis</label><textarea name="legal_basis" placeholder="e.g. Criminal Justice Act 2003, PACE 1984..."></textarea></div>
        <div class="form-group full"><label>Evidence Available</label><textarea name="evidence_available"></textarea></div>
        <div class="form-group full"><label>Representations</label><textarea name="representations"></textarea></div>
        <div class="form-group full"><label>Sentence (if applicable)</label><textarea name="sentence"></textarea></div>
        <div class="form-group full"><label>Outcome / Notes</label><textarea name="outcome"></textarea></div>
      </div>
    </div>
    <div class="btn-row">
      <a href="criminal_list.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">&#128190; Save Criminal Case</button>
    </div>
  </form>
</div>
</body>
</html>