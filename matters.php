<?php
require_once __DIR__ . '/phase2.php';
p2_start();
$pdo = p2_db();
p2_assert_matter_schema($pdo);
p2_check_csrf();

function matter_redirect(int $id, string $tab = 'overview', string $message = ''): void {
    p2_redirect('matters.php?id=' . $id . ($tab !== 'overview' ? '&tab=' . rawurlencode($tab) : ''), $message);
}
function matter_text(string $value, int $limit): bool {
    return $value !== '' && strlen($value) <= $limit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        try {
            [$id] = p2_create_matter($pdo, $_POST, p2_user_id());
            matter_redirect($id, 'overview', 'Matter created and saved.');
        } catch (InvalidArgumentException $exception) {
            p2_redirect('matters.php?action=new', $exception->getMessage());
        } catch (Throwable $exception) {
            p2_redirect('matters.php?action=new', 'The matter could not be saved. Please try again.');
        }
    }

    $matterId = (int)($_POST['matter_id'] ?? 0);
    if (!p2_matter_exists($pdo, $matterId)) {
        p2_error_page(404, 'Matter not found', 'This matter is unavailable or may have been removed.');
    }
    if ($action === 'update') {
        [$matter, $errors] = p2_validate_matter_input($pdo, $_POST);
        if ($errors) matter_redirect($matterId, 'details', implode(' ', $errors));
        $stmt = $pdo->prepare('UPDATE p2_matters SET client_id=?, title=?, practice_area=?, status=?, opened_on=?, description=? WHERE id=?');
        $stmt->execute([$matter['client_id'], $matter['title'], $matter['practice_area'], $matter['status'], $matter['opened_on'], $matter['description'], $matterId]);
        p2_log('matter.updated', 'Updated matter details', $matterId);
        matter_redirect($matterId, 'details', 'Matter details saved.');
    }
    if ($action === 'add_timeline') {
        $occurredOn = trim($_POST['occurred_on'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $detail = trim($_POST['detail'] ?? '');
        if (!p2_valid_date($occurredOn) || !matter_text($title, 255) || strlen($detail) > 10000) {
            matter_redirect($matterId, 'timeline', 'Enter a valid date, a timeline title (255 characters or fewer), and a concise detail.');
        }
        $stmt = $pdo->prepare('INSERT INTO p3_timeline (matter_id,occurred_on,title,detail,created_by) VALUES (?,?,?,?,?)');
        $stmt->execute([$matterId, $occurredOn, $title, $detail ?: null, p2_user_id()]);
        p2_log('timeline.created', 'Added timeline event: ' . $title, $matterId, ['timeline_id' => (int)$pdo->lastInsertId()]);
        matter_redirect($matterId, 'timeline', 'Timeline event added.');
    }
    if ($action === 'add_deadline') {
        $title = trim($_POST['title'] ?? '');
        $dueOn = trim($_POST['due_on'] ?? '');
        $priority = trim($_POST['priority'] ?? 'normal');
        if (!matter_text($title, 255) || !p2_valid_date($dueOn) || !in_array($priority, ['low', 'normal', 'high'], true)) {
            matter_redirect($matterId, 'deadlines', 'Enter a deadline title, a valid due date, and a valid priority.');
        }
        $stmt = $pdo->prepare('INSERT INTO p2_deadlines (matter_id,title,due_on,priority,created_by) VALUES (?,?,?,?,?)');
        $stmt->execute([$matterId, $title, $dueOn, $priority, p2_user_id()]);
        p2_log('deadline.created', 'Added deadline: ' . $title, $matterId, ['deadline_id' => (int)$pdo->lastInsertId()]);
        matter_redirect($matterId, 'deadlines', 'Deadline added.');
    }
    p2_error_page(400, 'Request not recognised', 'The requested matter action could not be completed.');
}

$action = $_GET['action'] ?? '';
if ($action === 'new') {
    $clients = $pdo->query('SELECT id,name FROM p2_clients ORDER BY name COLLATE NOCASE')->fetchAll();
    p2_page('Create matter', 'matters.php', function () use ($clients) { ?>
        <div class="toolbar"><div><h1>Create matter</h1><p class="subhead">Create the central workspace for the client instruction, documents, deadlines and work history.</p></div><a class="button secondary" href="matters.php">Back to matters</a></div>
        <section class="card"><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="create">
            <div class="full"><label>Matter title *</label><input required maxlength="255" name="title" autofocus placeholder="e.g. Smith v Acme Ltd"></div>
            <div><label>Client</label><select name="client_id"><option value="">Unassigned</option><?php foreach ($clients as $client): ?><option value="<?= (int)$client['id'] ?>"><?= p2_h($client['name']) ?></option><?php endforeach; ?></select></div>
            <div><label>Practice area</label><input maxlength="100" name="practice_area" placeholder="e.g. Employment"></div>
            <div><label>Status</label><select name="status"><option value="open">Open</option><option value="on_hold">On hold</option><option value="closed">Closed</option></select></div>
            <div><label>Opened date</label><input type="date" name="opened_on" value="<?= date('Y-m-d') ?>"></div>
            <div class="full"><label>Instruction summary</label><textarea maxlength="10000" name="description" placeholder="Record the scope, client objective and essential background."></textarea></div>
            <div><button>Create matter</button></div>
        </form></section>
    <?php });
    exit;
}

$hasId = array_key_exists('id', $_GET);
$reference = strtoupper(trim($_GET['reference'] ?? ''));
if (!$hasId && $reference === '') {
    $q = trim($_GET['q'] ?? '');
    $sql = 'SELECT m.*, c.name AS client_name, (SELECT MAX(a.created_at) FROM p2_activity a WHERE a.matter_id=m.id) AS last_activity FROM p2_matters m LEFT JOIN p2_clients c ON c.id=m.client_id';
    $params = [];
    if ($q !== '') {
        $sql .= ' WHERE m.reference LIKE ? OR m.title LIKE ? OR c.name LIKE ?';
        $params = ['%' . $q . '%', '%' . $q . '%', '%' . $q . '%'];
    }
    $sql .= ' ORDER BY COALESCE(last_activity, m.created_at) DESC, m.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $matters = $stmt->fetchAll();
    p2_page('Matters', 'matters.php', function () use ($matters, $q) { ?>
        <div class="toolbar"><div><h1>Matter workspace</h1><p class="subhead">The authoritative record for each instruction, including its canonical reference, work history and connected records.</p></div><a class="button" href="matters.php?action=new">+ New matter</a></div>
        <section class="card"><form method="get" class="inline"><div style="flex:1;min-width:240px"><label for="matter-search">Find a matter</label><input id="matter-search" name="q" value="<?= p2_h($q) ?>" placeholder="Reference, title or client"></div><button>Search</button></form></section>
        <section class="card" style="margin-top:18px"><?php if (!$matters): ?><div class="empty">No matters match this search.<br><a href="matters.php?action=new">Create a matter</a> to begin a connected workspace.</div><?php else: ?><table><thead><tr><th>Reference</th><th>Matter</th><th>Client</th><th>Status</th><th>Last activity</th></tr></thead><tbody><?php foreach ($matters as $matter): ?><tr><td><a href="matters.php?id=<?= (int)$matter['id'] ?>"><?= p2_h($matter['reference']) ?></a></td><td><?= p2_h($matter['title']) ?><br><span class="small"><?= p2_h($matter['practice_area'] ?: 'Unclassified') ?></span></td><td><?= p2_h($matter['client_name'] ?: 'Unassigned') ?></td><td><?= p2_status_badge($matter['status']) ?></td><td><?= p2_h(date('d M Y H:i', strtotime($matter['last_activity'] ?: $matter['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
    <?php });
    exit;
}

$id = $hasId ? (int)$_GET['id'] : 0;
if ($hasId && $id < 1) p2_error_page(400, 'Invalid matter link', 'The matter identifier in this link is invalid.');
if ($reference !== '' && !p2_matter_reference_is_valid($reference)) p2_error_page(400, 'Invalid matter reference', 'Matter references use the format AEP-MAT-YYYY-NNNN.');
$matter = p2_find_matter($pdo, $id, $reference);
if (!$matter) p2_error_page(404, 'Matter not found', 'This matter is unavailable or may have been removed.');
if (!$hasId) p2_redirect('matters.php?id=' . (int)$matter['id']);

$tabs = ['overview' => 'Overview', 'counsel_analysis' => 'Counsel Analysis', 'details' => 'Details', 'timeline' => 'Timeline', 'activity' => 'Activity', 'documents' => 'Documents', 'deadlines' => 'Deadlines', 'tasks' => 'Tasks', 'research' => 'Research'];
$tab = $_GET['tab'] ?? 'overview';
if (!isset($tabs[$tab])) p2_error_page(400, 'Invalid workspace tab', 'The requested workspace section is not available.');
$clients = $pdo->query('SELECT id,name FROM p2_clients ORDER BY name COLLATE NOCASE')->fetchAll();
$timeline = $activity = $documents = $deadlines = $tasks = $research = $counselAnalysis = $aiOutputs = [];
if ($tab === 'overview' || $tab === 'activity') {
    $stmt = $pdo->prepare('SELECT * FROM p2_activity WHERE matter_id=? ORDER BY created_at DESC, id DESC LIMIT 20'); $stmt->execute([$id]); $activity = $stmt->fetchAll();
}
if ($tab === 'timeline') { $stmt = $pdo->prepare('SELECT * FROM p3_timeline WHERE matter_id=? ORDER BY occurred_on DESC, id DESC'); $stmt->execute([$id]); $timeline = $stmt->fetchAll(); }
if ($tab === 'documents') { $stmt = $pdo->prepare('SELECT * FROM p2_documents WHERE matter_id=? ORDER BY created_at DESC'); $stmt->execute([$id]); $documents = $stmt->fetchAll(); }
if ($tab === 'deadlines') { $stmt = $pdo->prepare('SELECT * FROM p2_deadlines WHERE matter_id=? ORDER BY CASE WHEN status="open" THEN 0 ELSE 1 END, due_on'); $stmt->execute([$id]); $deadlines = $stmt->fetchAll(); }
if ($tab === 'tasks') { $stmt = $pdo->prepare('SELECT * FROM p3_tasks WHERE matter_id=? ORDER BY CASE WHEN status="open" THEN 0 ELSE 1 END, due_on, created_at DESC'); $stmt->execute([$id]); $tasks = $stmt->fetchAll(); }
if ($tab === 'research') { $stmt = $pdo->prepare('SELECT * FROM p3_research WHERE matter_id=? ORDER BY created_at DESC'); $stmt->execute([$id]); $research = $stmt->fetchAll(); }
if ($tab === 'counsel_analysis') {
    $stmt = $pdo->prepare('SELECT * FROM p4_workbench_assessments WHERE matter_id=?'); $stmt->execute([$id]); $counselAnalysis = $stmt->fetch() ?: [];
    $stmt = $pdo->prepare('SELECT id,title,workflow,created_at,revision_action FROM p6_ai_outputs WHERE matter_id=? ORDER BY created_at DESC,id DESC LIMIT 10'); $stmt->execute([$id]); $aiOutputs = $stmt->fetchAll();
}

p2_page($matter['reference'] . ' — Matter', 'matters.php', function () use ($matter, $id, $tab, $tabs, $clients, $timeline, $activity, $documents, $deadlines, $tasks, $research, $counselAnalysis, $aiOutputs) { ?>
    <div class="toolbar"><div><span class="small"><?= p2_h($matter['reference']) ?></span><h1><?= p2_h($matter['title']) ?></h1><p class="subhead"><?= p2_h($matter['client_name'] ?: 'Unassigned client') ?> · <?= p2_h($matter['practice_area'] ?: 'No practice area') ?> · <?= p2_status_badge($matter['status']) ?></p></div><div><a class="button secondary" href="counsel_engine.php?matter_id=<?= $id ?>">Counsel Engine</a> <a class="button secondary" href="matters.php">All matters</a></div></div>
    <?php p2_workspace_tabs('matters.php', $id, $tab, $tabs); ?>
    <section class="card matter-ai-actions"><div class="split"><div><h2>AI actions for this matter</h2><p class="small">Create a preliminary, lawyer-reviewed draft from this matter’s facts, issues, law, evidence, risks and strategy.</p></div><a class="button" href="counsel_engine.php?matter_id=<?= $id ?>#ai-workflows">Open AI workspace</a></div><div class="quick-actions"><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=letter#ai-workflows">Letter</a><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=advice#ai-workflows">Advice</a><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=appeal#ai-workflows">Appeal</a><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=witness#ai-workflows">Witness</a><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=skeleton#ai-workflows">Skeleton</a><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=research#ai-workflows">Research</a><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=strategy#ai-workflows">Strategy</a><a href="counsel_engine.php?matter_id=<?= $id ?>&workflow=evidence#ai-workflows">Evidence</a></div></section>
    <?php if ($tab === 'overview'): ?>
        <div class="grid grid-2"><section class="card"><h2>Instruction details</h2><dl class="detail-list"><dt>Client</dt><dd><?= p2_h($matter['client_name'] ?: 'Unassigned') ?></dd><dt>Opened</dt><dd><?= p2_h($matter['opened_on'] ?: 'Not recorded') ?></dd><dt>Status</dt><dd><?= p2_status_badge($matter['status']) ?></dd><dt>Summary</dt><dd><?= nl2br(p2_h($matter['description'] ?: 'No instruction summary recorded.')) ?></dd></dl><p><a class="button secondary" href="matters.php?id=<?= $id ?>&tab=details">Edit details</a></p></section><section class="card"><div class="split"><h2>Recent activity</h2><a class="small" href="matters.php?id=<?= $id ?>&tab=activity">Full history</a></div><?php if (!$activity): ?><div class="empty">Activity will be recorded as work is added to this matter.</div><?php else: ?><ol class="activity-stream"><?php foreach (array_slice($activity, 0, 5) as $event): ?><li><strong><?= p2_h(p2_event_label($event['event_type'])) ?></strong><span><?= p2_h($event['description']) ?> · <?= p2_h(date('d M Y H:i', strtotime($event['created_at']))) ?></span></li><?php endforeach; ?></ol><?php endif; ?></section></div>
    <?php elseif ($tab === 'counsel_analysis'): ?>
        <div class="grid grid-2"><section class="card"><div class="split"><h2>Current counsel assessment</h2><a class="button secondary" href="counsel_engine.php?matter_id=<?= $id ?>">Open workbench</a></div><?php if (!$counselAnalysis): ?><div class="empty">No assessment has been saved. Use Counsel Engine to record the current position, confidence, issues, evidence and risks.</div><?php else: ?><dl class="detail-list"><dt>Position</dt><dd><?= p2_status_badge($counselAnalysis['position']) ?></dd><dt>Position score</dt><dd><?= (int)$counselAnalysis['position_score'] ?> / 100</dd><dt>Confidence</dt><dd><?= (int)$counselAnalysis['confidence_score'] ?> / 100</dd><dt>Summary</dt><dd><?= nl2br(p2_h($counselAnalysis['executive_summary'] ?: 'No summary recorded.')) ?></dd></dl><?php endif; ?></section><section class="card"><div class="split"><h2>Preliminary AI output</h2><a class="small" href="counsel_engine.php?matter_id=<?= $id ?>#ai-workflows">Generate / revise</a></div><?php if (!$aiOutputs): ?><div class="empty">No preliminary AI output is retained on this matter.</div><?php else: ?><ul class="compact-list"><?php foreach ($aiOutputs as $output): ?><li><span><strong><?= p2_h($output['title']) ?></strong><small><?= p2_h(p6_workflows()[$output['workflow']] ?? $output['workflow']) ?> · <?= p2_h(date('d M Y H:i', strtotime($output['created_at']))) ?><?= $output['revision_action'] ? ' · ' . p2_h($output['revision_action']) : '' ?></small></span><a href="counsel_engine.php?matter_id=<?= $id ?>#ai-workflows">Open</a></li><?php endforeach; ?></ul><?php endif; ?></section></div>
    <?php elseif ($tab === 'details'): ?>
        <section class="card"><h2>Matter details</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="matter_id" value="<?= $id ?>"><div class="full"><label>Matter title *</label><input required maxlength="255" name="title" value="<?= p2_h($matter['title']) ?>"></div><div><label>Client</label><select name="client_id"><option value="">Unassigned</option><?php foreach ($clients as $client): ?><option value="<?= (int)$client['id'] ?>" <?= (int)$matter['client_id'] === (int)$client['id'] ? 'selected' : '' ?>><?= p2_h($client['name']) ?></option><?php endforeach; ?></select></div><div><label>Practice area</label><input maxlength="100" name="practice_area" value="<?= p2_h($matter['practice_area']) ?>"></div><div><label>Status</label><select name="status"><?php foreach (['open' => 'Open', 'on_hold' => 'On hold', 'closed' => 'Closed'] as $value => $label): ?><option value="<?= $value ?>" <?= $matter['status'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div><div><label>Opened date</label><input type="date" name="opened_on" value="<?= p2_h($matter['opened_on']) ?>"></div><div class="full"><label>Instruction summary</label><textarea maxlength="10000" name="description"><?= p2_h($matter['description']) ?></textarea></div><div><button>Save details</button></div></form></section>
    <?php elseif ($tab === 'timeline'): ?>
        <div class="grid grid-2"><section class="card"><h2>Add timeline event</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_timeline"><input type="hidden" name="matter_id" value="<?= $id ?>"><div><label>Date *</label><input required type="date" name="occurred_on" value="<?= date('Y-m-d') ?>"></div><div><label>Event *</label><input required maxlength="255" name="title"></div><div class="full"><label>Detail</label><textarea maxlength="10000" name="detail"></textarea></div><div><button>Add event</button></div></form></section><section class="card"><h2>Chronology</h2><?php if (!$timeline): ?><div class="empty">No chronology events have been recorded.</div><?php else: ?><ol class="activity-stream"><?php foreach ($timeline as $event): ?><li><strong><?= p2_h($event['occurred_on']) ?> · <?= p2_h($event['title']) ?></strong><?php if ($event['detail']): ?><span><?= nl2br(p2_h($event['detail'])) ?></span><?php endif; ?></li><?php endforeach; ?></ol><?php endif; ?></section></div>
    <?php elseif ($tab === 'activity'): ?>
        <section class="card"><h2>Activity history</h2><?php if (!$activity): ?><div class="empty">No activity has been recorded.</div><?php else: ?><ol class="activity-stream"><?php foreach ($activity as $event): ?><li><strong><?= p2_h(p2_event_label($event['event_type'])) ?></strong><span><?= p2_h($event['description']) ?> · <?= p2_h(date('d M Y H:i', strtotime($event['created_at']))) ?></span></li><?php endforeach; ?></ol><?php endif; ?></section>
    <?php elseif ($tab === 'documents'): ?>
        <section class="card"><div class="split"><h2>Linked documents</h2><a class="button" href="documents.php?matter_id=<?= $id ?>">+ Link document</a></div><?php if (!$documents): ?><div class="empty">No documents are linked to this matter.</div><?php else: ?><table><thead><tr><th>Document</th><th>Type</th><th>Source</th><th>Added</th></tr></thead><tbody><?php foreach ($documents as $document): ?><tr><td><?= p2_h($document['title']) ?><br><span class="small"><?= p2_h($document['content']) ?></span></td><td><?= p2_h($document['document_type']) ?></td><td><?= p2_h($document['source_table'] ? $document['source_table'] . ' #' . $document['source_id'] : 'Workspace') ?></td><td><?= p2_h(date('d M Y', strtotime($document['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
    <?php elseif ($tab === 'deadlines'): ?>
        <div class="grid grid-2"><section class="card"><h2>Add deadline</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_deadline"><input type="hidden" name="matter_id" value="<?= $id ?>"><div class="full"><label>Deadline *</label><input required maxlength="255" name="title"></div><div><label>Due date *</label><input required type="date" name="due_on"></div><div><label>Priority</label><select name="priority"><option value="normal">Normal</option><option value="high">High</option><option value="low">Low</option></select></div><div><button>Add deadline</button></div></form></section><section class="card"><h2>Matter deadlines</h2><?php if (!$deadlines): ?><div class="empty">No deadlines have been added.</div><?php else: ?><ul class="compact-list"><?php foreach ($deadlines as $deadline): ?><li><span><strong><?= p2_h($deadline['title']) ?></strong><small><?= p2_h($deadline['due_on']) ?> · <?= p2_status_badge($deadline['priority']) ?></small></span><?= p2_status_badge($deadline['status']) ?></li><?php endforeach; ?></ul><?php endif; ?></section></div>
    <?php elseif ($tab === 'tasks'): ?>
        <section class="card"><h2>Matter tasks</h2><?php if (!$tasks): ?><div class="empty">No tracked tasks. Counsel Engine actions are available from the workspace header.</div><?php else: ?><ul class="compact-list"><?php foreach ($tasks as $task): ?><li><span><strong><?= p2_h($task['title']) ?></strong><small><?= p2_h($task['due_on'] ?: 'No due date') ?><?= $task['description'] ? ' · ' . p2_h($task['description']) : '' ?></small></span><?= p2_status_badge($task['status']) ?></li><?php endforeach; ?></ul><?php endif; ?></section>
    <?php else: ?>
        <section class="card"><h2>Research records</h2><?php if (!$research): ?><div class="empty">No research records have been saved for this matter.</div><?php else: ?><ul class="compact-list"><?php foreach ($research as $item): ?><li><span><strong><?= p2_h($item['title']) ?></strong><small><?= p2_h($item['research_type']) ?><?= $item['summary'] ? ' · ' . p2_h($item['summary']) : '' ?></small></span><span class="small"><?= p2_h(date('d M Y', strtotime($item['created_at']))) ?></span></li><?php endforeach; ?></ul><?php endif; ?></section>
    <?php endif; ?>
<?php }); ?>
