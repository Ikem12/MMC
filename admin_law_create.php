<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ============================================================
// AUTO-CREATE admin_law_cases TABLE IF NOT EXISTS
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS admin_law_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_reference TEXT,
    status TEXT DEFAULT 'draft',
    case_type TEXT,
    client_name TEXT,
    client_address TEXT,
    client_email TEXT,
    client_phone TEXT,
    public_body_name TEXT,
    public_body_address TEXT,
    public_body_contact TEXT,
    decision_date TEXT,
    decision_description TEXT,
    grounds_of_challenge TEXT,
    judicial_review TEXT,
    jr_permission_date TEXT,
    jr_hearing_date TEXT,
    jr_venue TEXT,
    tribunal_name TEXT,
    tribunal_reference TEXT,
    tribunal_hearing_date TEXT,
    human_rights_article TEXT,
    regulatory_body TEXT,
    licence_reference TEXT,
    legal_basis TEXT,
    evidence_available TEXT,
    representations TEXT,
    settlement_offers TEXT,
    outcome TEXT,
    lawyer_name TEXT,
    law_firm TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ============================================================
// AUTO-GENERATE CASE REFERENCE: AEP/ADM/YYYY/NNN
// ============================================================
$year   = date('Y');
$prefix = 'AEP/ADM/' . $year . '/';
$stmt   = $pdo->query("SELECT COUNT(*) FROM admin_law_cases WHERE case_reference LIKE '" . $prefix . "%'");
$count  = (int)$stmt->fetchColumn();
$auto_reference = $prefix . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Use auto-reference unless user typed an override
        $case_ref = !empty(trim($_POST['case_reference_override']))
                    ? trim($_POST['case_reference_override'])
                    : $_POST['case_reference'];

        $stmt = $pdo->prepare("INSERT INTO admin_law_cases (
            case_reference, status, case_type,
            client_name, client_address, client_email, client_phone,
            public_body_name, public_body_address, public_body_contact,
            decision_date, decision_description, grounds_of_challenge,
            judicial_review, jr_permission_date, jr_hearing_date, jr_venue,
            tribunal_name, tribunal_reference, tribunal_hearing_date,
            human_rights_article, regulatory_body, licence_reference,
            legal_basis, evidence_available, representations,
            settlement_offers, outcome,
            lawyer_name, law_firm
        ) VALUES (
            :case_reference, :status, :case_type,
            :client_name, :client_address, :client_email, :client_phone,
            :public_body_name, :public_body_address, :public_body_contact,
            :decision_date, :decision_description, :grounds_of_challenge,
            :judicial_review, :jr_permission_date, :jr_hearing_date, :jr_venue,
            :tribunal_name, :tribunal_reference, :tribunal_hearing_date,
            :human_rights_article, :regulatory_body, :licence_reference,
            :legal_basis, :evidence_available, :representations,
            :settlement_offers, :outcome,
            :lawyer_name, :law_firm
        )");
        $stmt->execute([
            ':case_reference'        => $case_ref,
            ':status'                => $_POST['status'],
            ':case_type'             => $_POST['case_type'],
            ':client_name'           => $_POST['client_name'],
            ':client_address'        => $_POST['client_address'],
            ':client_email'          => $_POST['client_email'],
            ':client_phone'          => $_POST['client_phone'],
            ':public_body_name'      => $_POST['public_body_name'],
            ':public_body_address'   => $_POST['public_body_address'],
            ':public_body_contact'   => $_POST['public_body_contact'],
            ':decision_date'         => $_POST['decision_date'],
            ':decision_description'  => $_POST['decision_description'],
            ':grounds_of_challenge'  => $_POST['grounds_of_challenge'],
            ':judicial_review'       => $_POST['judicial_review'],
            ':jr_permission_date'    => $_POST['jr_permission_date'],
            ':jr_hearing_date'       => $_POST['jr_hearing_date'],
            ':jr_venue'              => $_POST['jr_venue'],
            ':tribunal_name'         => $_POST['tribunal_name'],
            ':tribunal_reference'    => $_POST['tribunal_reference'],
            ':tribunal_hearing_date' => $_POST['tribunal_hearing_date'],
            ':human_rights_article'  => $_POST['human_rights_article'],
            ':regulatory_body'       => $_POST['regulatory_body'],
            ':licence_reference'     => $_POST['licence_reference'],
            ':legal_basis'           => $_POST['legal_basis'],
            ':evidence_available'    => $_POST['evidence_available'],
            ':representations'       => $_POST['representations'],
            ':settlement_offers'     => $_POST['settlement_offers'],
            ':outcome'               => $_POST['outcome'],
            ':lawyer_name'           => $_POST['lawyer_name'],
            ':law_firm'              => $_POST['law_firm'],
        ]);
        $newId = $pdo->lastInsertId();
        header('Location: admin_law_view.php?id=' . $newId);
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
<title>New Administrative Law Case &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#6c3483,#9b59b6);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem;background:#f8d7da;color:#721c24}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#6c3483;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.blue{background:#1a3c5e}
.section-title.green{background:#27ae60}
.section-title.red{background:#c0392b}
.section-title.teal{background:#16a085}
.section-title.orange{background:#e67e22}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
label{font-size:0.8rem;font-weight:bold;color:#555}
input,select,textarea{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem;width:100%}
input:focus,select:focus,textarea:focus{outline:none;border-color:#6c3483}
textarea{resize:vertical;min-height:80px}
.btn-row{display:flex;gap:12px;justify-content:flex-end;margin-top:10px}
.btn{padding:10px 24px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#6c3483;color:#fff}
.btn-primary:hover{background:#5b2c6f}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
.auto-ref-box{background:#eaf4ff;border:1px solid #90caf9;border-radius:4px;padding:10px 14px;font-size:0.95rem;font-weight:bold;color:#1a3c5e;letter-spacing:0.5px}
.ref-hint{font-size:0.75rem;color:#888;margin-top:5px}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="admin_law_list.php">&#127963; Admin Law Cases</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#127963; New Administrative Law Case</h1>
  <p>Complete all sections below to create a new administrative law case record.</p>
</div>
<div class="container">
  <?php if ($error): ?><div class="alert">&#10060; <?php echo $error; ?></div><?php endif; ?>
  <form method="POST">

    <div class="card">
      <div class="section-title blue">1. CASE DETAILS</div>
      <div class="form-grid">

        <div class="form-group"><label>Case Type</label>
          <select name="case_type">
            <option value="">-- Select --</option>
            <option>Judicial Review</option>
            <option>Tribunal Appeal</option>
            <option>Human Rights Challenge</option>
            <option>Regulatory Appeal</option>
            <option>Local Authority Challenge</option>
            <option>Planning Appeal</option>
            <option>Licensing Appeal</option>
            <option>Freedom of Information</option>
            <option>Public Law Other</option>
          </select>
        </div>

        <div class="form-group"><label>Status</label>
          <select name="status">
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="court">At Court</option>
            <option value="tribunal">At Tribunal</option>
            <option value="won">Won</option>
            <option value="lost">Lost</option>
            <option value="settled">Settled</option>
            <option value="withdrawn">Withdrawn</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <!-- AUTO-GENERATED REFERENCE DISPLAY -->
        <div class="form-group">
          <label>&#128196; Case Reference (Auto-Generated)</label>
          <div class="auto-ref-box">&#9989; <?php echo htmlspecialchars($auto_reference); ?></div>
          <input type="hidden" name="case_reference" value="<?php echo htmlspecialchars($auto_reference); ?>"/>
          <span class="ref-hint">&#10003; Automatically assigned. Fill the override box below ONLY if you need a different reference.</span>
        </div>

        <!-- OPTIONAL MANUAL OVERRIDE -->
        <div class="form-group">
          <label>Override Reference <small style="color:#aaa;font-weight:normal">(optional &mdash; leave blank to use auto)</small></label>
          <input type="text" name="case_reference_override" placeholder="e.g. AEP/ADM/2026/007"/>
        </div>

        <div class="form-group"><label>Lawyer Name</label><input type="text" name="lawyer_name" placeholder="Full name"/></div>
        <div class="form-group"><label>Law Firm</label><input type="text" name="law_firm" value="AEP Legal Consultancy"/></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title">2. CLIENT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Full Name</label><input type="text" name="client_name" placeholder="Client full name"/></div>
        <div class="form-group"><label>Email</label><input type="email" name="client_email"/></div>
        <div class="form-group"><label>Phone</label><input type="text" name="client_phone"/></div>
        <div class="form-group full"><label>Address</label><textarea name="client_address" placeholder="Full address"></textarea></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title red">3. PUBLIC BODY / RESPONDENT</div>
      <div class="form-grid">
        <div class="form-group"><label>Public Body / Authority Name</label><input type="text" name="public_body_name" placeholder="e.g. Home Office, Local Council"/></div>
        <div class="form-group"><label>Contact Person</label><input type="text" name="public_body_contact" placeholder="Contact name"/></div>
        <div class="form-group full"><label>Public Body Address</label><textarea name="public_body_address" placeholder="Full address"></textarea></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title orange">4. DECISION DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Date of Decision</label><input type="date" name="decision_date"/></div>
        <div class="form-group full"><label>Description of Decision Being Challenged</label><textarea name="decision_description" placeholder="Describe the decision..."></textarea></div>
        <div class="form-group full"><label>Grounds of Challenge</label><textarea name="grounds_of_challenge" placeholder="e.g. Illegality, Irrationality, Procedural Impropriety, Human Rights breach..."></textarea></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title green">5. JUDICIAL REVIEW DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Judicial Review Sought?</label>
          <select name="judicial_review">
            <option value="">-- Select --</option>
            <option>Yes</option><option>No</option><option>Pending</option>
          </select>
        </div>
        <div class="form-group"><label>JR Permission Date</label><input type="date" name="jr_permission_date"/></div>
        <div class="form-group"><label>JR Hearing Date</label><input type="date" name="jr_hearing_date"/></div>
        <div class="form-group"><label>JR Venue</label><input type="text" name="jr_venue" placeholder="e.g. Administrative Court, Royal Courts of Justice"/></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title teal">6. TRIBUNAL DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Tribunal Name</label><input type="text" name="tribunal_name" placeholder="e.g. Upper Tribunal, First-tier Tribunal"/></div>
        <div class="form-group"><label>Tribunal Reference</label><input type="text" name="tribunal_reference" placeholder="Tribunal case number"/></div>
        <div class="form-group"><label>Tribunal Hearing Date</label><input type="date" name="tribunal_hearing_date"/></div>
        <div class="form-group"><label>Human Rights Article(s)</label><input type="text" name="human_rights_article" placeholder="e.g. Article 6, Article 8 ECHR"/></div>
        <div class="form-group"><label>Regulatory Body</label><input type="text" name="regulatory_body" placeholder="e.g. FCA, CQC, Ofsted"/></div>
        <div class="form-group"><label>Licence / Reference Number</label><input type="text" name="licence_reference" placeholder="Licence or permit number"/></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title blue">7. LEGAL SUBMISSIONS &amp; EVIDENCE</div>
      <div class="form-grid">
        <div class="form-group full"><label>Legal Basis</label><textarea name="legal_basis" placeholder="e.g. Human Rights Act 1998, Senior Courts Act 1981, GDPR..."></textarea></div>
        <div class="form-group full"><label>Evidence Available</label><textarea name="evidence_available" placeholder="Letters, decisions, emails, reports..."></textarea></div>
        <div class="form-group full"><label>Representations</label><textarea name="representations" placeholder="Full legal representations..."></textarea></div>
        <div class="form-group full"><label>Settlement Offers</label><textarea name="settlement_offers" placeholder="Details of any offers..."></textarea></div>
        <div class="form-group full"><label>Outcome / Notes</label><textarea name="outcome" placeholder="Case outcome or ongoing notes..."></textarea></div>
      </div>
    </div>

    <div class="btn-row">
      <a href="admin_law_list.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">&#128190; Save Administrative Law Case</button>
    </div>
  </form>
</div>
</body>
</html>
