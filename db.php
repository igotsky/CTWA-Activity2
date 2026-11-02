<?php
$host    = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ctwa_activity1';

$conn = new mysqli($host, $db_user, $db_pass);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db_name);
$conn->set_charset('utf8mb4');

$conn->query("CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>
