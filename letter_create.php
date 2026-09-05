<?php
require_once __DIR__ . '/phase2.php';
p2_start();
$pdo = p2_db();

function p5_workspace_text(array $input, string $field, int $limit = 12000): string {
    return substr(trim((string)($input[$field] ?? '')), 0, $limit);
}

function p5_workspace_analysis(array $workspace, ?array $matter): array {
    $facts = $workspace['facts'] !== '' ? $workspace['facts'] : trim((string)($matter['description'] ?? ''));
    $counsel = analyseMatter(array_merge($matter ?? [], ['description' => $facts, 'summary' => $facts]));
    $issues = [];
    $risks = ['Treat this as a drafting aid only; verify facts, law, procedure and authority before issue.'];
    $strategy = ['Use verified facts, state a clear requested outcome and keep the response proportionate.'];

    $issues[] = $facts !== '' ? 'Facts have been supplied for review.' : 'No factual summary is supplied; identify the material chronology and source documents.';
    $issues[] = $workspace['instructions'] !== '' ? 'Instructions identify the requested focus.' : 'Clarify the client objective, intended recipient and response sought.';
    if ($workspace['opponent_letter'] !== '') {
        $issues[] = 'The opponent correspondence should be answered point by point against the verified record.';
        $strategy[] = 'Address the opponent letter only where the position and supporting record have been confirmed.';
    } else {
        $risks[] = 'No opponent letter text is available; do not assume the other side’s position or concessions.';
    }
    if (!$matter) {
        $risks[] = 'This draft is not linked to a matter and will not have a matter record to support its context.';
    } else {
        $strategy[] = 'Keep the draft aligned with matter ' . $matter['reference'] . ' and its recorded scope.';
    }
    if ($facts === '') $strategy[] = 'Obtain and record the factual chronology before relying on the generated text.';

    return [
        'issues' => array_values(array_unique(array_merge($issues, identifyIssues($counsel)))),
        'law' => identifyApplicableLaw($counsel),
        'evidence' => (array)($counsel['Evidence'] ?? []),
        'risks' => array_values(array_unique(array_merge($risks, assessRisks($counsel)))),
        'strategy' => array_values(array_unique(array_merge($strategy, (array)($counsel['Strategy'] ?? [])))),
        'facts' => $facts,
        'counsel' => $counsel,
    ];
}

function p5_workspace_draft(array $letter, array $workspace, ?array $matter): array {
    $analysis = p5_workspace_analysis($workspace, $matter);
    $analysis['generated_output'] = generateLetter($analysis['counsel'], $matter ?? [], $workspace['instructions']);
    $subject = $letter['subject'] !== '' ? $letter['subject'] : 'RE: ' . ($matter['reference'] ?? 'Correspondence');
    $salutation = $letter['salutation'] !== '' ? $letter['salutation'] : 'Dear Sir/Madam';
    $facts = $analysis['facts'] !== '' ? $analysis['facts'] : '[Insert verified factual background.]';
    $purpose = $workspace['instructions'] !== '' ? $workspace['instructions'] : '[Insert the confirmed purpose of this correspondence.]';
    $opponent = $workspace['opponent_letter'] !== '' ? "\n\nWe note your correspondence raises the following point(s):\n" . $workspace['opponent_letter'] : '';
    $body = $salutation . ",\n\n";
    $body .= "We write regarding {$subject}.\n\n";
    $body .= "Our current instructions are: {$purpose}\n\n";
    $body .= "The verified factual position presently relied upon is:\n{$facts}" . $opponent . "\n\n";
    $body .= "Please provide your substantive response, or the requested information, by [insert date]. We reserve all rights.\n\n";
    $body .= "Yours faithfully,";
    return [$subject, $body, $analysis];
}

function p5_workspace_improve(string $body): string {
    $body = trim(str_replace(["\r\n", "\r"], "\n", $body));
    $body = preg_replace("/[ \t]+\n/", "\n", $body) ?? $body;
    $body = preg_replace("/\n{3,}/", "\n\n", $body) ?? $body;
    if ($body === '') return '';
    if (!str_contains($body, 'Please ensure that any response is supported by the relevant documents.')) {
        $marker = "\n\nYours faithfully,";
        $addition = "\n\nPlease ensure that any response is supported by the relevant documents.";
        $body = str_contains($body, $marker) ? str_replace($marker, $addition . $marker, $body) : $body . $addition;
    }
    return $body;
}

