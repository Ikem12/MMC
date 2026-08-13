<?php
require_once 'auth.php';
require_once 'init_db.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $maxim    = trim($_POST['maxim']);
    $meaning  = trim($_POST['meaning']);
    $category = trim($_POST['category']);
    $details  = trim($_POST['details']);

    if($maxim && $meaning){
        $stmt = $pdo->prepare("INSERT INTO latin_maxims (maxim, meaning, category, details, created_at) VALUES (?,?,?,?,datetime('now'))");
        $stmt->execute([$maxim, $meaning, $category, $details]);
        $success = 'Maxim added successfully!';
    } else {
        $error = 'Maxim and Meaning are required!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Latin Maxim</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>➕ Add Latin Maxim</h2>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Maxim *</label>
            <input type="text" name="maxim" class="form-control" placeholder="e.g. Audi alteram partem" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Meaning *</label>
            <input type="text" name="meaning" class="form-control" placeholder="English meaning" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="General">General</option>
                <option value="Criminal Law">Criminal Law</option>
                <option value="Contract Law">Contract Law</option>
                <option value="Tort Law">Tort Law</option>
                <option value="Constitutional Law">Constitutional Law</option>
                <option value="Equity">Equity</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Full Details / Usage</label>
            <textarea name="details" class="form-control" rows="5" placeholder="Explain usage and case examples..."></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save Maxim</button>
        <a href="latin_maxims.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>