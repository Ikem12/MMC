<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: appeal_list.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM grounds_of_appeal WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { header('Location: appeal_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Grounds of Appeal — <?php echo htmlspecialchars($r['case_title']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Times New Roman',serif;background:#fff;color:#000;font-size:12*
