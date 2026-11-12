<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../index.html'); exit; }
if ($_SESSION['user']['role'] !== 'admin') { header('Location: ../dashboard.php?err=admin'); exit; }
require '../db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $targetId = (int)($_POST['target_id'] ?? 0);
    if ($targetId === $_SESSION['user']['id']) {
        $msg = '<div class="msg error">You cannot modify your own account.</div>';
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        header('Location: users.php?deleted=1'); exit;
    } elseif ($_POST['action'] === 'role') {
        $newRole = in_array($_POST['role'] ?? '', ['user','admin']) ? $_POST['role'] : 'user';
        $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
        $stmt->bind_param('si', $newRole, $targetId);
        $stmt->execute();
        $msg = '<div class="msg success">Role updated.</div>';
    }
}

$users = $conn->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Manage Users - Support.ly</title>
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
    max-width: 1000px;
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
  table {
    width: 100%;
    border-collapse: collapse;
    background: var(--color-bg-elevated);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-md);
  }
  th, td {
    padding: var(--space-4) var(--space-5);
    text-align: left;
    border-bottom: 1px solid var(--color-border);
  }
  th {
    background: var(--color-surface);
    color: var(--color-text-muted);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
  }
  tr:last-child td {
    border-bottom: none;
  }
  tr:hover td {
    background: var(--color-surface);
  }
  .role {
    display: inline-block;
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .role.admin {
    background: var(--color-primary-light);
    color: var(--color-primary);
  }
  .role.user {
    background: rgba(179,136,255,0.2);
    color: #b388ff;
  }
  .actions {
    display: flex;
    gap: var(--space-2);
  }
  .actions a {
    font-size: 12px;
    color: var(--color-text-muted);
    text-decoration: none;
    padding: var(--space-1) var(--space-3);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    transition: var(--transition);
  }
  .actions a:hover {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    color: var(--color-primary);
  }
  .actions a.delete {
    color: var(--color-error);
    border-color: var(--color-error);
  }
  .actions a.delete:hover {
    background: var(--color-error-light);
    color: var(--color-error);
  }
  .me {
    color: var(--color-text-muted);
    font-size: 11px;
    font-style: italic;
  }
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">User Management</span>
    </div>
<div class="topbar-links">
      <a href="../dashboard.php">Dashboard</a>
      <a href="../tickets.php">All Tickets</a>
      <form action="../logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <?php echo $msg; ?>
    <div class="card">
      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>You</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo $u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['name']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><span class="role <?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
            <td><?php echo ($u['id'] === $_SESSION['user']['id']) ? '<span class="me">You</span>' : '-'; ?></td>
            <td class="actions">
              <?php if ($u['id'] !== $_SESSION['user']['id']): ?>
                <a href="edit_user.php?id=<?php echo $u['id']; ?>">Edit Role</a>
                <form action="delete_user.php" method="post" style="margin:0;display:inline;" onsubmit="return confirm('Delete this user?');">
                  <input type="hidden" name="id" value="<?php echo $u['id']; ?>" />
                  <button type="submit" style="background:none;border:none;color:var(--color-error);cursor:pointer;padding:0;font:inherit;">Delete</button>
                </form>
              <?php else: ?>
                <span style="color:var(--color-text-muted);font-size:11px;">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>