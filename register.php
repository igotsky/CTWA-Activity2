<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html'); exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$pass    = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

if (!$name || !$email || !$pass) {
    header('Location: index.html?err=fill'); exit;
}
if ($pass !== $confirm) {
    header('Location: index.html?err=nomatch'); exit;
}
if (strlen($pass) < 6) {
    header('Location: index.html?err=short'); exit;
}

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header('Location: index.html?err=exists'); exit;
}
$stmt->close();

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?,?,?)');
$stmt->bind_param('sss', $name, $email, $hash);
if ($stmt->execute()) {
    $_SESSION['user'] = ['id' => $stmt->insert_id, 'name' => $name, 'email' => $email, 'role' => 'user'];
    $stmt->close();
    header('Location: dashboard.php'); exit;
}
$stmt->close();
header('Location: index.html?err=fail'); exit;
?>