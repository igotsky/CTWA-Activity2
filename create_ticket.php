<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.html'); exit; }
$user = $_SESSION['user'];

// Redirect admins to admin tickets
if ($user['role'] === 'admin') { header('Location: admin/tickets.php'); exit; }

require 'db.php';
$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category'] ?? 'general';
    $priority    = $_POST['priority'] ?? 'medium';

    if (!$title || !$description) {
        header('Location: create_ticket.php?err=fill'); exit;
    }

    $stmt = $conn->prepare("INSERT INTO tickets (user_id, title, description, category, priority) VALUES (?,?,?,?,?)");
    $stmt->bind_param('issss', $user_id, $title, $description, $category, $priority);
    $stmt->execute();
    header('Location: tickets.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>New Ticket - Support.ly</title>
<style>
  :root {
    --color-primary: #667eea;
    --color-primary-hover: #5568d3;
    --color-primary-light: rgba(102,126,234,0.1);
    --color-secondary: #2c3e50;
    --color-bg: #1a252f;
    --color-bg-elevated: #243447;
    --color-surface: #2d3f54;
    --color-border: rgba(255,255,255,0.08);
    --color-text: #e8eaf0;
    --color-text-muted: #8b9cb0;
    --color-error: #e53935;
    --color-error-light: rgba(229,57,53,0.1);
    --color-success: #43a047;
    --color-success-light: rgba(67,160,71,0.1);
    --color-warning: #fdd835;
    --color-warning-light: rgba(253,216,53,0.1);
    --color-info: #2196f3;
    --color-info-light: rgba(33,150,243,0.1);
    --radius-sm: 6px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.2);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.25);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.3);
    --shadow-xl: 0 16px 48px rgba(0,0,0,0.4);
    --transition: 150ms ease;
    --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
    --space-1: 4px; --space-2: 8px; --space-3: 12px; --space-4: 16px; --space-5: 20px; --space-6: 24px; --space-8: 32px;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: var(--font-sans);
    background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
    min-height: 100vh;
    color: var(--color-text);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  .topbar {
    background: var(--color-bg-elevated);
    border-bottom: 1px solid var(--color-border);
    padding: var(--space-4) var(--space-6);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 40;
  }
  .topbar-brand {
    display: flex;
    align-items: center;
    gap: var(--space-4);
  }
  .topbar-brand .brand-name {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-text);
    letter-spacing: -0.5px;
  }
  .topbar-brand .brand-divider {
    width: 1px;
    height: 24px;
    background: var(--color-border);
  }
  .topbar-brand .page-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-text-muted);
  }
  .topbar-links {
    display: flex;
    align-items: center;
    gap: var(--space-3);
  }
  .topbar a {
    background: transparent;
    color: var(--color-text-muted);
    border: 1px solid var(--color-border);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: var(--transition);
  }
  .topbar a:hover {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    color: var(--color-primary);
  }
  .topbar button {
    background: transparent;
    color: var(--color-text-muted);
    border: 1px solid var(--color-border);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: var(--transition);
  }
  .topbar button:hover {
    background: var(--color-error-light);
    border-color: var(--color-error);
    color: var(--color-error);
  }
  .wrap {
    max-width: 700px;
    margin: var(--space-8) auto;
    padding: 0 var(--space-6);
  }
  .card {
    background: var(--color-bg-elevated);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    box-shadow: var(--shadow-md);
  }
  .card h2 {
    font-size: 18px;
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: var(--space-4);
  }
  label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-muted);
    margin-bottom: var(--space-2);
    margin-top: var(--space-4);
  }
  label:first-child { margin-top: 0; }
  input, select, textarea {
    width: 100%;
    padding: var(--space-3) var(--space-4);
    margin-bottom: var(--space-4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: 14px;
    background: var(--color-bg);
    color: var(--color-text);
    transition: var(--transition);
  }
  input:focus, select:focus, textarea:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px var(--color-primary-light);
  }
  .pw-wrap { position: relative; }
  .pw-wrap input { padding-right: 48px; }
  .pw-toggle {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    background: transparent; border: none; cursor: pointer; color: var(--color-text-muted);
    padding: 6px; display: flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; border-radius: var(--radius-sm);
    transition: var(--transition);
  }
  .pw-toggle:hover { background: var(--color-primary-light); color: var(--color-primary); }
  .pw-toggle svg { width: 20px; height: 20px; }
  button {
    width: 100%;
    padding: var(--space-3) var(--space-4);
    background: var(--color-primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
  }
  button:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
  button:active { transform: translateY(0); }
  .msg { margin-bottom: var(--space-4); font-size: 13px; }
  .msg.error { color: var(--color-error); background: var(--color-error-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .msg.success { color: var(--color-success); background: var(--color-success-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .topbar {
    background: var(--color-bg-elevated);
    border-bottom: 1px solid var(--color-border);
    padding: var(--space-4) var(--space-6);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 40;
  }
  .topbar-brand {
    display: flex;
    align-items: center;
    gap: var(--space-4);
  }
  .topbar-brand .brand-name {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-text);
    letter-spacing: -0.5px;
  }
  .topbar-brand .brand-divider {
    width: 1px;
    height: 24px;
    background: var(--color-border);
  }
  .topbar-brand .page-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-text-muted);
  }
  .topbar-links {
    display: flex;
    align-items: center;
    gap: var(--space-3);
  }
  .topbar a {
    background: transparent;
    color: var(--color-text-muted);
    border: 1px solid var(--color-border);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: var(--transition);
  }
  .topbar a:hover {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    color: var(--color-primary);
  }
  .topbar button {
    background: transparent;
    color: var(--color-text-muted);
    border: 1px solid var(--color-border);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: var(--transition);
  }
  .topbar button:hover {
    background: var(--color-error-light);
    border-color: var(--color-error);
    color: var(--color-error);
  }
  .wrap { max-width: 700px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow-md); }
  .card h2 { font-size: 18px; font-weight: 700; color: var(--color-text); margin-bottom: var(--space-4); }
  label { display: block; font-size: 13px; font-weight: 500; color: var(--color-text-muted); margin-bottom: var(--space-2); margin-top: var(--space-4); }
  label:first-child { margin-top: 0; }
  input, select, textarea { width: 100%; padding: var(--space-3) var(--space-4); margin-bottom: var(--space-4); border: 1px solid var(--color-border); border-radius: var(--radius-md); font-size: 14px; background: var(--color-bg); color: var(--color-text); transition: var(--transition); }
  input:focus, select:focus, textarea:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-light); }
  .pw-wrap { position: relative; }
  .pw-wrap input { padding-right: 48px; }
  .pw-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--color-text-muted); padding: 6px; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: var(--radius-sm); transition: var(--transition); }
  .pw-toggle:hover { background: var(--color-primary-light); color: var(--color-primary); }
  .pw-toggle svg { width: 20px; height: 20px; }
  button { width: 100%; padding: var(--space-3) var(--space-4); background: var(--color-primary); color: #fff; border: none; border-radius: var(--radius-md); font-size: 15px; font-weight: 600; cursor: pointer; transition: var(--transition); }
  button:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
  button:active { transform: translateY(0); }
  .msg { margin-bottom: var(--space-4); font-size: 13px; }
  .msg.error { color: var(--color-error); background: var(--color-error-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .msg.success { color: var(--color-success); background: var(--color-success-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .topbar { background: var(--color-bg-elevated); border-bottom: 1px solid var(--color-border); padding: var(--space-4) var(--space-6); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 40; }
  .topbar-brand { display: flex; align-items: center; gap: var(--space-4); }
  .topbar-brand .brand-name { font-size: 20px; font-weight: 700; color: var(--color-text); letter-spacing: -0.5px; }
  .topbar-brand .brand-divider { width: 1px; height: 24px; background: var(--color-border); }
  .topbar-brand .page-title { font-size: 16px; font-weight: 600; color: var(--color-text-muted); }
  .topbar-links { display: flex; align-items: center; gap: var(--space-3); }
  .topbar a { background: transparent; color: var(--color-text-muted); border: 1px solid var(--color-border); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); text-decoration: none; font-size: 13px; font-weight: 500; transition: var(--transition); }
  .topbar a:hover { background: var(--color-primary-light); border-color: var(--color-primary); color: var(--color-primary); }
  .topbar button { background: transparent; color: var(--color-text-muted); border: 1px solid var(--color-border); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); cursor: pointer; font-size: 13px; font-weight: 500; transition: var(--transition); }
  .topbar button:hover { background: var(--color-error-light); border-color: var(--color-error); color: var(--color-error); }
  .wrap { max-width: 700px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow-md); }
  .card h2 { font-size: 18px; font-weight: 700; color: var(--color-text); margin-bottom: var(--space-4); }
  label { display: block; font-size: 13px; font-weight: 500; color: var(--color-text-muted); margin-bottom: var(--space-2); margin-top: var(--space-4); }
  label:first-child { margin-top: 0; }
  input, select, textarea { width: 100%; padding: var(--space-3) var(--space-4); margin-bottom: var(--space-4); border: 1px solid var(--color-border); border-radius: var(--radius-md); font-size: 14px; background: var(--color-bg); color: var(--color-text); transition: var(--transition); }
  input:focus, select:focus, textarea:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-light); }
  .pw-wrap { position: relative; }
  .pw-wrap input { padding-right: 48px; }
  .pw-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--color-text-muted); padding: 6px; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: var(--radius-sm); transition: var(--transition); }
  .pw-toggle:hover { background: var(--color-primary-light); color: var(--color-primary); }
  .pw-toggle svg { width: 20px; height: 20px; }
  button { width: 100%; padding: var(--space-3) var(--space-4); background: var(--color-primary); color: #fff; border: none; border-radius: var(--radius-md); font-size: 15px; font-weight: 600; cursor: pointer; transition: var(--transition); }
  button:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
  button:active { transform: translateY(0); }
  .msg { margin-bottom: var(--space-4); font-size: 13px; }
  .msg.error { color: var(--color-error); background: var(--color-error-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .msg.success { color: var(--color-success); background: var(--color-success-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">New Ticket</span>
    </div>
    <div class="topbar-links">
      <a href="tickets.php">My Tickets</a>
      <a href="dashboard.php">Dashboard</a>
      <form action="logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <?php if (isset($_GET['err']) && $_GET['err'] === 'fill'): ?><div class="msg error">Please fill title and description.</div><?php endif; ?>
    <div class="card">
      <h2>Create New Ticket</h2>
      <form method="post">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required />
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6" required placeholder="Describe your issue in detail..."></textarea>
        <label>Category</label>
        <select name="category">
          <option value="technical">Technical</option>
          <option value="billing">Billing</option>
          <option value="general" selected>General</option>
          <option value="feature">Feature Request</option>
          <option value="bug">Bug Report</option>
        </select>
        <label>Priority</label>
        <select name="priority">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
        </select>
        <button type="submit">Submit Ticket</button>
      </form>
    </div>
  </div>
</body>
</html>