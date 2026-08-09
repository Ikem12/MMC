<?php
function getDB() {
    $pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function loginUser($username, $password) {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return 'Invalid username or password.';
    if (!password_verify($password, $user['password'])) return 'Invalid username or password.';
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    return true;
}