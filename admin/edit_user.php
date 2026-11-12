<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../index.html'); exit; }
if ($_SESSION['user']['role'] !== 'admin') { header('Location: ../dashboard.php?err=admin'); exit; }
require '../db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === $_SESSION['user']['id']) { header('Location: users.php'); exit; }

$stmt = $conn->prepare('SELECT id, name, email, role FROM users WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { header('Location: users.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRole = in_array($_POST['role'] ?? '', ['user','admin']) ? $_POST['role'] : 'user';
    $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
    $stmt->bind_param('si', $newRole, $id);
    $stmt->execute();
    $msg = '<div class="msg success">Role updated to '.ucfirst($newRole).'.</div>';
    $user['role'] = $newRole;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Edit User - Support.ly</title>
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
    max-width: 600px;
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
  label:first-child {
    margin-top: 0;
  }
  select, input[type="text"] {
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
  select:focus, input[type="text"]:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px var(--color-primary-light);
  }
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
  button:hover {
    background: var(--color-primary-hover);
    transform: translateY(-1px);
  }
  button:active {
    transform: translateY(0);
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
    max-width: 600px;
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
  label:first-child {
    margin-top: 0;
  }
  select, input[type="text"] {
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
  select:focus, input[type="text"]:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px var(--color-primary-light);
  }
  button {
    width: 100%;
    padding: var(--space-3) var(--space-4);
    background: var(--color-primary);
    color: #fff;
    border: none;
    border_radius: var(--radius-md);
    font-size: 15px;
    font_weight: 600;
    cursor: pointer;
    transition: var(--transition);
  }
  button:hover {
    background: var(--color-primary-hover);
    transform: translateY(-1px);
  }
  button:active {
    transform: translateY(0);
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
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">Edit User</span>
    </div>
<div class="topbar-links">
      <a href="../dashboard.php">Dashboard</a>
      <a href="../tickets.php">My Tickets</a>
      <a href="users.php">User Management</a>
      <form action="../logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <?php echo $msg; ?>
    <div class="card">
      <h2>Edit User</h2>
      <form method="post">
        <label>Name</label>
        <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" disabled />
        <label>Email</label>
        <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled />
        <label>Role</label>
        <select name="role">
          <option value="user" <?php echo $user['role']==='user'?'selected':''; ?>>User</option>
          <option value="admin" <?php echo $user['role']==='admin'?'selected':''; ?>>Admin</option>
        </select>
        <button type="submit">Save Role</button>
      </form>
    </div>
  </div>
</body>
</html>