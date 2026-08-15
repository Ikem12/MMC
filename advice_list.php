<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once __DIR__ . '/csrf.php';
csrf_token(); // ensure token exists

// Handle delete (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_verify()) { die('Invalid CSRF token.'); }
    $pdo->prepare("DELETE FROM legal_advice WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    header('Location: advice_list.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM legal_advice WHERE client_name LIKE ? OR file_no LIKE ? OR legal_domain LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM legal_advice ORDER BY created_at DESC");
}
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Legal Advice List — AEP</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar a{color:#fff;text-decoration:none;font-size:0.9rem}
.topbar a:hover{text-decoration:underline}
.container{max-width:1100px;margin:30px auto;padding:0 20px}
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
h2{color:#1a3c5e;font-size:1.3rem}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-primary:hover{background:#122840}
.btn-danger{background:#dc3545;color:#fff}
.btn-danger:hover{background:#b02a37}
.btn-info{background:#17a2b8;color:#fff}
.btn-info:hover{background:#117a8b}
.btn-warning{background:#ffc107;color:#333}
.btn-warning:hover{background:#e0a800}
.search-bar{display:flex;gap:10px;margin-bottom:20px}
.search-bar input{flex:1;padding:9px 14px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem}
.search-bar button{padding:9px 20px;background:#1a3c5e;color:#fff;border:none;border-radius:4px;cursor:pointer}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.08)}
table{width:100%;border-collapse:collapse;font-size:0.88rem}
th{background:#1a3c5e;color:#fff;padding:11px 12px;text-align:left}
td{padding:10px 12px;border-bottom:1px solid #f0f0f0;vertical-align:top}
tr:hover td{background:#f8f9ff}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.75rem;font-weight:bold}
.badge-draft{background:#fff3cd;color:#856404}
.badge-final{background:#d4edda;color:#155724}
.badge-delivered{background:#cce5ff;color:#004085}
.empty{text-align:center;padding:40px;color:#888}
.action-btns{display:flex;gap:6px;flex-wrap:wrap}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.stat-card{background:#fff;border-radius:8px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.06*
