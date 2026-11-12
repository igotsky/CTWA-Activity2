<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host    = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ctwa_activity1';

$conn = new mysqli($host, $db_user, $db_pass);
$conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db_name);
$conn->set_charset('utf8mb4');

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

try {
    $conn->query("ALTER TABLE users ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER email");
} catch (mysqli_sql_exception $e) {}

$conn->query("CREATE TABLE IF NOT EXISTS tickets (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category    ENUM('technical','billing','general','feature','bug') NOT NULL DEFAULT 'general',
    priority    ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    status      ENUM('open','in_progress','pending','closed') NOT NULL DEFAULT 'open',
    assigned_to INT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at   TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (user_id),
    INDEX (status),
    INDEX (assigned_to)
) ENGINE=InnoDB");

try {
    $conn->query("ALTER TABLE tickets ADD COLUMN category ENUM('technical','billing','general','feature','bug') NOT NULL DEFAULT 'general' AFTER description");
    $conn->query("ALTER TABLE tickets ADD COLUMN priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium' AFTER category");
    $conn->query("ALTER TABLE tickets ADD COLUMN status ENUM('open','in_progress','pending','closed') NOT NULL DEFAULT 'open' AFTER priority");
    $conn->query("ALTER TABLE tickets ADD COLUMN assigned_to INT NULL AFTER status");
    $conn->query("ALTER TABLE tickets ADD COLUMN closed_at TIMESTAMP NULL AFTER updated_at");
} catch (mysqli_sql_exception $e) {}

$conn->query("CREATE TABLE IF NOT EXISTS comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    is_internal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (ticket_id),
    INDEX (user_id)
) ENGINE=InnoDB");

try {
    $conn->query("ALTER TABLE comments ADD COLUMN is_internal BOOLEAN NOT NULL DEFAULT FALSE AFTER content");
} catch (mysqli_sql_exception $e) {}

$conn->query("CREATE TABLE IF NOT EXISTS ratings (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT NOT NULL,
    user_id    INT NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    feedback   TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (ticket_id, user_id)
) ENGINE=InnoDB");
?>