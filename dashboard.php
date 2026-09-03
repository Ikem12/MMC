<?php
// FILE: dashboard.php
// AEP Legal Intelligence Platform — Unified Workspace Dashboard
// Shows: upcoming deadlines, recent activity, practice areas, quick access to counsel engine

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/legal_library.php';

$pdo = aep_db();
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Get summary statistics
$counts = [
    'clients' => (int)$pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn() ?: 0,
    'matters' => (int)$pdo->query('SELECT COUNT(*) FROM matters WHERE status IS NULL OR status != "closed"')->fetchColumn() ?: 0,
    'tasks' => (int)$pdo->query('SELECT COUNT(*) FROM tasks WHERE status IS NULL OR status != "completed"')->fetchColumn() ?: 0,
    'deadlines' => 0,
];

// Try to get deadlines if table exists
try {
    $counts['deadlines'] = (int)$pdo->query(
        'SELECT COUNT(*) FROM deadlines WHERE status IS NULL OR (status != "completed" AND due_date >= date("now") AND due_date <= date("now","+14 days"))'
    )->fetchColumn() ?: 0;
} catch (Exception $e) {
    $counts['deadlines'] = 0;
}

// Get upcoming deadlines (next 14 days) sorted by due date
$upcoming = [];
try {
    $upcoming = $pdo->query(
        'SELECT d.*, m.matter_reference, m.subject FROM deadlines d 
         LEFT JOIN matters m ON m.id = d.matter_id 
         WHERE d.status IS NULL OR (d.status != "completed" AND d.due_date >= date("now") AND d.due_date <= date("now","+14 days"))
         ORDER BY d.due_date ASC LIMIT 10'
    )->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $upcoming = [];
}

// Get recent activity (last 8 entries)
$activity = [];
try {
    $activity = $pdo->query(
        'SELECT created_at, "Client created" as action FROM clients ORDER BY created_at DESC LIMIT 8'
    )->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $activity = [];
}

