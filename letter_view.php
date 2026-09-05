<?php
require_once __DIR__ . '/phase2.php';
p2_start();
$pdo = p2_db();
$id = (int)($_GET['id'] ?? $_POST['letter_id'] ?? 0);
if (!$id) p2_redirect('letter_list.php');

function correspondence_record(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT l.*, c.matter_id,c.client_id,c.practice_area,c.correspondence_reference,c.direction,c.delivery_method,c.response_due_on,c.response_received_on,c.requires_response,c.bundle_reference,c.ai_analysis,c.quality_score,
        m.reference,m.title AS matter_title,cl.name AS client_name,cl.email AS client_email
        FROM draft_letters l LEFT JOIN p5_correspondence c ON c.letter_id=l.id
        LEFT JOIN p2_matters m ON m.id=c.matter_id LEFT JOIN p2_clients cl ON cl.id=c.client_id WHERE l.id=?");
    $stmt->execute([$id]); return $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    p2_check_csrf();
    $letter = correspondence_record($pdo, $id);
    if (!$letter) p2_redirect('letter_list.php');
    $action = (string)($_POST['action'] ?? '');
    try {
        if ($action === 'delete') {
            $pdo->beginTransaction();
            foreach (['p5_letter_attachments', 'p5_letter_checklist', 'p5_letter_versions', 'p5_correspondence_timeline', 'p5_correspondence'] as $table) $pdo->prepare('DELETE FROM ' . $table . ' WHERE letter_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM draft_letters WHERE id=?')->execute([$id]);
            $pdo->commit();
            p2_redirect('letter_list.php', 'Correspondence deleted.');
        }
        if ($action === 'save_draft') {
            $updated = [
                'letter_type' => trim((string)($_POST['letter_type'] ?? '')),
                'recipient_name' => trim((string)($_POST['recipient_name'] ?? '')),
                'recipient_address' => trim((string)($_POST['recipient_address'] ?? '')),
                'recipient_email' => trim((string)($_POST['recipient_email'] ?? '')),
                'subject' => trim((string)($_POST['subject'] ?? '')),
                'salutation' => trim((string)($_POST['salutation'] ?? '')),
                'body' => trim((string)($_POST['body'] ?? '')),
                'signatory_name' => trim((string)($_POST['signatory_name'] ?? '')),
                'signatory_title' => trim((string)($_POST['signatory_title'] ?? '')),
                'ref_no' => $letter['ref_no'], 'lawyer_name' => $letter['lawyer_name']
            ];
            if ($updated['recipient_name'] === '' || $updated['subject'] === '' || $updated['body'] === '') throw new InvalidArgumentException('Recipient, subject and body are required.');
            if ($updated['recipient_email'] !== '' && !filter_var($updated['recipient_email'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid recipient email address.');
            $context = ['matter_id'=>(int)$letter['matter_id'], 'requires_response'=>(int)$letter['requires_response'], 'response_due_on'=>$letter['response_due_on']];
            $score = p5_quality_score($updated, $context);
            $analysis = p5_letter_analysis($updated, $context);
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE draft_letters SET letter_type=?,recipient_name=?,recipient_address=?,recipient_email=?,subject=?,salutation=?,body=?,signatory_name=?,signatory_title=? WHERE id=?')
                ->execute([$updated['letter_type'], $updated['recipient_name'], $updated['recipient_address'], $updated['recipient_email'], $updated['subject'], $updated['salutation'], $updated['body'], $updated['signatory_name'], $updated['signatory_title'], $id]);
            $pdo->prepare("INSERT INTO p5_correspondence (letter_id,correspondence_reference,direction,delivery_method,ai_analysis,quality_score,created_by) VALUES (?,?,?,?,?,?,?)
                ON CONFLICT(letter_id) DO UPDATE SET ai_analysis=excluded.ai_analysis,quality_score=excluded.quality_score,updated_at=CURRENT_TIMESTAMP")
                ->execute([$id, $letter['ref_no'], $letter['direction'] ?: 'outgoing', $letter['delivery_method'] ?: 'email', $analysis, $score, p2_user_id()]);
            $nextStatement = $pdo->prepare('SELECT COALESCE(MAX(version_no),0)+1 FROM p5_letter_versions WHERE letter_id=?');
            $nextStatement->execute([$id]);
            $next = (int)$nextStatement->fetchColumn();
            $pdo->prepare('INSERT INTO p5_letter_versions (letter_id,version_no,snapshot,note,created_by) VALUES (?,?,?,?,?)')->execute([$id, $next, json_encode(['letter'=>$updated,'context'=>$context], JSON_UNESCAPED_UNICODE), 'Draft content edited', p2_user_id()]);
            p5_add_timeline($pdo, $id, 'draft_edited', 'Draft content edited and local review refreshed.');
            if ($letter['matter_id']) p2_log('correspondence.updated', 'Edited correspondence ' . ($letter['ref_no'] ?: '#' . $id), (int)$letter['matter_id'], ['letter_id' => $id]);
            $pdo->commit();
            p2_redirect('letter_view.php?id=' . $id, 'Draft updated and a new version saved.');
        }
        if ($action === 'update_status') {
            $status = (string)($_POST['status'] ?? '');
            $received = trim((string)($_POST['response_received_on'] ?? ''));
            if (!in_array($status, ['draft', 'final', 'sent'], true)) throw new InvalidArgumentException('Select a valid correspondence status.');
            if ($received !== '' && !p2_valid_date($received)) throw new InvalidArgumentException('Response received date must be valid.');
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE draft_letters SET status=? WHERE id=?')->execute([$status, $id]);
            $pdo->prepare("INSERT INTO p5_correspondence (letter_id,correspondence_reference,direction,delivery_method,response_received_on,quality_score,created_by) VALUES (?,?,?,?,?,?,?)
                ON CONFLICT(letter_id) DO UPDATE SET response_received_on=excluded.response_received_on,updated_at=CURRENT_TIMESTAMP")
                ->execute([$id, $letter['ref_no'], $letter['direction'] ?: 'outgoing', $letter['delivery_method'] ?: 'email', $received ?: null, (int)$letter['quality_score'], p2_user_id()]);
            $nextStatement = $pdo->prepare('SELECT COALESCE(MAX(version_no),0)+1 FROM p5_letter_versions WHERE letter_id=?');
            $nextStatement->execute([$id]);
            $next = (int)$nextStatement->fetchColumn();
            $letter['status'] = $status; $letter['response_received_on'] = $received;
            $pdo->prepare('INSERT INTO p5_letter_versions (letter_id,version_no,snapshot,note,created_by) VALUES (?,?,?,?,?)')->execute([$id, $next, json_encode($letter, JSON_UNESCAPED_UNICODE), 'Status updated to ' . $status, p2_user_id()]);
            p5_add_timeline($pdo, $id, 'status_updated', 'Status changed to ' . $status . ($received ? '; response received ' . $received : '') . '.');
            if ($letter['matter_id']) p2_log('correspondence.updated', 'Updated correspondence ' . ($letter['ref_no'] ?: '#' . $id), (int)$letter['matter_id'], ['letter_id' => $id]);
            $pdo->commit();
            p2_redirect('letter_view.php?id=' . $id, 'Status and response record updated.');
        }
        if ($action === 'checklist') {
            $checkId = (int)($_POST['check_id'] ?? 0);
            $completed = isset($_POST['completed']) ? 1 : 0;
            $stmt = $pdo->prepare('UPDATE p5_letter_checklist SET completed=?, completed_at=CASE WHEN ?=1 THEN CURRENT_TIMESTAMP ELSE NULL END WHERE id=? AND letter_id=?');
            $stmt->execute([$completed, $completed, $checkId, $id]);
            p5_add_timeline($pdo, $id, 'checklist_updated', 'Review checklist updated.');
            p2_redirect('letter_view.php?id=' . $id, 'Checklist updated.');
        }
        if ($action === 'attachment') {
            $label = trim((string)($_POST['label'] ?? ''));
            $reference = trim((string)($_POST['file_reference'] ?? ''));
            $documentId = (int)($_POST['document_id'] ?? 0);
            if ($label === '' && !$documentId && $reference === '') throw new InvalidArgumentException('Provide an attachment label, linked document or reference.');
            if ($documentId) {
                $document = $pdo->prepare('SELECT matter_id FROM p2_documents WHERE id=?'); $document->execute([$documentId]);
                $documentMatter = $document->fetchColumn();
                if ($documentMatter === false || ($letter['matter_id'] && (int)$documentMatter !== (int)$letter['matter_id'])) throw new InvalidArgumentException('The linked document must belong to this matter.');
            }
            $pdo->prepare('INSERT INTO p5_letter_attachments (letter_id,label,document_id,file_reference) VALUES (?,?,?,?)')->execute([$id, $label ?: 'Linked document', $documentId ?: null, $reference ?: null]);
            p5_add_timeline($pdo, $id, 'attachment_linked', 'Attachment or document reference linked.');
            p2_redirect('letter_view.php?id=' . $id, 'Attachment reference linked.');
        }
        if ($action === 'generate_response') {
            $pdo->beginTransaction();
            $response = [
                'ref_no' => ($letter['ref_no'] ?: 'COR-' . $id) . '/R1',
                'letter_type' => 'Response to correspondence', 'recipient_name' => $letter['recipient_name'],
                'recipient_address' => $letter['recipient_address'], 'recipient_email' => $letter['recipient_email'],
                'subject' => 'RE: ' . $letter['subject'], 'salutation' => $letter['salutation'] ?: 'Dear Sir/Madam',
                'body' => "We refer to your correspondence concerning the above matter.\n\nWe are reviewing the points raised and will provide a substantive response as appropriate.\n\nYours faithfully,",
                'signatory_name' => '', 'signatory_title' => '', 'status' => 'draft'
            ];
            $insert = $pdo->prepare('INSERT INTO draft_letters (ref_no,letter_type,recipient_name,recipient_address,recipient_email,subject,salutation,body,signatory_name,signatory_title,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $insert->execute([$response['ref_no'], $response['letter_type'], $response['recipient_name'], $response['recipient_address'], $response['recipient_email'], $response['subject'], $response['salutation'], $response['body'], '', '', 'draft']);
            $responseId = (int)$pdo->lastInsertId();
            $context = ['matter_id'=>(int)$letter['matter_id'], 'client_id'=>(int)$letter['client_id'], 'practice_area'=>$letter['practice_area'], 'direction'=>'outgoing', 'delivery_method'=>'email', 'requires_response'=>0];
            $score = p5_quality_score($response, $context);
            $pdo->prepare('INSERT INTO p5_correspondence (letter_id,matter_id,client_id,practice_area,correspondence_reference,direction,delivery_method,requires_response,reply_to_letter_id,ai_analysis,quality_score,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$responseId, $context['matter_id'] ?: null, $context['client_id'] ?: null, $context['practice_area'], $response['ref_no'], 'outgoing', 'email', 0, $id, p5_letter_analysis($response, $context), $score, p2_user_id()]);
            $pdo->prepare('INSERT INTO p5_letter_versions (letter_id,version_no,snapshot,note,created_by) VALUES (?,?,?,?,?)')->execute([$responseId, 1, json_encode(['letter'=>$response,'context'=>$context], JSON_UNESCAPED_UNICODE), 'Response generator draft', p2_user_id()]);
            $check = $pdo->prepare('INSERT INTO p5_letter_checklist (letter_id,item,required) VALUES (?,?,1)');
            foreach (['Recipient and address checked', 'Matter reference checked', 'Subject and factual context checked', 'Signatory and delivery checked'] as $item) $check->execute([$responseId, $item]);
            p5_add_timeline($pdo, $responseId, 'response_generated', 'Response draft generated from correspondence #' . $id . '.');
            p5_add_timeline($pdo, $id, 'response_generated', 'Response draft #' . $responseId . ' generated.');
            $pdo->commit();
            p2_redirect('letter_view.php?id=' . $responseId, 'Response draft generated. Review and complete it before use.');
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        p2_redirect('letter_view.php?id=' . $id, $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'The requested update could not be completed.');
    }
}
$letter = correspondence_record($pdo, $id);
if (!$letter) p2_redirect('letter_list.php');
$versions = $pdo->prepare('SELECT * FROM p5_letter_versions WHERE letter_id=? ORDER BY version_no DESC'); $versions->execute([$id]); $versions = $versions->fetchAll();
$checklist = $pdo->prepare('SELECT * FROM p5_letter_checklist WHERE letter_id=? ORDER BY id'); $checklist->execute([$id]); $checklist = $checklist->fetchAll();
$attachments = $pdo->prepare('SELECT a.*,d.title AS document_title FROM p5_letter_attachments a LEFT JOIN p2_documents d ON d.id=a.document_id WHERE a.letter_id=? ORDER BY a.created_at DESC'); $attachments->execute([$id]); $attachments = $attachments->fetchAll();
$timeline = $pdo->prepare('SELECT * FROM p5_correspondence_timeline WHERE letter_id=? ORDER BY occurred_at DESC,id DESC'); $timeline->execute([$id]); $timeline = $timeline->fetchAll();
$documents = $pdo->query("SELECT d.id,d.title,d.matter_id,m.reference FROM p2_documents d LEFT JOIN p2_matters m ON m.id=d.matter_id ORDER BY d.created_at DESC LIMIT 100")->fetchAll();
$body = (string)$letter['body']; $words = preg_match_all('/\b[\p{L}\p{N}\'’-]+\b/u', $body, $unused); $sentences = $body === '' ? 0 : max(1, preg_match_all('/[.!?]+(?:\s|$)/', $body)); $minutes = max(1, (int)ceil($words / 200));
$completed = count(array_filter($checklist, fn($item) => (int)$item['completed'] === 1));
$email = $letter['recipient_email'] ? 'mailto:' . rawurlencode($letter['recipient_email']) . '?subject=' . rawurlencode($letter['subject']) : '';
p2_page('Correspondence record', 'letter_list.php', function () use ($letter, $versions, $checklist, $attachments, $timeline, $documents, $words, $sentences, $minutes, $completed, $email) { ?>
<div class="toolbar"><div><h1><?= p2_h($letter['subject']) ?></h1><p class="subhead"><?= p2_h($letter['ref_no'] ?: 'Unreferenced') ?> · <?= p2_status_badge($letter['status']) ?> · <?= p2_status_badge($letter['direction'] ?: 'outgoing') ?></p></div><div><a class="button" target="_blank" href="letter_print.php?id=<?= $letter['id'] ?>">Print / Save PDF</a> <a class="button secondary" href="letter_print.php?id=<?= $letter['id'] ?>&format=word">Download Word</a> <?php if ($email): ?><a class="button secondary" href="<?= p2_h($email) ?>">Email</a><?php endif; ?></div></div>
<section class="grid grid-4"><div class="card"><div class="metric"><?= (int)$letter['quality_score'] ?>/100</div><div class="metric-label">Draft quality</div></div><div class="card"><div class="metric"><?= $words ?></div><div class="metric-label">Words · <?= $minutes ?> min read</div></div><div class="card"><div class="metric"><?= $sentences ?></div><div class="metric-label">Sentences</div></div><div class="card"><div class="metric"><?= $completed ?>/<?= count($checklist) ?></div><div class="metric-label">Checklist complete</div></div></section>
<section class="card" style="margin-top:18px"><h2>AI Letter Assistant</h2><p><?= p2_h($letter['ai_analysis'] ?: p5_letter_analysis($letter, $letter)) ?></p><p class="small">This is a local completeness review, not legal advice or a substitute for counsel review.</p><form method="post"><input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>"><input type="hidden" name="letter_id" value="<?= $letter['id'] ?>"><input type="hidden" name="action" value="generate_response"><button class="button secondary">Generate response draft</button></form></section>
<details class="card" style="margin-top:18px"><summary><strong>Edit draft</strong></summary><form method="post" class="form-grid" style="margin-top:12px"><input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>"><input type="hidden" name="letter_id" value="<?= $letter['id'] ?>"><input type="hidden" name="action" value="save_draft"><label>Letter type <input name="letter_type" value="<?= p2_h($letter['letter_type']) ?>" maxlength="100"></label><label>Recipient name * <input name="recipient_name" value="<?= p2_h($letter['recipient_name']) ?>" required maxlength="255"></label><label>Recipient email <input name="recipient_email" type="email" value="<?= p2_h($letter['recipient_email']) ?>" maxlength="255"></label><label>Salutation <input name="salutation" value="<?= p2_h($letter['salutation']) ?>" maxlength="100"></label><label class="full">Recipient address <textarea name="recipient_address" rows="3"><?= p2_h($letter['recipient_address']) ?></textarea></label><label class="full">Subject * <input name="subject" value="<?= p2_h($letter['subject']) ?>" required maxlength="255"></label><label class="full">Body * <textarea name="body" rows="12" required><?= p2_h($letter['body']) ?></textarea></label><label>Signatory <input name="signatory_name" value="<?= p2_h($letter['signatory_name']) ?>" maxlength="255"></label><label>Title <input name="signatory_title" value="<?= p2_h($letter['signatory_title']) ?>" maxlength="255"></label><p><button class="button">Save new version</button></p></form></details>
<details class="card" open style="margin-top:18px"><summary><strong>Correspondence context</strong></summary><div class="form-grid" style="margin-top:12px"><div><strong>Matter</strong><br><?= $letter['matter_id'] ? '<a href="matters.php?id=' . (int)$letter['matter_id'] . '">' . p2_h($letter['reference'] ?: $letter['matter_title']) . '</a>' : 'Unlinked' ?></div><div><strong>Client</strong><br><?= p2_h($letter['client_name'] ?: '—') ?></div><div><strong>Practice / team</strong><br><?= p2_h($letter['practice_area'] ?: '—') ?></div><div><strong>Bundle reference</strong><br><?= p2_h($letter['bundle_reference'] ?: '—') ?></div><div><strong>Delivery</strong><br><?= p2_h($letter['delivery_method'] ?: '—') ?></div><div><strong>Response</strong><br><?= (int)$letter['requires_response'] ? p2_h($letter['response_received_on'] ? 'Received ' . $letter['response_received_on'] : ('Due ' . ($letter['response_due_on'] ?: 'not set'))) : 'Not required' ?></div></div></details>
<details class="card" open style="margin-top:18px"><summary><strong>Recipient and letter</strong></summary><div style="margin-top:12px"><p><strong><?= p2_h($letter['recipient_name']) ?></strong><br><?= nl2br(p2_h($letter['recipient_address'])) ?><?= $letter['recipient_email'] ? '<br>' . p2_h($letter['recipient_email']) : '' ?></p><p><strong><?= p2_h($letter['salutation'] ?: 'Dear Sir/Madam') ?>,</strong></p><div style="white-space:pre-wrap;line-height:1.65"><?= p2_h($letter['body']) ?></div><p style="margin-top:16px"><strong><?= p2_h($letter['signatory_name'] ?: $letter['lawyer_name'] ?: 'Counsel') ?></strong><br><?= p2_h($letter['signatory_title'] ?: '') ?></p></div></details>
<details class="card" open style="margin-top:18px"><summary><strong>Review checklist (<?= $completed ?>/<?= count($checklist) ?>)</strong></summary><?php if (!$checklist): ?><p class="small">No checklist is recorded for this legacy letter.</p><?php endif; ?><?php foreach ($checklist as $item): ?><form method="post" style="margin-top:10px"><input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>"><input type="hidden" name="letter_id" value="<?= $letter['id'] ?>"><input type="hidden" name="action" value="checklist"><input type="hidden" name="check_id" value="<?= $item['id'] ?>"><label><input type="checkbox" name="completed" value="1" <?= $item['completed']?'checked':'' ?> onchange="this.form.submit()"> <?= p2_h($item['item']) ?></label></form><?php endforeach; ?></details>
<details class="card" style="margin-top:18px"><summary><strong>Attachments, linked documents and bundle</strong></summary><ul class="compact-list"><?php foreach ($attachments as $attachment): ?><li><span><strong><?= p2_h($attachment['label']) ?></strong><small><?= p2_h($attachment['document_title'] ?: $attachment['file_reference'] ?: 'Reference recorded') ?></small></span></li><?php endforeach; ?><?php if (!$attachments): ?><li>No attachments or document links recorded.</li><?php endif; ?></ul><form method="post" class="form-grid" style="margin-top:12px"><input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>"><input type="hidden" name="letter_id" value="<?= $letter['id'] ?>"><input type="hidden" name="action" value="attachment"><label>Label <input name="label" maxlength="255"></label><label>Linked document <select name="document_id"><option value="">None</option><?php foreach ($documents as $document): if (!$letter['matter_id'] || (int)$letter['matter_id'] === (int)$document['matter_id']): ?><option value="<?= $document['id'] ?>"><?= p2_h(($document['reference'] ?: 'General') . ' — ' . $document['title']) ?></option><?php endif; endforeach; ?></select></label><label>Reference / location <input name="file_reference" maxlength="500"></label><p><button class="button secondary">Link reference</button></p></form></details>
<details class="card" style="margin-top:18px"><summary><strong>Version history (<?= count($versions) ?>)</strong></summary><ul class="compact-list"><?php foreach ($versions as $version): ?><li><span><strong>Version <?= (int)$version['version_no'] ?></strong><small><?= p2_h($version['note'] ?: 'Saved version') ?> · <?= p2_h($version['created_at']) ?></small></span></li><?php endforeach; ?><?php if (!$versions): ?><li>No version snapshot exists for this legacy letter.</li><?php endif; ?></ul></details>
<details class="card" open style="margin-top:18px"><summary><strong>Correspondence timeline</strong></summary><ol class="activity-stream"><?php foreach ($timeline as $event): ?><li><strong><?= p2_h(ucwords(str_replace('_', ' ', $event['event_type']))) ?></strong><span><?= p2_h($event['description']) ?> · <?= p2_h($event['occurred_at']) ?></span></li><?php endforeach; ?><?php if (!$timeline): ?><li>No workbench events recorded for this legacy letter.</li><?php endif; ?></ol></details>
<section class="card" style="margin-top:18px"><h2>Update delivery and response</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>"><input type="hidden" name="letter_id" value="<?= $letter['id'] ?>"><input type="hidden" name="action" value="update_status"><label>Status <select name="status"><?php foreach (['draft'=>'Draft','final'=>'Final','sent'=>'Sent'] as $key=>$label): ?><option value="<?= $key ?>" <?= $letter['status']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><label>Response received on <input type="date" name="response_received_on" value="<?= p2_h($letter['response_received_on'] ?: '') ?>"></label><p><button class="button">Save update</button></p></form></section>
<form method="post" style="margin-top:18px" onsubmit="return confirm('Delete this correspondence record?')"><input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>"><input type="hidden" name="letter_id" value="<?= $letter['id'] ?>"><input type="hidden" name="action" value="delete"><button class="button danger">Delete correspondence</button> <a class="button secondary" href="letter_list.php">Back to list</a></form>
<?php }); ?>
