<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: letter_list.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM draft_letters WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { header('Location: letter_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Letter — <?php echo htmlspecialchars($r['recipient_name']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',serif;background:#fff;color:#000;font-size:12pt}

/* Screen buttons */
.screen-only{padding:16px 40px;background:#f4f6f9;border-bottom:1px solid #ddd;display:flex;gap:12px}
.btn{padding:8px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;text-decoration:none;display:inline-block;font-family:Arial,sans-serif}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-secondary{background:#6c757d;color:#fff}

/* Page */
.page{max-width:210mm;margin:0 auto;padding:20mm 25mm;min-height:297mm}

/* Letterhead */
.letterhead{text-align:center;border-bottom:3px double #1a3c5e;padding-bottom:16px;margin-bottom:24px}
.firm-name{font-size:20pt;font-weight:bold;color:#1a3c5e;letter-spacing:2px;text-transform:uppercase}
.firm-tagline{font-size:9pt;color:#555;margin-top:4px;font-style:italic}
.firm-contact{font-size:8.5pt;color:#555;margin-top:6px}

/* Date & Ref */
.doc-meta{margin-bottom:20px;font-size:11pt;line-height:1.8}

/* Recipient block */
.recipient-block{margin-bottom:20px;font-size:11pt;line-height:1.8}

/* Subject line */
.subject-line{margin-bottom:20px;font-size:11.5pt;font-weight:bold;text-decoration:underline;text-transform:uppercase}

/* Salutation */
.salutation{margin-bottom:16px;font-size:11pt}

/* Body */
.letter-body{font-size:11pt;line-height:1.9;text-align:justify;white-space:pre-wrap;margin-bottom:30px}

/* Signature */
.signature-block{font-size:11pt;line-height:1.8}
.sig-line{border-top:1px solid #000;width:220px;margin-top:50px;margin-bottom:8px}

/* Footer */
.footer{text-align:center;margin-top:40px;padding-top:10px;border-top:1px solid #ccc;font-size:8.5pt;color:#666}

/* Confidential */
.confidential{text-align:right;color:#cc0000;font-weight:bold;font-size:9pt;letter-spacing:1px;margin-bottom:16px;text-transform:uppercase}

@media print{
  .screen-only{display:none!important}
  .page{padding:15mm 20mm;margin:0;max-width:100%}
  body{font-size:11pt}
}
</style>
</head>
<body>

<!-- SCREEN BUTTONS -->
<div class="screen-only">
  <button onclick="window.print()" class="btn btn-primary">🖨 Print / Save as PDF</button>
  <a href="letter_view.php?id=<?php echo $r['id']; ?>" class="btn btn-secondary">← Back to Record</a>
  <a href="letter_list.php" class="btn btn-secondary">📋 All Letters</a>
</div>

<div class="page">

  <!-- LETTERHEAD -->
  <div class="letterhead">
    <div class="firm-name">AEP Legal Consultancy</div>
    <div class="firm-tagline">Barristers & Solicitors | Legal Consultants & Advocates</div>
    <div class="firm-contact">
      📍 No. 1 Justice Close, Abuja, Nigeria &nbsp;|&nbsp;
      📞 +234 800 000 0000 &nbsp;|&nbsp;
      ✉ info@aeplegal.com
    </div>
  </div>

  <!-- CONFIDENTIAL -->
  <div class="confidential">Private & Confidential</div>

  <!-- DATE & REF -->
  <div class="doc-meta">
    <strong>Our Ref:</strong> <?php echo htmlspecialchars($r['ref_no'] ?: 'N/A'); ?><br/>
    <strong>Date:</strong> <?php echo date('d F Y', strtotime($r['created_at'])); ?>
  </div>

  <!-- RECIPIENT -->
  <div class="recipient-block">
    <strong><?php echo htmlspecialchars($r['recipient_name']); ?></strong><br/>
    <?php echo nl2br(htmlspecialchars($r['recipient_address'] ?: '')); ?>
  </div>

  <!-- SUBJECT -->
  <div class="subject-line">
    RE: <?php echo htmlspecialchars($r['subject']); ?>
  </div>

  <!-- SALUTATION -->
  <div class="salutation">
    <?php echo htmlspecialchars($r['salutation'] ?: 'Dear Sir/Madam'); ?>,
  </div>

  <!-- BODY -->
  <div class="letter-body"><?php echo nl2br(htmlspecialchars($r['body'] ?: '')); ?></div>

  <!-- SIGNATURE -->
  <div class="signature-block">
    <div class="sig-line"></div>
    <strong><?php echo htmlspecialchars($r['signatory_name'] ?: 'Counsel'); ?></strong><br/>
    <?php echo htmlspecialchars($r['signatory_title'] ?: ''); ?><br/>
    For: <strong>AEP Legal Consultancy</strong>
  </div>

  <!-- FOOTER -->
  <div class="footer">
    AEP Legal Consultancy &mdash; Barristers & Solicitors &mdash; Abuja, Nigeria<br/>
    This letter is confidential and intended solely for the addressee.
  </div>

</div>
</body>
</html>