<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../index.html'); exit; }
if ($_SESSION['user']['role'] !== 'admin') { header('Location: ../dashboard.php?err=admin'); exit; }
$user = $_SESSION['user'];
require '../db.php';

$totalUsers = (int)$conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$totalTickets = (int)$conn->query('SELECT COUNT(*) FROM tickets')->fetch_row()[0];
$openTickets = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetch_row()[0];
$closedTickets = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE status='closed'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Admin Dashboard - Support.ly</title>
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
    max-width: 1000px;
    margin: var(--space-8) auto;
    padding: 0 var(--space-6);
  }
  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
    margin-bottom: var(--space-8);
  }
  .stat {
    background: var(--color-bg-elevated);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-5);
    box-shadow: var(--shadow-md);
    text-align: center;
    transition: var(--transition);
  }
  .stat:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }
  .stat .val {
    font-size: 32px;
    font-weight: 800;
    color: var(--color-primary);
    display: block;
    margin-bottom: var(--space-1);
    letter-spacing: -0.5px;
  }
  .stat .lbl {
    font-size: 12px;
    color: var(--color-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
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
  .btn {
    display: inline-block;
    padding: var(--space-3) var(--space-4);
    font-size: 14px;
    font-weight: 600;
    background: var(--color-primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
  }
  .btn:hover {
    background: var(--color-primary-hover);
    transform: translateY(-1px);
  }
  .btn:active { transform: translateY(0); }
  .btn-secondary {
    background: transparent;
    color: var(--color-text);
    border: 1px solid var(--color-border);
  }
  .btn-secondary:hover {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    color: var(--color-primary);
  }
  .btn-danger { background: var(--color-error); }
  .btn-danger:hover { background: #c62828; }
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
  .wrap { max-width: 1000px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-8); }
  .stat { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-5); box-shadow: var(--shadow-md); text-align: center; transition: var(--transition); }
  .stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
  .stat .val { font-size: 32px; font-weight: 800; color: var(--color-primary); display: block; margin-bottom: var(--space-1); letter-spacing: -0.5px; }
  .stat .lbl { font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow-md); }
  .card h2 { font-size: 18px; font-weight: 700; color: var(--color-text); margin-bottom: var(--space-4); }
  .btn { display: inline-block; padding: var(--space-3) var(--space-4); font-size: 14px; font-weight: 600; background: var(--color-primary); color: #fff; border: none; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); text-decoration: none; }
  .btn:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }
  .btn-secondary { background: transparent; color: var(--color-text); border: 1px solid var(--color-border); }
  .btn-secondary:hover { background: var(--color-primary-light); border-color: var(--color-primary); color: var(--color-primary); }
  .btn-danger { background: var(--color-error); }
  .btn-danger:hover { background: #c62828; }
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
  .topbar button:hover { background: var(--color-error_light); border-color: var(--color-error); color: var(--color-error); }
  .wrap { max-width: 1000px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-8); }
  .stat { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border_radius: var(--radius-xl); padding: var(--space-5); box_shadow: var(--shadow-md); text_align: center; transition: var(--transition); }
  .stat:hover { transform: translateY(-2px); box_shadow: var(--shadow-lg); }
  .stat .val { font_size: 32px; font_weight: 800; color: var(--color-primary); display: block; margin_bottom: var(--space-1); letter_spacing: -0.5px; }
  .stat .lbl { font_size: 12px; color: var(--color-text-muted); text_transform: uppercase; letter_spacing: 0.5px; font_weight: 600; }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border_radius: var(--radius-xl); padding: var(--space-6); box_shadow: var(--shadow-md); }
  .card h2 { font_size: 18px; font_weight: 700; color: var(--color_text); margin_bottom: var(--space-4); }
  .btn { display: inline_block; padding: var(--space-3) var(--space-4); font_size: 14px; font_weight: 600; background: var(--color_primary); color: #fff; border: none; border_radius: var(--radius-md); cursor: pointer; transition: var(--transition); text_decoration: none; }
  .btn:hover { background: var(--color_primary_hover); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }
  .btn-secondary { background: transparent; color: var(--color_text); border: 1px solid var(--color_border); }
  .btn-secondary:hover { background: var(--color_primary_light); border_color: var(--color_primary); color: var(--color_primary); }
  .btn-danger { background: var(--color_error); }
  .btn-danger:hover { background: #c62828; }
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">Admin Dashboard</span>
    </div>
    <div class="topbar-links">
      <a href="tickets.php">All Tickets</a>
      <a href="users.php">User Management</a>
      <form action="../logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <div class="welcome" style="margin-bottom: var(--space-8);">
      <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
      <p style="margin-top: var(--space-2);"><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    <div class="stats">
      <div class="stat"><span class="val"><?php echo $totalUsers = (int)$conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0]; ?></span><span class="lbl">Total Users</span></div>
      <div class="stat"><span class="val"><?php echo $totalTickets = (int)$conn->query('SELECT COUNT(*) FROM tickets')->fetch_row()[0]; ?></span><span class="lbl">Total Tickets</span></div>
      <div class="stat"><span class="val"><?php echo $openTickets = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetch_row()[0]; ?></span><span class="lbl">Open</span></div>
      <div class="stat"><span class="val"><?php echo $closedTickets = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE status='closed'")->fetch_row()[0]; ?></span><span class="lbl">Closed</span></div>
    </div>
    <div class="card">
      <h2>Quick Actions</h2>
      <a href="tickets.php" class="btn">View All Tickets</a>
      <a href="users.php" class="btn-secondary" style="margin-left:var(--space-3);">Manage Users</a>
    </div>
  </div>
</body>
</html>