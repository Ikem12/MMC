<?php
// FILE: list.php
// List cases for AEP Legal Platform
session_start();

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT id,title,client_name,address,case_type,created_at FROM cases ORDER BY created_at DESC");
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Cases — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;max-width:980px;margin:24px auto;padding:18px;color:#222}
    .banner{font-size:12px;color:#eef;padding:6px 10px;background:#073e63;border-radius:4px;display:inline-block;margin-left:8px}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:8px;border:1px solid #e6e6e6;text-align:left}
    th{background:#f5f8fb}
    a.button{display:inline-block;padding:6px 10px;background:#0b63a8;color:#fff;text-decoration:none;border-radius:4px}
  </style>
</head>
<body>
  <h1>Cases <span class="banner">FILE: list.php</span></h1>

  <p><a class="button" href="create.php">New case</a> <a href="index.php" style="margin-left:8px">Home</a></p>

  <?php if (empty($cases)): ?>
    <p>No cases found. Create one using the "New case" button.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Client</th>
          <th>Address</th>
          <th>Type</th>
          <th>Created</th>
          <th>View</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $c): ?>
          <tr>
            <td><?php echo htmlspecialchars($c['id']); ?></td>
            <td><?php echo htmlspecialchars($c['title']); ?></td>
            <td><?php echo htmlspecialchars($c['client_name']); ?></td>
            <td><?php echo htmlspecialchars($c['address']); ?></td>
            <td><?php echo htmlspecialchars($c['case_type']); ?></td>
            <td><?php echo htmlspecialchars($c['created_at']); ?></td>
            <td><a href="view.php?id=<?php echo urlencode($c['id']); ?>">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>