function p5_workspace_form(array $input = []): array {
    return [
        'ref_no' => p5_workspace_text($input, 'ref_no', 100),
        'letter_type' => p5_workspace_text($input, 'letter_type', 100) ?: 'General correspondence',
        'recipient_name' => p5_workspace_text($input, 'recipient_name', 255),
        'recipient_address' => p5_workspace_text($input, 'recipient_address', 2000),
        'recipient_email' => p5_workspace_text($input, 'recipient_email', 255),
        'subject' => p5_workspace_text($input, 'subject', 255),
        'salutation' => p5_workspace_text($input, 'salutation', 100) ?: 'Dear Sir/Madam',
        'body' => p5_workspace_text($input, 'body'),
        'signatory_name' => p5_workspace_text($input, 'signatory_name', 255),
        'signatory_title' => p5_workspace_text($input, 'signatory_title', 255),
        'status' => p5_workspace_text($input, 'status', 20) ?: 'draft',
        'matter_id' => (int)($input['matter_id'] ?? 0),
        'client_id' => (int)($input['client_id'] ?? 0),
        'practice_area' => p5_workspace_text($input, 'practice_area', 100),
        'direction' => p5_workspace_text($input, 'direction', 20) ?: 'outgoing',
        'delivery_method' => p5_workspace_text($input, 'delivery_method', 20) ?: 'email',
        'response_due_on' => p5_workspace_text($input, 'response_due_on', 10),
        'requires_response' => isset($input['requires_response']) ? 1 : 0,
        'bundle_reference' => p5_workspace_text($input, 'bundle_reference', 100),
        'document_id' => (int)($input['document_id'] ?? 0),
        'attachment_label' => p5_workspace_text($input, 'attachment_label', 255),
        'attachment_reference' => p5_workspace_text($input, 'attachment_reference', 500),
        'instructions' => p5_workspace_text($input, 'instructions'),
        'facts' => p5_workspace_text($input, 'facts'),
        'opponent_letter' => p5_workspace_text($input, 'opponent_letter'),
    ];
}

