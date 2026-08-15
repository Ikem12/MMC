<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: skeleton_list.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM skeleton_arguments WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { header('Location: skeleton_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Skeleton Argument — <?php echo htmlspecialchars($r['case_title']); ?></title>
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
.letterhead{text-align:center;border-bottom:3px double #1a3c5e;padding-bottom:16px;margin-bottom:20px}
.firm-name{font-size:20pt;font-weight:bold;color:#1a3c5e;letter-spacing:2px;text-transform:uppercase}
.firm-tagline{font-size:9pt;color:#555;margin-top:4px;font-style:italic}
.firm-contact{font-size:8.5pt;color:#555;margin-top:6px}

/* Court heading */
.court-heading{text-align:center;margin-bottom:20px}
.court-name{font-size:12pt;font-weight:bold;text-transform:uppercase;margin-bottom:6px}
.suit-no{font-size:11pt;margin-bottom:10px}

/* Document title */
.doc-title{text-align:center;margin:16px 0;font-size:13pt;font-weight:bold;text-transform:uppercase;letter-spacing:1px;text-decoration:underline}
.doc-subtitle{text-align:center;font-size:10.5pt;margin-bottom:20px;font-style:italic}

/* Meta info */
.meta-table{width:100%;margin-bottom:20px;font-size:10.5pt;border-collapse:collapse}
.meta-table td{padding:4px 8px;vertical-align:top}
.meta-table .label{font-weight:bold;width:160px;color:#1a3c5e}

/* Sections */
.section{margin-bottom:20px}
.section-heading{font-size:11pt;font-weight:bold;text-transform:uppercase;color:#1a3c5e;border-bottom:1.5px solid #1a3c5e;padding-bottom:3px;margin-bottom:10px;letter-spacing:0.5px}
.section-body{font-size:11pt;line-height:1.9;text-align:justify;white-space:pre-wrap}

/* Authorities box */
.authorities-box{border:1px solid #ccc;padding:12px;font-size:10.5pt;line-height:1.8;white-space:pre-wrap;background:#fafafa}

/* Signature */
.signature-block{margin-top:30px;font-size:11pt;line-height:1.8}
.sig-line{border-top:1px solid #000;width:220px;margin-top:40px;margin-bottom:6px}

/* Footer */
.footer{text-align:center;margin-top:30px;padding-top:10px;border-top:1px solid #ccc;font-size:8.5pt;color:#666}

/* Page number */
.page-info{text-align:right;font-size:8.5pt;color:#888;margin-bottom:10px}

@media print{
  .screen-only{display:none!important}
  .page{padding:15mm 20mm;margin:0;max-width:100%}
  body{font-size:11pt}
  .section{page-break-inside:avoid}
}
</style>
</head>
<body>

<!-- SCREEN BUTTONS -->
<div class="screen-only">
  <button onclick="window.print()" class="btn btn-primary">🖨 Print / Save as PDF</button>
  <a href="skeleton_view.php?id=<?php echo $r['id']; ?>" class="btn btn-secondary">← Back to Record</a>
  <a href="skeleton_list.php" class="btn btn-secondary">📋 All Skeletons</a>
</div>

<div class="page">

  <!-- PAGE INFO -->
  <div class="page-info">
    Filed: <?php echo date('d F Y', strtotime($r['created_at'])); ?>
  </div>

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

  <!-- COURT HEADING -->
  <div class="court-heading">
    <div class="court-name"><?php echo htmlspecialchars($r['court'] ?: 'IN THE HIGH COURT OF JUSTICE'); ?></div>
    <div class="court-title"><?php echo htmlspecialchars($r['case_title'] ?? ''); ?></div>
  </div>

  <!-- CASE REFERENCE -->
  <?php if (!empty($r['case_number'])): ?>
  <div style="text-align:right;margin-bottom:12px;font-size:10pt">
    Case No: <strong><?php echo htmlspecialchars($r['case_number']); ?></strong>
  </div>
  <?php endif; ?>

  <!-- INTRODUCTION -->
  <?php if (!empty($r['introduction'])): ?>
  <h2>A. Introduction</h2>
  <p><?php echo nl2br(htmlspecialchars($r['introduction'])); ?></p>
  <?php endif; ?>

  <!-- FACTS -->
  <?php if (!empty($r['facts_summary'])): ?>
  <h2>B. Summary of Facts</h2>
  <p><?php echo nl2br(htmlspecialchars($r['facts_summary'])); ?></p>
  <?php endif; ?>

  <!-- ISSUES -->
  <?php if (!empty($r['issues'])): ?>
  <h2>C. Issues for Determination</h2>
  <p><?php echo nl2br(htmlspecialchars($r['issues'])); ?></p>
  <?php endif; ?>

  <!-- SUBMISSIONS -->
  <?php if (!empty($r['submissions'])): ?>
  <h2>D. Submissions</h2>
  <p><?php echo nl2br(htmlspecialchars($r['submissions'])); ?></p>
  <?php endif; ?>

  <!-- AUTHORITIES -->
  <?php if (!empty($r['authorities'])): ?>
  <h2>E. Authorities</h2>
  <p><?php echo nl2br(htmlspecialchars($r['authorities'])); ?></p>
  <?php endif; ?>

  <!-- CONCLUSION -->
  <?php if (!empty($r['conclusion'])): ?>
  <h2>F. Conclusion</h2>
  <p><?php echo nl2br(htmlspecialchars($r['conclusion'])); ?></p>
  <?php endif; ?>

  <!-- RELIEF SOUGHT -->
  <?php if (!empty($r['relief_sought'])): ?>
  <h2>G. Relief Sought</h2>
  <p><?php echo nl2br(htmlspecialchars($r['relief_sought'])); ?></p>
  <?php endif; ?>

  <div style="margin-top:40px;font-size:9pt;color:#666">
    Submitted by: <?php echo htmlspecialchars($r['lawyer_name'] ?? ''); ?><br/>
    Date: <?php echo date('d F Y'); ?>
  </div>

</body>
</html>
