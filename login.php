<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html'); exit;
}

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if (!$email || !$pass) {
    header('Location: index.html?err=fill'); exit;
}

$stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($id, $name, $hash);
if ($stmt->fetch() && password_verify($pass, $hash)) {
    $_SESSION['user'] = ['id' => $id, 'name' => $name, 'email' => $email];
    $stmt->close();
    header('Location: dashboard.php'); exit;
}
$stmt->close();
header('Location: index.html?err=invalid'); exit;
?>
