<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.html'); exit; }
$user = $_SESSION['user'];
require 'db.php';

// Redirect admins to admin dashboard
if ($user['role'] === 'admin') { header('Location: admin/dashboard.php'); exit; }

$uid = $user['id'];

$openCount = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE user_id=$uid AND status='open'")->fetch_row()[0];
$inProgressCount = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE user_id=$uid AND status='in_progress'")->fetch_row()[0];
$pendingCount = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE user_id=$uid AND status='pending'")->fetch_row()[0];
$closedCount = (int)$conn->query("SELECT COUNT(*) FROM tickets WHERE user_id=$uid AND status='closed'")->fetch_row()[0];

$recentTickets = $conn->query("SELECT * FROM tickets WHERE user_id=$uid ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard - Support.ly Support</title>
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
  .welcome {
    background: var(--color-bg-elevated);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    box-shadow: var(--shadow-lg);
    margin-bottom: var(--space-6);
  }
  .welcome h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: var(--space-2);
  }
  .welcome p {
    color: var(--color-text-muted);
    font-size: 14px;
  }
  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: var(--space-4);
    margin-bottom: var(--space-6);
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
  .stat.open .val { color: var(--color-info); }
  .stat.in_progress .val { color: var(--color-warning); }
  .stat.pending .val { color: var(--color-primary); }
  .stat.closed .val { color: var(--color-success); }
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
  .btn:active {
    transform: translateY(0);
  }
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
  .btn-danger {
    background: var(--color-error);
  }
  .btn-danger:hover {
    background: #c62828;
  }
  .msg {
    margin-bottom: var(--space-4);
    font-size: 13px;
  }
  .msg.error {
    color: var(--color-error);
    background: var(--color-error-light);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-md);
  }
  .msg.success {
    color: var(--color-success);
    background: var(--color-success-light);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-md);
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
  .welcome {
    background: var(--color-bg-elevated);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    box-shadow: var(--shadow-lg);
    margin-bottom: var(--space-6);
  }
  .welcome h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: var(--space-2);
  }
  .welcome p {
    color: var(--color-text-muted);
    font-size: 14px;
  }
  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: var(--space-4);
    margin-bottom: var(--space-6);
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
  .stat.open .val { color: var(--color-info); }
  .stat.in_progress .val { color: var(--color-warning); }
  .stat.pending .val { color: var(--color-primary); }
  .stat.closed .val { color: var(--color-success); }
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
  .btn:active {
    transform: translateY(0);
  }
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
  .btn-danger {
    background: var(--color-error);
  }
  .btn-danger:hover {
    background: #c62828;
  }
  .msg {
    margin-bottom: var(--space-4);
    font-size: 13px;
  }
  .msg.error {
    color: var(--color-error);
    background: var(--color-error-light);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-md);
  }
  .msg.success {
    color: var(--color-success);
    background: var(--color-success-light);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-md);
  }
  .table-responsive { overflow-x: auto; }
  table {
    width: 100%;
    border-collapse: collapse;
    background: var(--color-bg-elevated);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-md);
  }
  th, td { padding: var(--space-4) var(--space-5); text-align: left; border-bottom: 1px solid var(--color-border); }
  th { background: var(--color-surface); color: var(--color-text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: var(--color-surface); }
  .badge { display: inline-block; padding: 4px 10px; border-radius: var(--radius-sm); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge.open { background: var(--color-info-light); color: var(--color-info); }
  .badge.in_progress { background: var(--color-warning-light); color: var(--color-warning); }
  .badge.pending { background: var(--color-primary-light); color: var(--color-primary); }
  .badge.closed { background: var(--color-success-light); color: var(--color-success); }
  .badge.low { background: var(--color-success-light); color: var(--color-success); }
  .badge.medium { background: var(--color-info-light); color: var(--color-info); }
  .badge.high { background: var(--color-warning-light); color: var(--color-warning); }
  .badge.urgent { background: var(--color-error-light); color: var(--color-error); }
  .actions { display: flex; gap: var(--space-2); }
  .actions a { font-size: 12px; color: var(--color-text-muted); text-decoration: none; padding: var(--space-1) var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm); transition: var(--transition); }
  .actions a:hover { background: var(--color-primary-light); border-color: var(--color-primary); color: var(--color-primary); }
  .actions a.delete { color: var(--color-error); border-color: var(--color-error); }
  .actions a.delete:hover { background: var(--color-error-light); color: var(--color-error); }
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">Dashboard</span>
    </div>
    <div class="topbar-links">
      <a href="tickets.php">My Tickets</a>
      <a href="create_ticket.php" class="btn" style="padding: var(--space-2) var(--space-3); font-size: 13px;">New Ticket</a>
      <form action="logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <div class="welcome">
      <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
      <p><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    <div class="stats">
      <div class="stat open"><span class="val"><?php echo $openCount; ?></span><span class="lbl">Open</span></div>
      <div class="stat in_progress"><span class="val"><?php echo $inProgressCount; ?></span><span class="lbl">In Progress</span></div>
      <div class="stat pending"><span class="val"><?php echo $pendingCount; ?></span><span class="lbl">Pending</span></div>
      <div class="stat closed"><span class="val"><?php echo $closedCount; ?></span><span class="lbl">Closed</span></div>
    </div>
    <div class="card">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-4);">
        <h2 style="margin:0;">Recent Tickets</h2>
        <a href="tickets.php" class="btn-secondary" style="font-size:13px;padding:var(--space-1) var(--space-3);">View All</a>
      </div>
      <?php if (empty($recentTickets)): ?>
        <div class="empty">No tickets yet. <a href="create_ticket.php" class="btn" style="font-size:13px;padding:var(--space-1) var(--space-3);">Create your first ticket</a></div>
      <?php else: ?>
        <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentTickets as $t): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
              <td><?php echo ucfirst($t['category']); ?></td>
              <td><span class="badge <?php echo $t['priority']; ?>"><?php echo ucfirst($t['priority']); ?></span></td>
              <td><span class="badge <?php echo $t['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$t['status'])); ?></span></td>
              <td><?php echo date('M j, Y', strtotime($t['created_at'])); ?></td>
              <td class="actions"><a href="ticket_detail.php?id=<?php echo $t['id']; ?>">View</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>