<?php
// FILE: hr_cover_letter.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id   = (int)($_GET['id'] ?? 0);
$case = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM human_rights_cases WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
}

$today = date('d F Y');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Human Rights Cover Letter — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#b5451b;color:#fff;padding:24px 20px;text-align:center}
    header h1{font-size:1.6rem;margin-bottom:4px}
    header p{font-size:0.9rem;opacity:0.85}
    .file-tag{font-size:11px;background:#fff;color:#b5451b;padding:3px 8px;border-radius:4px;margin-left:10px;vertical-align:middle}
    nav{background:#7a2e0e;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
    nav a:hover{background:rgba(255,255,255,0.3)}
    .container{max-width:860px;margin:30px auto;padding:0 18px}
    .action-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
    .btn{display:inline-block;padding:9px 20px;background:#b5451b;color:#fff;border-radius:4px;text-decoration:none;font-size:0.9rem;border:none;cursor:pointer}
    .btn:hover{background:#7a2e0e}
    .btn-secondary{background:#6c757d}
    .btn-secondary:hover{background:#495057}
    .btn-print{background:#28a745}
    .btn-print:hover{background:#1e7e34}

    /* Letter styles */
    .letter{background:#fff;border-radius:8px;padding:50px 60px;box-shadow:0 2px 12px rgba(0,0,0,0.10);margin-bottom:30px;min-height:800px}
    .letter-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:40px;padding-bottom:20px;border-bottom:3px solid #b5451b}
    .firm-name{font-size:1.4rem;font-weight:bold;color:#b5451b}
    .firm-sub{font-size:0.85rem;color:#666;margin-top:4px}
    .letter-date{font-size:0.9rem;color:#444;text-align:right}
    .ref-line{font-size:0.88rem;color:#666;text-align:right;margin-top:4px}
    .addressee{margin-bottom:30px}
    .addressee p{font-size:0.92rem;line-height:1.8;color:#333}
    .addressee strong{color:#222}
    .letter-subject{font-size:1rem;font-weight:bold;color:#b5451b;margin-bottom:24px;padding:10px 0;border-bottom:1px solid #eee}
    .letter-body p{font-size:0.92rem;line-height:1.9;color:#333;margin-bottom:16px}
    .letter-body ul{margin:12px 0 16px 24px}
    .letter-body ul li{font-size:0.92rem;line-height:1.8;color:#333}
    .letter-body strong{color:#222}
    .signature{margin-top:40px;padding-top:20px;border-top:1px solid #eee}
    .signature p{font-size:0.92rem;line-height:1.8;color:#333}
    .signature .sig-name{font-size:1rem;font-weight:bold;color:#b5451b;margin-top:30px}
    .signature .sig-title{font-size:0.85rem;color:#666}
    .no-case{text-align:center;padding:60px;color:#888;background:#fff;border-radius:8px}

    @media print {
      header,nav,.action-bar{display:none!important}
      body{background:#fff}
      .container{max-width:100%;margin:0;padding:0}
      .letter{box-shadow:none;padding:30px 40px;margin:0}
    }
  </style>
</head>
<body>

<header>
  <h1>🕊️ Human Rights Law <span class="file-tag">FILE: hr_cover_letter.php</span></h1>
  <p>Cover Letter Generator</p>
</header>

<nav>
  <a href="index.php">🏠 Home</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="hr_create.php">New Case</a>
  <a href="hr_checklist.php">Checklist</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">

  <div class="action-bar">
    <a href="human-rights.php" class="btn btn-secondary">← Back to Cases</a>
    <?php if ($case): ?>
      <a href="hr_view.php?id=<?php echo $case['id']; ?>" class="btn btn-secondary">📁 View Case</a>
      <a href="hr_checklist.php?id=<?php echo $case['id']; ?>" class="btn">📋 Checklist</a>
    <?php endif; ?>
    <button onclick="window.print()" class="btn btn-print">🖨️ Print Letter</button>
  </div>

  <?php if (!$case): ?>
    <div class="no-case">
      <p style="font-size:1.1rem;margin-bottom:16px">⚠️ No case selected.</p>
      <p style="margin-bottom:20px">Please select a case to generate a cover letter.</p>
      <a href="human-rights.php" class="btn">View All Cases</a>
    </div>
  <?php else: ?>

  <div class="letter">

    <div class="letter-header">
      <div>
        <div class="firm-name">AEP Legal Platform</div>
        <div class="firm-sub">Human Rights & Public Law Division</div>
        <div class="firm-sub" style="margin-top:6px">
          Email: legal@aep-platform.com &nbsp;|&nbsp; Tel: +234 000 000 0000
        </div>
      </div>
      <div>
        <div class="letter-date"><?php echo $today; ?></div>
        <div class="ref-line">Ref: HR-<?php echo str_pad($case['id'], 4, '0', STR_PAD_LEFT); ?>/<?php echo date('Y'); ?></div>
      </div>
    </div>

    <div class="addressee">
      <p><strong>The Head of Legal / Compliance</strong></p>
      <p><?php echo htmlspecialchars($case['respondent'] ?? '[Respondent Name]'); ?></p>
      <p>[Address Line 1]</p>
      <p>[Address Line 2]</p>
      <p>[City, State]</p>
    </div>

    <div class="letter-subject">
      RE: NOTICE OF HUMAN RIGHTS VIOLATION —
      <?php echo htmlspecialchars(strtoupper($case['title'] ?? 'CASE REFERENCE')); ?>
    </div>

    <div class="letter-body">

      <p>Dear Sir/Madam,</p>

      <p>
        We write on behalf of our client,
        <strong><?php echo htmlspecialchars($case['claimant'] ?? '[Claimant Name]'); ?></strong>
        (hereinafter referred to as "the Claimant"), to formally notify you of a serious
        violation of fundamental human rights, and to demand immediate remedy in accordance
        with applicable law.
      </p>

      <p>
        This letter constitutes a formal <strong>pre-action notice</strong> before the
        commencement of legal proceedings. We urge you to treat this matter with the
        utmost urgency.
      </p>

      <p><strong>1. NATURE OF THE VIOLATION</strong></p>
      <p>
        Our client alleges a violation of the
        <strong><?php echo htmlspecialchars($case['right_violated'] ?? '[Right Violated]'); ?></strong>
        as guaranteed under
        <strong><?php echo htmlspecialchars($case['article_section'] ?? '[Article / Section / Provision]'); ?></strong>.
        <?php if (!empty($case['violation_date'])): ?>
          The violation is alleged to have occurred on or around
          <strong><?php echo htmlspecialchars($case['violation_date']); ?></strong>.
        <?php endif; ?>
      </p>

      <?php if (!empty($case['grounds'])): ?>
      <p><strong>2. GROUNDS OF CLAIM</strong></p>
      <p><?php echo nl2br(htmlspecialchars($case['grounds'])); ?></p>
      <?php endif; ?>

      <?php if (!empty($case['summary'])): ?>
      <p><strong>3. SUMMARY OF FACTS</strong></p>
      <p><?php echo nl2br(htmlspecialchars($case['summary'])); ?></p>
      <?php endif; ?>

      <p><strong><?php echo (!empty($case['grounds'])) ? '4' : '2'; ?>. REMEDY SOUGHT</strong></p>
      <p>
        Our client seeks the following remedy:
        <strong><?php echo htmlspecialchars($case['remedy'] ?? '[Remedy Sought]'); ?></strong>.
        We call upon you to provide a substantive response to this notice within
        <strong>14 days</strong> of receipt of this letter.
      </p>

      <p><strong>NOTICE OF LEGAL PROCEEDINGS</strong></p>
      <p>
        Please be advised that in the absence of a satisfactory response within the
        stipulated timeframe, our client reserves the right to commence formal legal
        proceedings without further notice, including an application to the court for
        appropriate relief. All costs arising from such proceedings may be sought against
        you.
      </p>

      <p>
        We trust that this matter will be resolved amicably and look forward to your
        prompt response.
      </p>

    </div>

    <div class="signature">
      <p>Yours faithfully,</p>
      <div class="sig-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Legal Officer'); ?></div>
      <div class="sig-title">AEP Legal Platform — Human Rights Division</div>
      <p style="margin-top:16px;font-size:0.82rem;color:#888">
        This letter was generated on <?php echo $today; ?> and is confidential.
        It is intended solely for the named recipient.
      </p>
    </div>

  </div>

  <?php endif; ?>
</div>
</body>
</html>