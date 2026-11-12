<?php
session_start();
if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    header('Location: index.html?forgot=step1'); exit;
}
require 'db.php';

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$pass || !$confirm) {
        header('Location: index.html?forgot=step3&err=fill'); exit;
    } elseif (strlen($pass) < 6) {
        header('Location: index.html?forgot=step3&err=short'); exit;
    } elseif ($pass !== $confirm) {
        header('Location: index.html?forgot=step3&err=nomatch'); exit;
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->bind_param('ss', $hash, $email);
        $stmt->execute();
        $stmt->close();
        unset($_SESSION['reset_email'], $_SESSION['reset_verified']);
        header('Location: index.html?forgot=success'); exit;
    }
}
header('Location: index.html?forgot=step1'); exit;
?>