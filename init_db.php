<?php
// init_db.php - create SQLite DB (data/aep.sqlite) and seed admin user
// Run once: php init_db.php
require_once __DIR__ . '/includes/database.php';

$dbFile = __DIR__ . '/data/aep.sqlite';

// aep_db() creates the data/ directory (if needed) and runs every schema
// migration (platform tables + the 15 domain-specific case tables).
$pdo = aep_db();

// seed admin if missing
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
$stmt->execute([':u' => 'admin']);
if ($stmt->fetchColumn() == 0) {
    $pw = 'change-me'; // change immediately after first login
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username,password_hash,is_admin,created_at) VALUES (:u,:p,1,:c)");
    $stmt->execute([':u' => 'admin', ':p' => $hash, ':c' => date('c')]);
    echo "Seeded admin user: username=admin ******\n";
} else {
    echo "Admin user already present.\n";
}

echo "Database initialized at: $dbFile\n";
