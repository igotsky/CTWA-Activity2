<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../index.html'); exit; }
if ($_SESSION['user']['role'] !== 'admin') { header('Location: ../dashboard.php?err=admin'); exit; }
require '../db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id && $id !== $_SESSION['user']['id']) {
    $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
header('Location: users.php?deleted=1');
exit;
?>