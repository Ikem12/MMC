<?php
require_once 'auth.php';
require_once 'init_db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM family_cases WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if(!$c){ header('Location: family_law.php'); exit; }
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($c['title']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <a href="family_law.php" class="btn btn-secondary mb-3">← Back</a>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>👨‍👩‍👧 <?= htmlspecialchars($c['title']) ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Case Type:</strong> <?= htmlspecialchars($c['case_type']) ?></p>
                    <p><strong>Petitioner:</strong> <?= htmlspecialchars($c['petitioner']) ?></p>
                    <p><strong>Respondent:</strong> <?= htmlspecialchars($c['respondent']) ?></p>
                    <p><strong>Children:</strong> <?= htmlspecialchars($c['children']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Relief Sought:</strong> <?= htmlspecialchars($c['relief'])