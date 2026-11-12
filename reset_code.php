<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
    header('Location: index.html?forgot=step1'); exit;
}
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html?forgot=step1'); exit;
}

$boxes = $_POST['code'] ?? [];
$code = implode('', array_filter($boxes));
if (strlen($code) !== 6 || !ctype_digit($code)) {
    header('Location: index.html?forgot=step2&err=code'); exit;
}

$_SESSION['reset_verified'] = true;
header('Location: index.html?forgot=step3'); exit;
?>