function p5_workspace_validate(PDO $pdo, array &$form, bool $saving): array {
    $errors = [];
    if (!in_array($form['status'], ['draft', 'final', 'sent'], true)) $errors[] = 'Select a valid status.';
    if (!in_array($form['direction'], ['outgoing', 'incoming'], true)) $errors[] = 'Select a valid direction.';
    if (!in_array($form['delivery_method'], ['email', 'post', 'hand', 'portal'], true)) $errors[] = 'Select a valid delivery method.';
    if ($form['recipient_email'] !== '' && !filter_var($form['recipient_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid recipient email address.';
    if ($form['response_due_on'] !== '' && !p2_valid_date($form['response_due_on'])) $errors[] = 'Response due date must be valid.';
    $matter = $form['matter_id'] ? p2_find_matter($pdo, $form['matter_id']) : null;
    if ($form['matter_id'] && !$matter) $errors[] = 'Select a valid matter.';
    if ($matter) {
        $form['client_id'] = (int)$matter['client_id'];
        $form['practice_area'] = (string)$matter['practice_area'];
    } elseif ($form['client_id']) {
        $client = $pdo->prepare('SELECT 1 FROM p2_clients WHERE id=?');
        $client->execute([$form['client_id']]);
        if (!$client->fetchColumn()) $errors[] = 'Select a valid client.';
    }
    if ($form['document_id']) {
        $document = $pdo->prepare('SELECT matter_id FROM p2_documents WHERE id=?');
        $document->execute([$form['document_id']]);
        $documentMatter = $document->fetchColumn();
        if ($documentMatter === false || ($form['matter_id'] && (int)$documentMatter !== $form['matter_id'])) $errors[] = 'Select a document linked to this matter.';
    }
    if ($saving) {
        if ($form['recipient_name'] === '') $errors[] = 'Recipient name is required.';
        if ($form['subject'] === '') $errors[] = 'Subject is required.';
        if ($form['body'] === '') $errors[] = 'Generate or enter a letter body before saving.';
    }
    return [$errors, $matter];
}

$errors = [];
$analysis = null;
$form = p5_workspace_form(['matter_id' => (int)($_GET['matter_id'] ?? 0)]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    p2_check_csrf();
    $form = p5_workspace_form($_POST);
    $action = (string)($_POST['action'] ?? 'save');
    if ($action === 'export_word') {
        if ($form['body'] === '') $errors[] = 'Generate or enter a draft before exporting it.';
        else {
            header('Content-Type: application/msword; charset=UTF-8');
            header('Content-Disposition: attachment; filename="aep-preliminary-letter.doc"');
            echo '<!doctype html><html><head><meta charset="utf-8"></head><body><pre style="white-space:pre-wrap;font-family:Calibri,Arial,sans-serif">' . p2_h($form['body']) . '</pre></body></html>';
            exit;
        }
    }
    [$errors, $matter] = p5_workspace_validate($pdo, $form, in_array($action, ['save', 'save_to_matter'], true));
    $workspace = ['instructions' => $form['instructions'], 'facts' => $form['facts'], 'opponent_letter' => $form['opponent_letter']];
    $analysis = p5_workspace_analysis($workspace, $matter);
    if (!$errors && $action === 'generate') {
        [$form['subject'], $form['body'], $analysis] = p5_workspace_draft($form, $workspace, $matter);
    } elseif (!$errors && $action === 'improve') {
        if ($form['body'] === '') $errors[] = 'Generate or enter a letter before improving it.';
        else $form['body'] = p5_workspace_improve($form['body']);
    } elseif (!$errors && in_array($action, ['save', 'save_to_matter'], true)) {
        if ($action === 'save_to_matter' && !$matter) $errors[] = 'Select a matter before saving this draft to its document library.';
        if (!$errors) {
            try {
                $pdo->beginTransaction();
                if ($form['ref_no'] === '') {
                    $number = (int)$pdo->query("SELECT COUNT(*) + 1 FROM p5_correspondence WHERE correspondence_reference LIKE 'AEP-COR-" . date('Y') . "-%'")->fetchColumn();
                    $form['ref_no'] = sprintf('AEP-COR-%s-%04d', date('Y'), $number);
                }
                $letter = array_intersect_key($form, array_flip(['ref_no', 'letter_type', 'recipient_name', 'recipient_address', 'recipient_email', 'subject', 'salutation', 'body', 'signatory_name', 'signatory_title', 'status']));
                $insert = $pdo->prepare('INSERT INTO draft_letters (ref_no,letter_type,recipient_name,recipient_address,recipient_email,subject,salutation,body,signatory_name,signatory_title,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                $insert->execute([$letter['ref_no'], $letter['letter_type'], $letter['recipient_name'], $letter['recipient_address'], $letter['recipient_email'], $letter['subject'], $letter['salutation'], $letter['body'], $letter['signatory_name'], $letter['signatory_title'], $letter['status']]);
                $letterId = (int)$pdo->lastInsertId();
                $context = array_intersect_key($form, array_flip(['matter_id', 'client_id', 'practice_area', 'direction', 'delivery_method', 'response_due_on', 'requires_response', 'bundle_reference']));
                $context['quality_score'] = p5_quality_score($letter, $context);
                $review = 'Local workspace analysis — Issues: ' . implode(' ', $analysis['issues']) . ' Risks: ' . implode(' ', $analysis['risks']) . ' Strategy: ' . implode(' ', $analysis['strategy']);
                $insert = $pdo->prepare('INSERT INTO p5_correspondence (letter_id,matter_id,client_id,practice_area,correspondence_reference,direction,delivery_method,response_due_on,requires_response,bundle_reference,ai_analysis,quality_score,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $insert->execute([$letterId, $context['matter_id'] ?: null, $context['client_id'] ?: null, $context['practice_area'] ?: null, $letter['ref_no'], $context['direction'], $context['delivery_method'], $context['response_due_on'] ?: null, $context['requires_response'], $context['bundle_reference'] ?: null, $review, $context['quality_score'], p2_user_id()]);
                $snapshot = json_encode(['letter' => $letter, 'context' => $context, 'workspace' => $workspace], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $pdo->prepare('INSERT INTO p5_letter_versions (letter_id,version_no,snapshot,note,created_by) VALUES (?,?,?,?,?)')->execute([$letterId, 1, $snapshot, 'Initial workspace draft', p2_user_id()]);
                $check = $pdo->prepare('INSERT INTO p5_letter_checklist (letter_id,item,required) VALUES (?,?,1)');
                foreach (['Recipient and address checked', 'Matter reference checked', 'Subject and factual context checked', 'Signatory and delivery checked'] as $item) $check->execute([$letterId, $item]);
                if ($form['document_id'] || $form['attachment_label'] !== '' || $form['attachment_reference'] !== '') {
                    $pdo->prepare('INSERT INTO p5_letter_attachments (letter_id,label,document_id,file_reference) VALUES (?,?,?,?)')->execute([$letterId, $form['attachment_label'] ?: 'Linked document', $form['document_id'] ?: null, $form['attachment_reference'] ?: null]);
                }
                if ($action === 'save_to_matter') {
                    $document = $pdo->prepare('INSERT INTO p2_documents (matter_id,title,document_type,source_table,source_id,content,created_by) VALUES (?,?,?,?,?,?,?)');
                    $document->execute([$form['matter_id'], 'Correspondence draft — ' . $letter['ref_no'], 'correspondence', 'draft_letters', $letterId, $letter['body'], p2_user_id()]);
                    $documentId = (int)$pdo->lastInsertId();
                    $pdo->prepare('INSERT OR IGNORE INTO p2_document_links (document_id,matter_id) VALUES (?,?)')->execute([$documentId, $form['matter_id']]);
                    p5_add_timeline($pdo, $letterId, 'saved_to_matter', 'Generated draft saved to the matter document library.');
                }
                p5_add_timeline($pdo, $letterId, 'created', 'Correspondence drafted in the AI legal correspondence workspace.');
                if ($form['matter_id']) p2_log('correspondence.created', 'Drafted correspondence ' . $letter['ref_no'], $form['matter_id'], ['letter_id' => $letterId]);
                $pdo->commit();
                p2_redirect('letter_view.php?id=' . $letterId, $action === 'save_to_matter' ? 'Correspondence and generated draft saved to the matter document library.' : 'Correspondence saved to the workbench.');
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $errors[] = 'The correspondence could not be saved. Please review the details and try again.';
            }
        }
    } elseif (!$errors && $action !== 'analyse') {
        $errors[] = 'Select a valid workspace action.';
    }
}

$matters = p2_matter_options($pdo);
$clients = $pdo->query('SELECT id,name FROM p2_clients ORDER BY name')->fetchAll();
$documents = $pdo->query('SELECT d.id,d.title,d.matter_id,m.reference FROM p2_documents d LEFT JOIN p2_matters m ON m.id=d.matter_id ORDER BY d.created_at DESC LIMIT 100')->fetchAll();
$selectedMatter = $form['matter_id'] ? p2_find_matter($pdo, $form['matter_id']) : null;
$analysis ??= p5_workspace_analysis(['instructions' => $form['instructions'], 'facts' => $form['facts'], 'opponent_letter' => $form['opponent_letter']], $selectedMatter);
$sourceMaterials = $selectedMatter ? p6_source_materials($pdo, (int)$selectedMatter['id']) : [];
$qualityScore = p5_quality_score($form, $form);

p2_page('AI legal correspondence workspace', 'letter_list.php', function () use ($errors, $form, $analysis, $matters, $clients, $documents, $sourceMaterials, $qualityScore) { ?>
<div class="toolbar"><div><h1>AI Legal Correspondence Workspace</h1><p class="subhead">Prepare a matter-linked draft with deterministic local analysis. Review all facts and legal content before issue.</p></div><a class="button secondary" href="letter_list.php">All correspondence</a></div>
<?php if ($errors): ?><div class="flash error"><?= p2_h(implode(' ', $errors)) ?></div><?php endif; ?>
<form method="post" class="letter-workspace">
 <input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>">
 <section class="card workspace-context"><div class="form-grid">
  <label>Matter <select name="matter_id"><option value="">Unlinked correspondence</option><?php foreach ($matters as $matter): ?><option value="<?= (int)$matter['id'] ?>" <?= $form['matter_id'] === (int)$matter['id'] ? 'selected' : '' ?>><?= p2_h($matter['reference'] . ' — ' . $matter['title']) ?></option><?php endforeach; ?></select></label>
  <label>Client <select name="client_id"><option value="">Use linked matter client</option><?php foreach ($clients as $client): ?><option value="<?= (int)$client['id'] ?>" <?= $form['client_id'] === (int)$client['id'] ? 'selected' : '' ?>><?= p2_h($client['name']) ?></option><?php endforeach; ?></select></label>
  <label>Template <select id="template"><option value="">Use workspace draft</option><option value="general">General correspondence</option><option value="acknowledgement">Acknowledgement</option><option value="information">Request for information</option><option value="response">Response to correspondence</option><option value="reminder">Reminder</option></select></label>
  <label>Letter type <input name="letter_type" id="letter_type" value="<?= p2_h($form['letter_type']) ?>" maxlength="100"></label>
  <label>Recipient name * <input name="recipient_name" value="<?= p2_h($form['recipient_name']) ?>" maxlength="255"></label>
  <label>Recipient email <input name="recipient_email" type="email" value="<?= p2_h($form['recipient_email']) ?>" maxlength="255"></label>
  <label class="full">Subject / Re: * <input name="subject" id="subject" value="<?= p2_h($form['subject']) ?>" maxlength="255"></label>
  <label class="full">Recipient address <textarea name="recipient_address" rows="2"><?= p2_h($form['recipient_address']) ?></textarea></label>
 </div></section>

 <section class="letter-workspace-grid">
  <aside class="card workspace-inputs"><h2>Instructions</h2><textarea name="instructions" rows="7" placeholder="What outcome, tone, deadline or point should this letter address?"><?= p2_h($form['instructions']) ?></textarea><h2>Facts / Evidence</h2><textarea name="facts" rows="8" placeholder="Enter only verified facts, chronology and available evidence."><?= p2_h($form['facts']) ?></textarea><h2>Opponent Letter</h2><textarea name="opponent_letter" rows="8" placeholder="Paste the relevant opposing correspondence or points to answer."><?= p2_h($form['opponent_letter']) ?></textarea><h2>Source Materials</h2><?php if (!$sourceMaterials): ?><p class="small">Select a matter to bring its documents, evidence, authorities, research and timeline into this review.</p><?php else: ?><ul class="workspace-source-list"><?php foreach ($sourceMaterials as $source): ?><li><strong><?= p2_h($source['label']) ?>:</strong> <?= p2_h($source['title']) ?></li><?php endforeach; ?></ul><?php endif; ?><div class="workspace-actions"><button name="action" value="analyse" class="button secondary">Analyse</button><button name="action" value="generate" class="button">Generate Draft</button></div></aside>
  <div class="workspace-results">
   <section class="card analysis-panel"><div class="split"><div><h2>AI Analysis</h2><p class="small">Preliminary deterministic local review based only on this workspace and linked matter.</p></div><span class="badge badge-legal-analysis">Local</span></div><div class="analysis-columns"><div><h3>Issues / Law</h3><ul><?php foreach (array_merge($analysis['issues'], $analysis['law']) as $item): ?><li><?= p2_h($item) ?></li><?php endforeach; ?></ul></div><div><h3>Evidence / Risks</h3><ul><?php foreach (array_merge($analysis['evidence'], $analysis['risks']) as $item): ?><li><?= p2_h($item) ?></li><?php endforeach; ?></ul></div><div><h3>Strategy / Remedies</h3><ul><?php foreach ($analysis['strategy'] as $item): ?><li><?= p2_h($item) ?></li><?php endforeach; ?></ul></div></div><div class="letter-quality"><strong>Quality Review: <?= $qualityScore ?>/100</strong><span><?= $qualityScore >= 80 ? 'Review-ready drafting information' : 'Add verified context before issue' ?></span></div></section>
   <section class="card generated-letter"><div class="split"><div><h2>Generated Output</h2><p class="small">PRELIMINARY AI OUTPUT — edit and lawyer-review before saving, sending or issue.</p></div><button type="button" class="button secondary no-print" onclick="window.print()">Export PDF</button></div><textarea name="body" id="body" rows="18" placeholder="Generate a draft or write the letter here."><?= p2_h($form['body']) ?></textarea><div class="workspace-actions no-print"><button name="action" value="improve" class="button secondary">Improve Draft</button><a class="button secondary" href="<?= $form['matter_id'] ? 'counsel_engine.php?matter_id=' . (int)$form['matter_id'] . '#authorities' : 'counsel_engine.php' ?>">Add Authorities</a><a class="button secondary" href="<?= $form['matter_id'] ? 'counsel_engine.php?matter_id=' . (int)$form['matter_id'] . '#risk-review' : 'counsel_engine.php' ?>">Risk Review</a><button name="action" value="export_word" class="button secondary">Export Word</button><button name="action" value="save" class="button">Save correspondence</button><button name="action" value="save_to_matter" class="button">Save To Matter</button></div></section>
  </div>
 </section>
 <details class="card workspace-metadata no-print"><summary><strong>Correspondence details, status and attachments</strong></summary><div class="form-grid">
  <label>Reference <input name="ref_no" value="<?= p2_h($form['ref_no']) ?>" maxlength="100" placeholder="Generated if blank"></label><label>Practice / team <input name="practice_area" value="<?= p2_h($form['practice_area']) ?>" maxlength="100"></label>
  <label>Direction <select name="direction"><option value="outgoing" <?= $form['direction'] === 'outgoing' ? 'selected' : '' ?>>Outgoing</option><option value="incoming" <?= $form['direction'] === 'incoming' ? 'selected' : '' ?>>Incoming</option></select></label><label>Status <select name="status"><?php foreach (['draft' => 'Draft', 'final' => 'Final', 'sent' => 'Sent'] as $key => $label): ?><option value="<?= $key ?>" <?= $form['status'] === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label>
  <label>Salutation <input name="salutation" value="<?= p2_h($form['salutation']) ?>" maxlength="100"></label><label>Delivery method <select name="delivery_method"><?php foreach (['email' => 'Email', 'post' => 'Post', 'hand' => 'Hand delivery', 'portal' => 'Portal'] as $key => $label): ?><option value="<?= $key ?>" <?= $form['delivery_method'] === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label>
  <label><input type="checkbox" name="requires_response" value="1" <?= $form['requires_response'] ? 'checked' : '' ?>> Response required</label><label>Response due date <input type="date" name="response_due_on" value="<?= p2_h($form['response_due_on']) ?>"></label>
  <label>Signatory <input name="signatory_name" value="<?= p2_h($form['signatory_name']) ?>" maxlength="255"></label><label>Title <input name="signatory_title" value="<?= p2_h($form['signatory_title']) ?>" maxlength="255"></label>
  <label>Linked matter document <select name="document_id"><option value="">No document linked</option><?php foreach ($documents as $document): if (!$form['matter_id'] || (int)$document['matter_id'] === $form['matter_id']): ?><option value="<?= (int)$document['id'] ?>" <?= $form['document_id'] === (int)$document['id'] ? 'selected' : '' ?>><?= p2_h(($document['reference'] ?: 'General') . ' — ' . $document['title']) ?></option><?php endif; endforeach; ?></select></label><label>Bundle reference <input name="bundle_reference" value="<?= p2_h($form['bundle_reference']) ?>" maxlength="100"></label>
  <label>Attachment label <input name="attachment_label" value="<?= p2_h($form['attachment_label']) ?>" maxlength="255"></label><label>Attachment reference / location <input name="attachment_reference" value="<?= p2_h($form['attachment_reference']) ?>" maxlength="500"></label>
 </div></details>
</form>
<script>
const templates={general:['General correspondence','RE: ','We write regarding the above matter.\n\nPlease treat this correspondence as a formal record of our communication.\n\nYours faithfully,'],acknowledgement:['Acknowledgement','RE: Acknowledgement of correspondence','We acknowledge receipt of your correspondence.\n\nWe are reviewing its contents and will respond as appropriate.\n\nYours faithfully,'],information:['Request for information','RE: Request for information','Please provide the information and documents relevant to the above matter.\n\nPlease respond by the date indicated.\n\nYours faithfully,'],response:['Response to correspondence','RE: Response to correspondence','We refer to your correspondence and set out our response below.\n\n[Insert factual response and next steps.]\n\nYours faithfully,'],reminder:['Reminder','RE: Reminder','We refer to our earlier correspondence.\n\nPlease provide your response or the requested information by the stated date.\n\nYours faithfully,']};
document.getElementById('template').addEventListener('change', event => { const template = templates[event.target.value]; if (template) { document.getElementById('letter_type').value = template[0]; document.getElementById('subject').value = template[1]; if (!document.getElementById('body').value.trim()) document.getElementById('body').value = template[2]; } });
</script>
<?php }); ?>
