<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO employment_cases (
            case_type, case_reference, tribunal_reference, status,
            claimant_name, claimant_dob, claimant_address, claimant_email,
            claimant_phone, claimant_job_title, claimant_start_date,
            claimant_end_date, claimant_salary, claimant_notice_period,
            respondent_name, respondent_address, respondent_contact, respondent_sector,
            dismissal_date, dismissal_reason, disciplinary_process,
            appeal_lodged, appeal_outcome,
            claim_unfair_dismissal, claim_wrongful_dismissal, claim_discrimination,
            discrimination_type, claim_harassment, claim_whistleblowing,
            claim_redundancy, claim_unpaid_wages, claim_other,
            et1_filed, et1_date, et3_received, et3_date,
            hearing_date, hearing_venue, preliminary_hearing,
            legal_basis, schedule_loss, without_prejudice,
            acas_certificate, acas_number, settlement_offers,
            representations, evidence_available, lawyer_name, law_firm
        ) VALUES (
            :case_type, :case_reference, :tribunal_reference, :status,
            :claimant_name, :claimant_dob, :claimant_address, :claimant_email,
            :claimant_phone, :claimant_job_title, :claimant_start_date,
            :claimant_end_date, :claimant_salary, :claimant_notice_period,
            :respondent_name, :respondent_address, :respondent_contact, :respondent_sector,
            :dismissal_date, :dismissal_reason, :disciplinary_process,
            :appeal_lodged, :appeal_outcome,
            :claim_unfair_dismissal, :claim_wrongful_dismissal, :claim_discrimination,
            :discrimination_type, :claim_harassment, :claim_whistleblowing,
            :claim_redundancy, :claim_unpaid_wages, :claim_other,
            :et1_filed, :et1_date, :et3_received, :et3_date,
            :hearing_date, :hearing_venue, :preliminary_hearing,
            :legal_basis, :schedule_loss, :without_prejudice,
            :acas_certificate, :acas_number, :settlement_offers,
            :representations, :evidence_available, :lawyer_name, :law_firm
        )");

        $stmt->execute([
            ':case_type'                => $_POST['case_type'],
            ':case_reference'           => $_POST['case_reference'],
            ':tribunal_reference'       => $_POST['tribunal_reference'],
            ':status'                   => $_POST['status'],
            ':claimant_name'            => $_POST['claimant_name'],
            ':claimant_dob'             => $_POST['claimant_dob'],
            ':claimant_address'         => $_POST['claimant_address'],
            ':claimant_email'           => $_POST['claimant_email'],
            ':claimant_phone'           => $_POST['claimant_phone'],
            ':claimant_job_title'       => $_POST['claimant_job_title'],
            ':claimant_start_date'      => $_POST['claimant_start_date'],
            ':claimant_end_date'        => $_POST['claimant_end_date'],
            ':claimant_salary'          => $_POST['claimant_salary'],
            ':claimant_notice_period'   => $_POST['claimant_notice_period'],
            ':respondent_name'          => $_POST['respondent_name'],
            ':respondent_address'       => $_POST['respondent_address'],
            ':respondent_contact'       => $_POST['respondent_contact'],
            ':respondent_sector'        => $_POST['respondent_sector'],
            ':dismissal_date'           => $_POST['dismissal_date'],
            ':dismissal_reason'         => $_POST['dismissal_reason'],
            ':disciplinary_process'     => $_POST['disciplinary_process'],
            ':appeal_lodged'            => $_POST['appeal_lodged'],
            ':appeal_outcome'           => $_POST['appeal_outcome'],
            ':claim_unfair_dismissal'   => isset($_POST['claim_unfair_dismissal']) ? 'yes' : 'no',
            ':claim_wrongful_dismissal' => isset($_POST['claim_wrongful_dismissal']) ? 'yes' : 'no',
            ':claim_discrimination'     => isset($_POST['claim_discrimination']) ? 'yes' : 'no',
            ':discrimination_type'      => $_POST['discrimination_type'],
            ':claim_harassment'         => isset($_POST['claim_harassment']) ? 'yes' : 'no',
            ':claim_whistleblowing'     => isset($_POST['claim_whistleblowing']) ? 'yes' : 'no',
            ':claim_redundancy'         => isset($_POST['claim_redundancy']) ? 'yes' : 'no',
            ':claim_unpaid_wages'       => isset($_POST['claim_unpaid_wages']) ? 'yes' : 'no',
            ':claim_other'              => $_POST['claim_other'],
            ':et1_filed'                => $_POST['et1_filed'],
            ':et1_date'                 => $_POST['et1_date'],
            ':et3_received'             => $_POST['et3_received'],
            ':et3_date'                 => $_POST['et3_date'],
            ':hearing_date'             => $_POST['hearing_date'],
            ':hearing_venue'            => $_POST['hearing_venue'],
            ':preliminary_hearing'      => $_POST['preliminary_hearing'],
            ':legal_basis'              => $_POST['legal_basis'],
            ':schedule_loss'            => $_POST['schedule_loss'],
            ':without_prejudice'        => $_POST['without_prejudice'],
            ':acas_certificate'         => $_POST['acas_certificate'],
            ':acas_number'              => $_POST['acas_number'],
            ':settlement_offers'        => $_POST['settlement_offers'],
            ':representations'          => $_POST['representations'],
            ':evidence_available'       => $_POST['evidence_available'],
            ':lawyer_name'              => $_POST['lawyer_name'],
            ':law_firm'                 => $_POST['law_firm'],
        ]);

        $newId = $pdo->lastInsertId();
        header('Location: employment_view.php?id=' . $newId);
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
<title>New Employment Case — AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.topbar a:hover{text-decoration:underline}
.hero{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem}
.alert-error{background:#f8d7da;color:#721c24}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#1a3c5e;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.red{background:#c0392b}
.section-title.orange{background:#e67e22}
.section-title.green{background:#27ae60}
.section-title.purple{background:#8e44ad}
.section-title.teal{background:#16a085}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
label{font-size:0.8rem;font-weight:bold;color:#555}
input,select,textarea{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem;width:100%}
input:focus,select:focus,textarea:focus{outline:none;border-color:#1a3c5e}
textarea{resize:vertical;min-height:80px}
.checkbox-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:8px}
.checkbox-item{display:flex;align-items:center;gap:8px;font-size:0.85rem}
.checkbox-item input{width:auto}
.btn-row{display:flex;gap:12px;justify-content:flex-end;margin-top:10px}
.btn{padding:10px 24px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-primary:hover{background:#122840}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
.btn-outline:hover{background:#f0f4ff}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="employment_list.php">&#128188; Employment Cases</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>

<div class="hero">
  <h1>&#128188; New Employment Law Case</h1>
  <p>Complete all sections below to create a new employment case record.</p>
</div>

<div class="container">

  <?php if ($error): ?>
    <div class="alert alert-error">&#10060; <?php echo $error; ?></div>
  <?php endif; ?>

  <form method="POST">

    <!-- 1. CASE DETAILS -->
    <div class="card">
      <div class="section-title">1. CASE DETAILS</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Case Type</label>
          <select name="case_type">
            <option value="">-- Select --</option>
            <option>Unfair Dismissal</option>
            <option>Wrongful Dismissal</option>
            <option>Discrimination</option>
            <option>Harassment</option>
            <option>Whistleblowing</option>
            <option>Redundancy</option>
            <option>Unpaid Wages</option>
            <option>Constructive Dismissal</option>
            <option>TUPE</option>
            <option>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="tribunal">At Tribunal</option>
            <option value="settled">Settled</option>
            <option value="won">Won</option>
            <option value="lost">Lost</option>
            <option value="withdrawn">Withdrawn</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        <div class="form-group">
          <label>Case Reference</label>
          <input type="text" name="case_reference" placeholder="e.g. AEP/EMP/2026/001"/>
        </div>
        <div class="form-group">
          <label>Tribunal Reference</label>
          <input type="text" name="tribunal_reference" placeholder="e.g. 2300123/2026"/>
        </div>
        <div class="form-group">
          <label>Lawyer / Solicitor Name</label>
          <input type="text" name="lawyer_name" placeholder="Full name"/>
        </div>
        <div class="form-group">
          <label>Law Firm</label>
          <input type="text" name="law_firm" value="AEP Legal Consultancy"/>
        </div>
      </div>
    </div>

    <!-- 2. CLAIMANT DETAILS -->
    <div class="card">
      <div class="section-title red">2. CLAIMANT DETAILS</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="claimant_name" placeholder="Claimant full name"/>
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" name="claimant_dob"/>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="claimant_email" placeholder="Email address"/>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="claimant_phone" placeholder="Phone number"/>
        </div>
        <div class="form-group full">
          <label>Address</label>
          <textarea name="claimant_address" placeholder="Full address"></textarea>
        </div>
        <div class="form-group">
          <label>Job Title</label>
          <input type="text" name="claimant_job_title" placeholder="Position held"/>
        </div>
        <div class="form-group">
          <label>Annual Salary</label>
          <input type="text" name="claimant_salary" placeholder="e.g. £35,000"/>
        </div>
        <div class="form-group">
          <label>Employment Start Date</label>
          <input type="date" name="claimant_start_date"/>
        </div>
        <div class="form-group">
          <label>Employment End Date</label>
          <input type="date" name="claimant_end_date"/>
        </div>
        <div class="form-group">
          <label>Notice Period</label>
          <input type="text" name="claimant_notice_period" placeholder="e.g. 3 months"/>
        </div>
      </div>
    </div>

    <!-- 3. RESPONDENT DETAILS -->
    <div class="card">
      <div class="section-title orange">3. RESPONDENT / EMPLOYER DETAILS</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Employer / Company Name</label>
          <input type="text" name="respondent_name" placeholder="Company name"/>
        </div>
        <div class="form-group">
          <label>Sector / Industry</label>
          <input type="text" name="respondent_sector" placeholder="e.g. Healthcare, Retail"/>
        </div>
        <div class="form-group">
          <label>Contact Person</label>
          <input type="text" name="respondent_contact" placeholder="HR Manager / Director"/>
        </div>
        <div class="form-group full">
          <label>Employer Address</label>
          <textarea name="respondent_address" placeholder="Full address"></textarea>
        </div>
      </div>
    </div>

    <!-- 4. DISMISSAL DETAILS -->
    <div class="card">
      <div class="section-title purple">4. DISMISSAL DETAILS</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Date of Dismissal</label>
          <input type="date" name="dismissal_date"/>
        </div>
        <div class="form-group">
          <label>Reason Given by Employer</label>
          <input type="text" name="dismissal_reason" placeholder="e.g. Misconduct, Redundancy"/>
        </div>
        <div class="form-group full">
          <label>Disciplinary Process Followed</label>
          <textarea name="disciplinary_process" placeholder="Describe the disciplinary process..."></textarea>
        </div>
        <div class="form-group">
          <label>Internal Appeal Lodged?</label>
          <select name="appeal_lodged">
            <option value="">-- Select --</option>
            <option>Yes</option>
            <option>No</option>
            <option>N/A</option>
          </select>
        </div>
        <div class="form-group">
          <label>Appeal Outcome</label>
          <input type="text" name="appeal_outcome" placeholder="e.g. Upheld, Dismissed"/>
        </div>
      </div>
    </div>

    <!-- 5. CLAIMS -->
    <div class="card">
      <div class="section-title green">5. CLAIMS BEING MADE</div>
      <label style="font-size:0.85rem;color:#555;margin-bottom:8px;display:block">Select all that apply:</label>
      <div class="checkbox-grid">
        <label class="checkbox-item"><input type="checkbox" name="claim_unfair_dismissal"/> Unfair Dismissal</label>
        <label class="checkbox-item"><input type="checkbox" name="claim_wrongful_dismissal"/> Wrongful Dismissal</label>
        <label class="checkbox-item"><input type="checkbox" name="claim_discrimination"/> Discrimination</label>
        <label class="checkbox-item"><input type="checkbox" name="claim_harassment"/> Harassment</label>
        <label class="checkbox-item"><input type="checkbox" name="claim_whistleblowing"/> Whistleblowing</label>
        <label class="checkbox-item"><input type="checkbox" name="claim_redundancy"/> Redundancy</label>
        <label class="checkbox-item"><input type="checkbox" name="claim_unpaid_wages"/> Unpaid Wages</label>
      </div>
      <div class="form-grid" style="margin-top:16px">
        <div class="form-group">
          <label>Discrimination Type (if applicable)</label>
          <select name="discrimination_type">
            <option value="">-- Select --</option>
            <option>Age</option>
            <option>Disability</option>
            <option>Gender Reassignment</option>
            <option>Marriage / Civil Partnership</option>
            <option>Pregnancy / Maternity</option>
            <option>Race</option>
            <option>Religion or Belief</option>
            <option>Sex</option>
            <option>Sexual Orientation</option>
          </select>
        </div>
        <div class="form-group">
          <label>Other Claims</label>
          <input type="text" name="claim_other" placeholder="Any other claims..."/>
        </div>
      </div>
    </div>

    <!-- 6. TRIBUNAL DETAILS -->
    <div class="card">
      <div class="section-title teal">6. EMPLOYMENT TRIBUNAL DETAILS</div>
      <div class="form-grid">
        <div class="form-group">
          <label>ET1 Filed?</label>
          <select name="et1_filed">
            <option value="">-- Select --</option>
            <option>Yes</option>
            <option>No</option>
            <option>Pending</option>
          </select>
        </div>
        <div class="form-group">
          <label>ET1 Date Filed</label>
          <input type="date" name="et1_date"/>
        </div>
        <div class="form-group">
          <label>ET3 Received?</label>
          <select name="et3_received">
            <option value="">-- Select --</option>
            <option>Yes</option>
            <option>No</option>
            <option>Pending</option>
          </select>
        </div>
        <div class="form-group">
          <label>ET3 Date Received</label>
          <input type="date" name="et3_date"/>
        </div>
        <div class="form-group">
          <label>Preliminary Hearing Date</label>
          <input type="date" name="preliminary_hearing"/>
        </div>
        <div class="form-group">
          <label>Final Hearing Date</label>
          <input type="date" name="hearing_date"/>
        </div>
        <div class="form-group full">
          <label>Hearing Venue</label>
          <input type="text" name="hearing_venue" placeholder="e.g. London Central Employment Tribunal"/>
        </div>
        <div class="form-group">
          <label>ACAS Early Conciliation Certificate?</label>
          <select name="acas_certificate">
            <option value="">-- Select --</option>
            <option>Yes</option>
            <option>No</option>
            <option>Pending</option>
          </select>
        </div>
        <div class="form-group">
          <label>ACAS Certificate Number</label>
          <input type="text" name="acas_number" placeholder="e.g. R000000/26/00"/>
        </div>
      </div>
    </div>

    <!-- 7. LEGAL SUBMISSIONS -->
    <div class="card">
      <div class="section-title">7. LEGAL SUBMISSIONS &amp; EVIDENCE</div>
      <div class="form-grid">
        <div class="form-group full">
          <label>Legal Basis of Claim</label>
          <textarea name="legal_basis" rows="4" placeholder="ERA 1996, Equality Act 2010, etc..."></textarea>
        </div>
        <div class="form-group full">
          <label>Schedule of Loss</label>
          <textarea name="schedule_loss" rows="4" placeholder="Basic award, compensatory award, etc..."></textarea>
        </div>
        <div class="form-group full">
          <label>Without Prejudice / Settlement Offers</label>
          <textarea name="without_prejudice" rows="3" placeholder="Details of without prejudice offers..."></textarea>
        </div>
        <div class="form-group full">
          <label>Settlement Offers</label>
          <textarea name="settlement_offers" rows="3" placeholder="Details of settlement offers..."></textarea>
        </div>
        <div class="form-group full">
          <label>Representations &amp; Submissions</label>
          <textarea name="representations" rows="5" placeholder="Full legal representations..."></textarea>
        </div>
        <div class="form-group full">
          <label>Evidence &amp; Documents Available</label>
          <textarea name="evidence_available" rows="4" placeholder="Contracts, payslips, emails, etc..."></textarea>
        </div>
      </div>
    </div>

    <div class="btn-row">
      <a href="employment_list.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">&#128190; Save Employment Case</button>
    </div>

  </form>
</div>

</body>
</html>