$pageTitle = 'Workspace | AEP Legal Intelligence';
$activeNav = 'workspace';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #f5f7fa; color: #222; line-height: 1.6; }
        .header { background: #163b62; color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
        .header h1 { font-size: 1.2rem; }
        .header-nav { display: flex; gap: 16px; align-items: center; }
        .header-nav a { color: #fff; text-decoration: none; font-size: 0.9rem; transition: opacity 0.2s; }
        .header-nav a:hover { opacity: 0.8; }
        .header-nav .logout { background: rgba(255, 255, 255, 0.2); padding: 6px 12px; border-radius: 4px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .hero { background: linear-gradient(135deg, #163b62, #2469a3); border-radius: 16px; color: #fff; padding: 28px; margin-bottom: 18px; }
        .hero h1 { margin: 0 0 6px; font-size: 1.8rem; }
        .hero p { margin: 0; font-size: 1rem; opacity: 0.95; }
        .stat { padding: 18px; text-align: center; }
        .stat strong { display: block; font-size: 1.6rem; color: #0b63a8; margin-bottom: 4px; }
        .stat span { font-size: 0.85rem; color: #666; }
        .badge { display: inline-block; background: #e74c3c; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; margin-left: 8px; }
        .deadline-item { padding: 12px; border-left: 4px solid #e74c3c; background: #fef6f5; margin-bottom: 8px; border-radius: 4px; }
        .deadline-item.soon { border-left-color: #f39c12; background: #fef9f0; }
        .deadline-item.future { border-left-color: #27ae60; background: #f0f9f5; }
        .deadline-date { font-weight: bold; color: #0b63a8; font-size: 0.85rem; }
        .deadline-title { color: #222; font-weight: 600; margin: 4px 0; }
        .deadline-matter { font-size: 0.8rem; color: #666; }
        .empty { padding: 18px; text-align: center; color: #999; font-size: 0.9rem; background: #f9f9f9; border-radius: 4px; }
        .activity-item { padding: 10px; border-bottom: 1px solid #eee; font-size: 0.85rem; }
        .activity-item:last-child { border-bottom: none; }
        .activity-user { font-weight: bold; color: #0b63a8; }
        .activity-time { color: #999; font-size: 0.75rem; }
        .action-btn { display: inline-block; padding: 7px 14px; background: #0b63a8; color: #fff; border-radius: 4px; text-decoration: none; font-size: 0.85rem; border: 1px solid #0b63a8; transition: all 0.2s; }
        .action-btn:hover { background: #084d85; border-color: #084d85; }
        .action-btn.secondary { background: transparent; color: #0b63a8; border: 1px solid #0b63a8; }
        .action-btn.secondary:hover { background: #f0f4f8; }
        .practice-areas { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
        .practice-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; text-align: center; transition: all 0.3s; }
        .practice-card:hover { border-color: #0b63a8; box-shadow: 0 2px 8px rgba(11, 99, 168, 0.1); }
        .practice-card h4 { margin: 0 0 6px; font-size: 0.9rem; color: #0b63a8; }
        .practice-card p { margin: 0 0 10px; font-size: 0.7rem; color: #666; line-height: 1.4; }
        .practice-card a { display: inline-block; padding: 4px 10px; background: #0b63a8; color: #fff; border-radius: 4px; text-decoration: none; font-size: 0.7rem; }
        .counsel-engine { background: linear-gradient(135deg, #1a472a 0%, #2d7a3d 100%); color: #fff; padding: 20px; border-radius: 8px; }
        .counsel-engine h3 { margin: 0 0 8px; font-size: 1.1rem; }
        .counsel-engine p { margin: 0 0 12px; font-size: 0.85rem; opacity: 0.9; }
        .counsel-engine .action-list { display: flex; flex-direction: column; gap: 8px; }
        .counsel-engine a { background: rgba(255, 255, 255, 0.2); color: #fff; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.4); transition: all 0.2s; }
        .counsel-engine a:hover { background: rgba(255, 255, 255, 0.3); border-color: rgba(255, 255, 255, 0.6); }
        .quick-actions { background: #f0f4f8; padding: 16px; border-radius: 8px; }
        .quick-actions h3 { margin: 0 0 12px; font-size: 1rem; color: #0b63a8; }
        .quick-actions p { margin: 0 0 12px; font-size: 0.85rem; color: #666; }
        .quick-actions .action-list { display: flex; flex-direction: column; gap: 8px; }
        .quick-actions a { display: inline-block; padding: 8px 12px; background: #0b63a8; color: #fff; border-radius: 4px; text-decoration: none; font-size: 0.8rem; transition: all 0.2s; }
        .quick-actions a:hover { background: #084d85; }
        .grid { display: grid; gap: 18px; }
        .grid-2 { grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); }
        .grid-3 { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        .card { background: #fff; border-radius: 8px; border: 1px solid #e0e0e0; padding: 16px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
        .card h2 { margin: 0 0 12px; font-size: 1.05rem; color: #222; }
        .card p { margin: 0; font-size: 0.85rem; color: #666; }
        .actions { display: flex; align-items: center; justify-content: space-between; }
        .footer { background: #163b62; color: #fff; padding: 16px 20px; text-align: center; margin-top: 40px; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="header">
    <h1>🏛️ AEP Legal Intelligence Platform</h1>
    <div class="header-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="client_list.php">Clients</a>
        <a href="matter_list.php">Matters</a>
        <a href="counsel_engine.php">Counsel Engine</a>
        <a href="knowledge_centre.php">Knowledge</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<div class="container">

    <!-- Hero Section -->
    <section class="hero">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
        <p>Manage clients, matters, legal tasks, deadlines, and analysis from one unified workspace.</p>
    </section>

    <!-- Summary Statistics -->
    <section class="grid grid-3">
        <div class="card stat">
            <strong><?php echo $counts['clients']; ?></strong>
            <span>Clients</span>
        </div>
        <div class="card stat">
            <strong><?php echo $counts['matters']; ?></strong>
            <span>Open Matters</span>
        </div>
        <div class="card stat">
            <strong><?php echo $counts['tasks']; ?></strong>
            <span>Open Tasks</span>
        </div>
    </section>

    <!-- AI Counsel Engine & Quick Actions -->
    <section class="grid grid-2" style="margin-top: 18px;">
        
        <div class="counsel-engine">
            <h3>🤖 AI Counsel Engine</h3>
            <p>AI-powered legal analysis: case assessment, appeal strategy, skeleton arguments, legal memoranda.</p>
            <div class="action-list">
                <a href="counsel_engine.php">📋 Case Analysis</a>
                <a href="counsel_engine.php?tab=appeal">⚖️ Appeal Review</a>
                <a href="counsel_engine.php?tab=skeleton">📑 Skeleton Building</a>
                <a href="counsel_engine.php?tab=strategy">🎯 Legal Strategy</a>
            </div>
        </div>

        <div class="quick-actions">
            <h3>⚡ Quick Actions</h3>
            <p>Start work without navigating modules.</p>
            <div class="action-list">
                <a href="client_create.php">➕ Add Client</a>
                <a href="matter_create.php">📂 Create Matter</a>
                <a href="task_create.php">✓ Add Task</a>
                <a href="deadline_create.php">📅 Add Deadline</a>
            </div>
        </div>

    </section>

    <!-- Upcoming Deadlines & Recent Activity -->
    <section class="grid grid-2" style="margin-top: 18px;">

        <div class="card">
            <div class="actions" style="justify-content: space-between; margin-bottom: 12px;">
                <h2>⏰ Upcoming Deadlines 
                    <?php if ($counts['deadlines'] > 0): ?>
                        <span class="badge"><?php echo $counts['deadlines']; ?> in 14 days</span>
                    <?php endif; ?>
                </h2>
                <a href="matter_list.php" class="action-btn secondary" style="margin: 0;">View All</a>
            </div>
            
            <?php if (empty($upcoming)): ?>
                <div class="empty">No upcoming deadlines. Add the first deadline to keep the workspace on track.</div>
            <?php else: ?>
                <?php foreach ($upcoming as $deadline): 
                    $dueDate = new DateTime($deadline['due_date']);
                    $today = new DateTime();
                    $interval = $today->diff($dueDate);
                    $daysLeft = (int)$interval->format('%r%a');
                    $class = 'future';
                    if ($daysLeft <= 2 && $daysLeft > 0) $class = 'soon';
                    if ($daysLeft <= 0) $class = 'soon';
                ?>
                    <div class="deadline-item <?php echo $class; ?>">
                        <div class="deadline-date">
                            <?php echo $dueDate->format('D, M j, Y'); ?> 
                            (<?php echo $daysLeft > 0 ? $daysLeft . ' days' : 'TODAY'; ?>)
                        </div>
                        <div class="deadline-title">
                            <?php echo htmlspecialchars($deadline['description'] ?? 'Untitled'); ?>
                        </div>
                        <?php if (!empty($deadline['matter_reference'])): ?>
                            <div class="deadline-matter">
                                📁 <?php echo htmlspecialchars($deadline['matter_reference']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>📋 Recent Activity</h2>
            
            <?php if (empty($activity)): ?>
                <div class="empty">Activity will appear as you create and update platform records.</div>
            <?php else: ?>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <?php foreach ($activity as $entry): 
                        $time = new DateTime($entry['created_at']);
                        $timeAgo = $time->format('M j, g:i A');
                    ?>
                        <li class="activity-item">
                            <div>
                                <span class="activity-user">System</span>
                                <span><?php echo htmlspecialchars($entry['action']); ?></span>
                            </div>
                            <div class="activity-time"><?php echo $timeAgo; ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </section>

    <!-- Practice Areas Overview -->
    <section class="card" style="margin-top: 18px;">
        <div style="margin-bottom: 14px;">
            <h2>⚖️ Practice Areas</h2>
            <p>Open a domain workbench or use its existing legal reference materials and templates.</p>
        </div>
        
        <div class="practice-areas">
            <div class="practice-card">
                <h4>📜 Contract Law</h4>
                <p>Formation, breach, remedies and enforcement of contracts.</p>
                <a href="contract.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>⚖️ Criminal Law</h4>
                <p>Offences, defences, procedure and sentencing in criminal matters.</p>
                <a href="criminal.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>👨‍⚖️ Employment Law</h4>
                <p>Workplace disputes, dismissal, discrimination and tribunal proceedings.</p>
                <a href="employment.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>👨‍👩‍👧 Family Law</h4>
                <p>Divorce, maintenance, custody and property settlements.</p>
                <a href="family_law.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>🛂 Immigration Law</h4>
                <p>Visas, leave to remain, asylum and deportation matters.</p>
                <a href="immigration_create.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>🏘️ Property Law</h4>
                <p>Landlord/tenant disputes, conveyancing and possession claims.</p>
                <a href="company_law.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>⚠️ Tort Law</h4>
                <p>Negligence, nuisance, defamation and civil wrongs.</p>
                <a href="tort.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>🛢️ Oil & Gas Law</h4>
                <p>Licensing, compliance, operator disputes and environmental issues.</p>
                <a href="oil_gas.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>👤 Human Rights</h4>
                <p>Fundamental rights, freedoms and ECHR claims.</p>
                <a href="human-rights.php">Open</a>
            </div>
            <div class="practice-card">
                <h4>🏛️ Administrative Law</h4>
                <p>Public body decisions, judicial review and tribunal challenges.</p>
                <a href="admin_law.php">Open</a>
            </div>
        </div>
    </section>

    <!-- Knowledge Centre -->
    <section class="card" style="margin-top: 18px;">
        <div class="actions" style="justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h2>📚 Knowledge Centre</h2>
                <p>Legal library, templates, domain maps, Latin maxims and phrase banks by practice area.</p>
            </div>
            <a href="knowledge_centre.php" class="action-btn">View All</a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px;">
            <a href="legal_library.php" class="action-btn" style="text-align: center; padding: 10px;">📖 Legal Library</a>
            <a href="domain_templates.php" class="action-btn" style="text-align: center; padding: 10px;">📋 Templates</a>
            <a href="domain_map.php" class="action-btn" style="text-align: center; padding: 10px;">🗺️ Domain Map</a>
            <a href="latin_maxims.php" class="action-btn" style="text-align: center; padding: 10px;">📜 Latin Maxims</a>
        </div>
    </section>

</div>

<div class="footer">
    <p>&copy; 2026 AEP Legal Intelligence Platform. All rights reserved.</p>
</div>

</body>
</html>
