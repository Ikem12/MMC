<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: advice_list.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM legal_advice WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { header('Location: advice_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Legal Advice — <?php echo htmlspecialchars($r['client_name']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',serif;background:#fff;color:#000;font-size:12pt}

/* Screen buttons - hidden on print */
.screen-only{padding:16px 40px;background:#f4f6f9;border-bottom:1px solid #ddd;display:flex;gap:12px}
.btn{padding:8px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;text-decoration:none;display:inline-block;font-family:Arial,sans-serif}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-secondary{background:#6c757d;color:#fff}

/* Page */
.page{max-width:210mm;margin:0 auto;padding:20mm 25mm;min-height:297mm}

/* Letterhead */
.letterhead{text-align:center;border-bottom:3px double #1a3c5e;padding-bottom:16px;margin-bottom:20px}
.firm-name{font-size:20pt;font-weight:bold;color:#1a3c5e;letter-spacing:2px;text-transform:uppercase}
.firm-tagline{font-size:9pt;color:#555;margin-top:4px;font-style:italic}
.firm-contact{font-size:8.5pt;color:#555;margin-top:6px}
.doc-title{text-align:center;margin:18px 0;font-size:13pt;font-weight:bold;text-transform:uppercase;letter-spacing:1px;text-decoration:underline}

/* Meta info */
.meta-table{width:100%;margin-bottom:18px;font-size:10.5pt}
.meta-table td{padding:3px 6px;vertical-align:top}
.meta-table .label{font-weight:bold;width:160px;color:#1a3c5e}

/* Sections */
.section{margin-bottom:16px}
.section-heading{font-size:10.5pt;font-weight:bold;text-transform:uppercase;color:#1a3c5e;border-bottom:1px solid #1a3c5e;padding-bottom:3px;margin-bottom:8px;letter-spacing:0.5px}
.section-body{font-size:11pt;line-height:1.8;text-align:justify;white-space:pre-wrap}

/* Signature */
.signature-block{margin-top:30px;font-size:11pt}
.sig-line{border-top:1px solid #000;width:220px;margin-top:40px;margin-bottom:6px}

/* Disclaimer */
.disclaimer{margin-top:24px;padding:10px 14px;border:1px solid #ccc;background:#fffde7;font-size:9pt;line-height:1.6;font-style:italic}

/* Footer */
.footer{text-align:center;margin-top:30px;padding-top:10px;border-top:1px solid #ccc;font-size:8.5pt;color:#666}

/* Confidential stamp */
.confidential{text-align:center;color:#cc0000;font-weight:bold;font-size:9pt;letter-spacing:2px;margin-bottom:10px;text-transform:uppercase}

/* Badge */
.status-badge{display:inline-block;padding:2px 10px;border:1px solid #1a3c5e;border-radius:3px;font-size:9pt;color:#1a3c5e;font-weight:bold}

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
  <a href="advice_view.php?id=<?php echo $r['id']; ?>" class="btn btn-secondary">← Back to Record</a>
  <a href="advice_list.php" class="btn btn-secondary">📋 All Advice</a>
</div>

<div class="page">

  <!-- CONFIDENTIAL -->
  <div class="confidential">⚖ Strictly Confidential — Legal Professional Privilege ⚖</div>

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

  <!-- DOCUMENT TITLE -->
  <div class="doc-title">Legal Diagnosis & Formal Advice</div>

  <!-- META INFORMATION -->
  <table class="meta-table">
    <tr>
      <td class="label">File Number:</td>
      <td><?php echo htmlspecialchars($r['file_no'] ?: 'N/A'); ?></td>
      <td class="label">Date:</td>
      <td><?php echo date('d F Y', strtotime($r['created_at'])); ?></td>
    </tr>
    <tr>
      <td class="label">Client Name:</td>
      <td><?php echo htmlspecialchars($r['client_name']); ?></td>
      <td class="label">Status:</td>
      <td><span class="status-badge"><?php echo strtoupper($r['status']); ?></span></td>
    </tr>
    <tr>
      <td class="label">Legal Domain:</td>
      <td><?php echo htmlspecialchars($r['legal_domain'] ?: 'N/A'); ?></td>
      <td class="label">Advising Lawyer:</td>
      <td><?php echo htmlspecialchars($r['lawyer_name'] ?: 'N/A'); ?></td>
    </tr>
  </table>

  <!-- FACTS -->
  <div class="section">
    <div class="section-heading">1. Facts Presented By Client</div>
    <div class="section-body"><?php echo nl2br(htmlspecialchars($r['facts'] ?: 'N/A')); ?></div>
  </div>

  <!-- LEGAL ISSUES -->
  <div class="section">
    <div class="section-heading">2. Legal Issues For Determination</div>
    <div class="section-body"><?php echo nl2br(htmlspecialchars($r['legal_issues'] ?: 'N/A')); ?></div>
  </div>

  <!-- APPLICABLE LAW -->
  <div class="section">
    <div class="section-heading">3. Applicable Law</div>
    <div class="section-body"><?php echo nl2br(htmlspecialchars($r['applicable_law'] ?: 'N/A')); ?></div>
  </div>

  <!-- DIAGNOSIS -->
  <div class="section">
    <div class="section-heading">4. Legal Diagnosis</div>
    <div class="section-body"><?php echo nl2br(htmlspecialchars($r['diagnosis'] ?: 'N/A')); ?></div>
  </div>

  <!-- ADVICE -->
  <div class="section">
    <div class="section-heading">5. Formal Legal Advice & Opinion</div>
    <div class="section-body"><?php echo nl2br(htmlspecialchars($r['advice'] ?: 'N/A')); ?></div>
  </div>

  <!-- ACTION PLAN -->
  <div class="section">
    <div class="section-heading">6. Action Plan</div>
    <div class="section-body"><?php echo nl2br(htmlspecialchars($r['action_plan'] ?: 'N/A')); ?></div>
  </div>

  <!-- SIGNATURE -->
  <div class="signature-block">
    <p>Yours faithfully,</p>
    <div class="sig-line"></div>
    <p><strong><?php echo htmlspecialchars($r['lawyer_name'] ?: 'Counsel'); ?></strong></p>
    <p>For: AEP Legal Consultancy</p>
    <p>Date: <?php echo date('d F Y', strtotime($r['created_at'])); ?></p>
  </div>

  <!-- DISCLAIMER -->
  <div class="disclaimer">
    <strong>DISCLAIMER:</strong>
    <?php echo nl2br(htmlspecialchars($r['disclaimer'] ?: 'This advice is confidential.')); ?>
  </div>

  <!-- FOOTER -->
  <div class="footer">
    AEP Legal Consultancy &mdash; Barristers & Solicitors &mdash; Abuja, Nigeria<br/>
    This document is confidential and protected by Legal Professional Privilege.
  </div>

</div>
</body>
</html>