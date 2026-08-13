<?php
require_once 'auth.php';
require_once 'init_db.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title       = trim($_POST['title']);
    $case_type   = trim($_POST['case_type']);
    $petitioner  = trim($_POST['petitioner']);
    $respondent  = trim($_POST['respondent']);
    $children    = trim($_POST['children']);
    $relief      = trim($_POST['relief']);
    $court       = trim($_POST['court']);
    $summary     = trim($_POST['summary']);

    if($title && $petitioner){
        $stmt = $pdo->prepare("INSERT INTO family_cases 
            (title, case_type, petitioner, respondent, children, relief, court, summary, status, created_at) 
            VALUES (?,?,?,?,?,?,?,?,'open',datetime('now'))");
        $stmt->execute([$title, $case_type, $petitioner, $respondent, $children, $relief, $court, $summary]);
        $success = 'Family law case added successfully!';
    } else {
        $error = 'Title and Petitioner are required!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Family Law Case</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>➕ New Family Law Case</h2>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Case Title *</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Case Type</label>
            <select name="case_type" class="form-select">
                <option value="*
