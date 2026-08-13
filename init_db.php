<?php
// init_db.php - create SQLite DB (data/aep.sqlite) and seed admin user
// Run once: php init_db.php

$dbFile = __DIR__ . '/data/aep.sqlite';
@mkdir(dirname($dbFile), 0755, true);

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// create tables
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  is_admin INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  client_name TEXT,
  address TEXT,
  date TEXT,
  case_type TEXT,
  facts TEXT,
  instructions TEXT,
  created_at TEXT
);
CREATE TABLE IF NOT EXISTS hr_requests (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_id INTEGER,
  title TEXT,
  note TEXT,
  requested_by TEXT,
  requested_at TEXT,
  status TEXT DEFAULT 'pending'
);
");

// seed admin if missing
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
$stmt->execute([':u' => 'admin']);
if ($stmt->fetchColumn() == 0) {
    $pw = 'change-me'; // change immediately after first login
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username,password_hash,is_admin,created_at) VALUES (:u,:p,1,:c)");
    $stmt->execute([':u' => 'admin', ':p' => $hash, ':c' => date('c')]);
    echo "Seeded admin user: username=admin password=change-me\n";
} else {
    echo "Admin user already present.\n";
}

echo "Database initialized at: $dbFile\n";