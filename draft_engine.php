<?php
// FILE: draft_engine.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/claude_api.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$today   = date('d F Y');
$domains = [
    'human_rights' => 'Human Rights Cases',
    'tort'         => 'Tort Law Cases',
    'admin_law'    => 'Admin Law Cases',
    'oil_gas'      => 'Oil & Gas Cases',
];

$tables = [
    'human_rights' => 'human_rights_cases',
    'tort'         => 'tort_cases',
    'admin_law'    => 'admin_law_cases',
    'oil_gas'      => 'oil_gas_cases',
];

$domain   = $_REQUEST['domain'] ?? '';
$case_id  = (int)($_REQUEST['id'] ?? 0);
$doc_type = $_REQUEST['doc_type'] ?? 'demand_letter';
$use_ai   = isset($_POST['use_ai']);
$case     = null;

if ($domain && $case_id && isset($tables[$domain])) {
    $table = $tables[$domain];
    $stmt  = $pdo->prepare("SELECT * FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $case_id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Claude AI draft (on demand)
$ai_draft = null;
$ai_enabled = claude_api_key() !== null;
if ($case && $use_ai) {
    $prompt = claude_draft_prompt($domain, $doc_type, $case);
    $ai_draft = claude_ask($prompt['system'], $prompt['user'], 'claude-3-5-sonnet-20241022', 2000);
}

$docTypes = [
    'demand_letter'   => '📩 Demand Letter',
    'legal_opinion'   => '⚖️ Legal Opinion',
    'court_filing'    => '🏛️ Court Filing Notice',
    'settlement'      => '🤝 Settlement Proposal',
    'cease_desist'    => '🛑 Cease & Desist',
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Draft Engine — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#2c3e50;color:#fff;padding:24px 20px;text-align:center}
    header h1{font-size:1.6rem;margin-bottom:4px}
    header p{font-size:0.9rem;opacity:0.85}
    .file-tag{font-size:11px;background:#fff;color:#2c3e50;padding:3px 8px;border-radius:4px;margin-left:10px;vertical-align:middle}
    nav{background:#1a252f;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
    nav a:hover{background:rgba(255,255,255,0.3)}
    .container{max-width:960px;margin:30px auto;padding:0 18px}
    .selector-card{background:#fff;border-radius:8px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:24px}
    .selector-card h2{font-size:1.1rem;color:#2c3e50;margin-bottom:16px;border-bottom:2px solid #2c3e50;padding-bottom:8px}
    .form-row{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:14px;align-items:end}
    .form-group{margin-bottom:0}
    label{display:block;font-size:0.82rem;font-weight:bold;margin-bottom:5px;color:#555;text-transform:uppercase;letter-spacing:0.5px}
    select,input{width:100%;padding:9px 12px;border:1px solid #ccd;border-radius:4px;font-size:0.9rem;font-family:inherit}
    select:focus,input:focus{outline:none;border-color:#2c3e50}
    .btn{display:inline-block;padding:9px 20px;background:#2c3e50;color:#fff;border:none;border-radius:4px;font-size:0.9rem;cursor:pointer;text-decoration:none;white-space:nowrap}
    .btn:hover{background:#1a252f}
    .btn-print{background:#28a745}
    .btn-print:hover{background:#1e7e34}
    .btn-secondary{background:#6c757d}
    .btn-secondary:hover{background:#495057}
    .action-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}

    /* ── Sticky Toolbar ─────────────────────────────────── */
    .toolbar{position:sticky;top:0;z-index:100;background:#2c3e50;padding:8px 18px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,0.18)}
    .toolbar .tb-btn{padding:6px 14px;border:none;border-radius:4px;font-size:0.82rem;font-weight:bold;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background 0.15s,transform 0.1s;white-space:nowrap}
    .toolbar .tb-btn:active{transform:scale(0.96)}
    .tb-reset{background:#dc3545;color:#fff}
    .tb-reset:hover{background:#b02a37}
    .tb-details{background:#0d6efd;color:#fff}
    .tb-details:hover{background:#0a58ca}
    .tb-instructions{background:#198754;color:#fff}
    .tb-instructions:hover{background:#146c43}
    .tb-outputs{background:#fd7e14;color:#fff}
    .tb-outputs:hover{background:#d86309}
    .tb-summary{background:#6f42c1;color:#fff}
    .tb-summary:hover{background:#59359a}
    .tb-drafts{background:#20c997;color:#fff}
    .tb-drafts:hover{background:#19a47c}
    .tb-analysis{background:#ffc107;color:#212529}
    .tb-analysis:hover{background:#e0a800}

    /* ── Instruction Field ─────────────────────────────── */
    .instruction-box{background:#f0fff4;border:1px solid #28a745;border-radius:8px;padding:18px 20px;margin-bottom:20px;display:none}
    .instruction-box h4{font-size:0.9rem;color:#155724;margin-bottom:10px;font-weight:bold}
    .instruction-box textarea{width:100%;padding:10px;border:1px solid #b2dfdb;border-radius:4px;font-size:0.88rem;font-family:inherit;resize:vertical;min-height:80px}
    .instruction-box .ib-actions{display:flex;gap:8px;margin-top:10px}
    .instruction-box .ib-btn{padding:6px 14px;border:none;border-radius:4px;font-size:0.82rem;cursor:pointer;font-weight:bold}
    .ib-save{background:#198754;color:#fff}
    .ib-clear{background:#6c757d;color:#fff}

    /* Document */
    .document{background:#fff;border-radius:8px;padding:50px 60px;box-shadow:0 2px 12px rgba(0,0,0,0.10);margin-bottom:30px}
    .doc-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:36px;padding-bottom:18px;border-bottom:3px solid #2c3e50}
    .firm-name{font-size:1.3rem;font-weight:bold;color:#2c3e50}
    .firm-sub{font-size:0.82rem;color:#666;margin-top:4px}
    .doc-date{font-size:0.88rem;color:#444;text-align:right}
    .doc-ref{font-size:0.82rem;color:#888;text-align:right;margin-top:4px}
    .doc-type-tag{display:inline-block;background:#2c3e50;color:#fff;padding:4px 14px;border-radius:12px;font-size:0.8rem;margin-bottom:20px}
    .addressee{margin-bottom:28px}
    .addressee p{font-size:0.9rem;line-height:1.8;color:#333}
    .doc-subject{font-size:1rem;font-weight:bold;color:#2c3e50;margin-bottom:22px;padding:10px 0;border-bottom:1px solid #eee}
    .doc-body p{font-size:0.92rem;line-height:1.9;color:#333;margin-bottom:16px}
    .doc-body strong{color:#222}
    .doc-body ul{margin:10px 0 16px 24px}
    .doc-body ul li{font-size:0.92rem;line-height:1.8;color:#333}
    .signature{margin-top:40px;padding-top:20px;border-top:1px solid #eee}
    .signature p{font-size:0.92rem;line-height:1.8;color:#333}
    .sig-name{font-size:1rem;font-weight:bold;color:#2c3e50;margin-top:30px}
    .sig-title{font-size:0.85rem;color:#666}
    .no-doc{text-align:center;padding:50px;color:#888;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07)}
    .domain-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:16px}
    .domain-card{background:#f4f6f9;border-radius:6px;padding:16px;text-align:center;border:2px solid #e0e0e0;cursor:pointer;text-decoration:none;color:#333}
    .domain-card:hover{border-color:#2c3e50;background:#eef0f3}
    .domain-card .icon{font-size:1.8rem;margin-bottom:6px}
    .domain-card .name{font-size:0.85rem;font-weight:bold}
    .btn-ai{background:#7c3aed;color:#fff;border:none;padding:10px 22px;border-radius:5px;font-size:0.9rem;cursor:pointer;font-family:inherit}
    .btn-ai:hover{background:#6d28d9}
    .ai-document{background:#fff;border-radius:8px;padding:50px 60px;box-shadow:0 2px 12px rgba(0,0,0,0.10);margin-bottom:30px;border-top:4px solid #7c3aed}
    .ai-document h2{font-size:1rem;color:#7c3aed;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #ede9fe}
    .ai-body{font-size:0.92rem;line-height:1.9;color:#333;white-space:pre-wrap}
    .ai-error{background:#fef2f2;border-radius:6px;padding:14px 18px;font-size:0.88rem;color:#dc2626;border:1px solid #fecaca;margin-bottom:14px}
    .ai-disabled{background:#f3f0ff;border-radius:6px;padding:14px 18px;font-size:0.88rem;color:#7c3aed;border:1px dashed #c4b5fd;margin-bottom:14px}
    .ai-spinner{display:none;font-size:0.88rem;color:#7c3aed;margin-left:10px}

    @media print {
      header,nav,.selector-card,.action-bar{display:none!important}
      body{background:#fff}
      .container{max-width:100%;margin:0;padding:0}
      .document{box-shadow:none;padding:30px 40px;margin:0}
    }
  </style>
</head>
<body>

<header>
  <h1>✍️ Draft Engine <span class="file-tag">FILE: draft_engine.php</span></h1>
  <p>Automated Legal Document Drafting — All Domains</p>
</header>

<nav>
  <a href="index.php">🏠 Home</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="tort.php">Tort Law</a>
  <a href="logout.php">Logout</a>
</nav>

<!-- ── Sticky Toolbar ─────────────────────────────────────────── -->
<div class="toolbar">
  <button class="tb-btn tb-reset" onclick="doReset()" title="Clear the current case selection">🔄 Reset</button>
  <a class="tb-btn tb-details" href="#sec-case-details" onclick="smoothTo(event,'sec-case-details')" title="Jump to Case selector / Details">📁 Case Details</a>
  <button class="tb-btn tb-instructions" onclick="toggleInstructions()" title="Open instruction / notes field">📝 Instructions</button>
  <a class="tb-btn tb-outputs" href="#sec-outputs" onclick="smoothTo(event,'sec-outputs')" title="Jump to Document Outputs">📄 Outputs</a>
  <a class="tb-btn tb-summary" href="#sec-summary" onclick="smoothTo(event,'sec-summary')" title="Jump to Case Summary">📋 Summary</a>
  <a class="tb-btn tb-drafts" href="#sec-draft" onclick="smoothTo(event,'sec-draft')" title="Jump to Document Draft">✍️ Drafts</a>
  <a class="tb-btn tb-analysis" href="analysis_engine.php<?php echo ($domain && $case_id) ? '?domain='.urlencode($domain).'&id='.$case_id : ''; ?>" title="Open Analysis Engine for this case">🔍 Analysis</a>
</div>

<div class="container">

  <!-- Selector -->
  <div class="selector-card" id="sec-case-details">
    <h2>✍️ Generate Legal Document</h2>
    <form method="GET">
      <div class="form-row">
        <div class="form-group">
          <label>Domain</label>
          <select name="domain" onchange="this.form.submit()">
            <option value="">— Select Domain —</option>
            <?php foreach ($domains as $key => $label): ?>
              <option value="<?php echo $key; ?>" <?php echo $domain===$key?'selected':''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Case ID</label>
          <input type="number" name="id" value="<?php echo $case_id ?: ''; ?>" placeholder="e.g. 1" min="1"/>
        </div>
        <div class="form-group">
          <label>Document Type</label>
          <select name="doc_type">
            <?php foreach ($docTypes as $key => $label): ?>
              <option value="<?php echo $key; ?>" <?php echo $doc_type===$key?'selected':''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <button type="submit" class="btn">Generate →</button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($case): ?>

  <!-- ── Instruction Field ─────────────────────────────────────── -->
  <div class="instruction-box" id="instruction-box">
    <h4>📝 Case Instructions / Notes</h4>
    <textarea id="instruction-text" placeholder="Enter special instructions, drafting notes, or context for this document…"></textarea>
    <div class="ib-actions">
      <button class="ib-btn ib-save" onclick="saveInstructions()">💾 Save Note</button>
      <button class="ib-btn ib-clear" onclick="clearInstructions()">🗑 Clear</button>
    </div>
  </div>

  <div class="action-bar" id="sec-outputs">
    <button onclick="window.print()" class="btn btn-print">🖨️ Print Document</button>
    <?php if ($ai_enabled): ?>
    <form method="POST" style="display:inline" onsubmit="document.getElementById('draft-spin').style.display='inline'">
      <input type="hidden" name="domain" value="<?php echo htmlspecialchars($domain); ?>">
      <input type="hidden" name="id" value="<?php echo $case_id; ?>">
      <input type="hidden" name="doc_type" value="<?php echo htmlspecialchars($doc_type); ?>">
      <button type="submit" name="use_ai" value="1" class="btn-ai">🤖 AI-Generated Draft</button>
      <span id="draft-spin" class="ai-spinner">Generating… (10–20 seconds)</span>
    </form>
    <?php endif; ?>
    <a href="draft_engine.php" class="btn btn-secondary">← New Document</a>
  </div>

  <?php if ($ai_draft): ?>
    <div class="ai-document" id="sec-draft">
      <h2>🤖 Claude AI — <?php echo $docTypes[$doc_type] ?? 'Legal Document'; ?></h2>
      <?php if (!$ai_draft['ok']): ?>
        <div class="ai-error">⚠️ AI Error: <?php echo htmlspecialchars($ai_draft['error']); ?></div>
      <?php else: ?>
        <div class="ai-body"><?php echo nl2br(htmlspecialchars($ai_draft['text'])); ?></div>
      <?php endif; ?>
    </div>
  <?php else: ?>
  <div class="document" id="sec-draft">

    <div class="doc-header" id="sec-summary">
      <div>
        <div class="firm-name">AEP Legal Platform</div>
        <div class="firm-sub"><?php echo $domains[$domain] ?? 'Legal Division'; ?></div>
        <div class="firm-sub" style="margin-top:5px">Email: legal@aep-platform.com &nbsp;|&nbsp; Tel: +234 000 000 0000</div>
      </div>
      <div>
        <div class="doc-date"><?php echo $today; ?></div>
        <div class="doc-ref">Ref: AEP-<?php echo strtoupper(substr($domain,0,3)); ?>-<?php echo str_pad($case_id,4,'0',STR_PAD_LEFT); ?>/<?php echo date('Y'); ?></div>
      </div>
    </div>

    <div class="doc-type-tag"><?php echo $docTypes[$doc_type] ?? 'Legal Document'; ?></div>

    <div class="addressee">
      <p><strong><?php echo htmlspecialchars($case['respondent'] ?? $case['defendant'] ?? '[Respondent / Defendant]'); ?></strong></p>
      <p>[Address Line 1]</p>
      <p>[Address Line 2]</p>
      <p>[City, State]</p>
    </div>

    <div class="doc-subject">
      RE: <?php echo htmlspecialchars(strtoupper($docTypes[$doc_type] ?? 'LEGAL NOTICE')); ?>
      — <?php echo htmlspecialchars(strtoupper($case['title'] ?? 'CASE REFERENCE')); ?>
    </div>

    <div class="doc-body">
      <p>Dear Sir/Madam,</p>

      <p>
        We write on behalf of our client,
        <strong><?php echo htmlspecialchars($case['claimant'] ?? '[Claimant]'); ?></strong>,
        in connection with the above-referenced matter. This
        <strong><?php echo $docTypes[$doc_type] ?? 'document'; ?></strong>
        is issued formally and requires your immediate attention.
      </p>

      <?php if ($doc_type === 'demand_letter'): ?>
        <p><strong>DEMAND</strong></p>
        <p>
          Our client formally demands that you immediately remedy the situation arising from
          <strong><?php echo htmlspecialchars($case['title']); ?></strong>.
          Failure to comply within <strong>14 days</strong> of this letter will result in
          formal legal proceedings being instituted against you without further notice.
        </p>

      <?php elseif ($doc_type === 'legal_opinion'): ?>
        <p><strong>LEGAL OPINION</strong></p>
        <p>
          Having reviewed the facts and circumstances of this matter, it is our considered
          legal opinion that our client has a strong and meritorious claim arising from the
          facts herein. We advise that immediate steps be taken to resolve this matter.
        </p>

      <?php elseif ($doc_type === 'court_filing'): ?>
        <p><strong>NOTICE OF COURT FILING</strong></p>
        <p>
          Please be advised that our client has instructed us to commence formal court
          proceedings in respect of this matter. All relevant processes will be served on
          you in due course through the appropriate court channels.
        </p>

      <?php elseif ($doc_type === 'settlement'): ?>
        <p><strong>SETTLEMENT PROPOSAL</strong></p>
        <p>
          Without prejudice to our client's full legal rights, our client is willing to
          explore an amicable resolution of this matter. We invite you to respond with
          your settlement proposals within <strong>21 days</strong> of this letter.
        </p>

      <?php elseif ($doc_type === 'cease_desist'): ?>
        <p><strong>CEASE & DESIST NOTICE</strong></p>
        <p>
          You are hereby formally directed to <strong>immediately cease and desist</strong>
          from any further conduct that gives rise to the claims described herein.
          Continued conduct will result in immediate legal action.
        </p>
      <?php endif; ?>

      <?php if (!empty($case['summary'])): ?>
        <p><strong>BACKGROUND</strong></p>
        <p><?php echo nl2br(htmlspecialchars($case['summary'])); ?></p>
      <?php endif; ?>

      <p>
        We look forward to your prompt response and trust that this matter can be
        resolved without the need for further legal action.
      </p>
    </div>

    <div class="signature">
      <p>Yours faithfully,</p>
      <div class="sig-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Legal Officer'); ?></div>
      <div class="sig-title">AEP Legal Platform — <?php echo $domains[$domain] ?? 'Legal Division'; ?></div>
      <p style="margin-top:16px;font-size:0.82rem;color:#888">
        Generated on <?php echo $today; ?>. This document is confidential and intended solely for the named recipient.
      </p>
    </div>

  </div>
  <?php endif; // end ai_draft else (template document) ?>

  <?php else: ?>
    <div class="no-doc">
      <p style="font-size:2rem;margin-bottom:16px">✍️</p>
      <p style="font-size:1.1rem;margin-bottom:10px;font-weight:bold">Select a domain, case ID and document type above</p>
      <p style="font-size:0.9rem;color:#aaa">The engine will automatically generate a professional legal document from your case data.</p>
    </div>
  <?php endif; ?>

</div>
</body>
</html>