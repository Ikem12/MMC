<?php
// FILE: view.php
// View a single case for AEP Legal Platform
session_start();

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "Invalid case id. <a href='list.php'>Back to list</a>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM cases WHERE id = :id");
$stmt->execute([':id' => $id]);
$case = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$case) {
    echo "Case not found. <a href='list.php'>Back to list</a>";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>View Case — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;max-width:900px;margin:24px auto;padding:18px;color:#222}
    .banner{font-size:12px;color:#eef;padding:6px 10px;background:#073e63;border-radius:4px;display:inline-block;margin-left:8px}
    .meta{color:#666;font-size:13px;margin-bottom:12px}
    .field{margin-top:10px}
    pre{white-space:pre-wrap;background:#fafafa;padding:12px;border:1px solid #eee;border-radius:4px}
    a.button{display:inline-block;padding:6px 10px;background:#0b63a8;color:#fff;text-decoration:none;border-radius:4px}
  </style>
</head>
<body>
  <h1>Case: <?php echo htmlspecialchars($case['title']); ?> <span class="banner">FILE: view.php</span></h1>

  <div class="meta">
    ID: <?php echo htmlspecialchars($case['id']); ?> |
    Created: <?php echo htmlspecialchars($case['created_at']); ?> |
    Type: <?php echo htmlspecialchars($case['case_type']); ?>
  </div>

  <div class="field"><strong>Client name</strong><br><?php echo htmlspecialchars($case['client_name']); ?></div>
  <div class="field"><strong>Address</strong><br><?php echo nl2br(htmlspecialchars($case['address'])); ?></div>

  <div class="field"><strong>Facts</strong><br>
    <pre><?php echo htmlspecialchars($case['facts']); ?></pre>
  </div>

  <div class="field"><strong>Instructions / Notes</strong><br>
    <pre><?php echo htmlspecialchars($case['instructions']); ?></pre>
  </div>

  <p style="margin-top:14px">
    <a class="button" href="list.php">Back to list</a>
    <a href="create.php" style="margin-left:8px">Create new</a>
  </p>
</body>
</html>