<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../index.html'); exit; }
if ($_SESSION['user']['role'] !== 'admin') { header('Location: ../dashboard.php?err=admin'); exit; }
require '../db.php';

$statusFilter = $_GET['status'] ?? '';
$where = '1=1';
if ($statusFilter) { $where .= " AND t.status='" . $conn->real_escape_string($statusFilter) . "'"; }

$tickets = $conn->query("SELECT t.*, u.name as user_name, u.email as user_email, a.name as assigned_name FROM tickets t LEFT JOIN users u ON t.user_id=u.id LEFT JOIN users a ON t.assigned_to=a.id WHERE $where ORDER BY t.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>All Tickets - Support.ly Admin</title>
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
  body { font-family: var(--font-sans); background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); min-height: 100vh; color: var(--color-text); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
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
  .wrap { max-width: 1400px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
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
  .table-responsive { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; background: var(--color-bg-elevated); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-md); table-layout: fixed; }
  th, td { padding: var(--space-4) var(--space-5); text-align: left; border-bottom: 1px solid var(--color-border); }
  th { background: var(--color-surface); color: var(--color-text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  /* Column widths */
  th:nth-child(1), td:nth-child(1) { width: 60px; }   /* ID */
  th:nth-child(2), td:nth-child(2) { width: 280px; }  /* Title */
  th:nth-child(3), td:nth-child(3) { width: 180px; }  /* User */
  th:nth-child(4), td:nth-child(4) { width: 120px; }  /* Category */
  th:nth-child(5), td:nth-child(5) { width: 100px; }  /* Priority */
  th:nth-child(6), td:nth-child(6) { width: 120px; }  /* Status */
  th:nth-child(7), td:nth-child(7) { width: 150px; }  /* Assigned */
  th:nth-child(8), td:nth-child(8) { width: 130px; }  /* Created */
  th:nth-child(9), td:nth-child(9) { width: 80px; }   /* Actions */
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
  .filter-tabs { display: flex; gap: var(--space-2); margin-bottom: var(--space-4); flex-wrap: wrap; }
  .filter-tabs a { padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-size: 13px; font-weight: 500; text-decoration: none; color: var(--color-text-muted); background: var(--color-bg); border: 1px solid var(--color-border); transition: var(--transition); }
  .filter-tabs a:hover { background: var(--color-primary-light); color: var(--color-primary); border-color: var(--color-primary); }
  .filter-tabs a.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
  .empty { text-align: center; padding: var(--space-8); color: var(--color-text-muted); }
  .table-responsive { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; background: var(--color-bg-elevated); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-md); table-layout: fixed; }
  th, td { padding: var(--space-4) var(--space-5); text-align: left; border-bottom: 1px solid var(--color-border); }
  th { background: var(--color-surface); color: var(--color-text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  /* Column widths */
  th:nth-child(1), td:nth-child(1) { width: 60px; }   /* ID */
  th:nth-child(2), td:nth-child(2) { width: 280px; }  /* Title */
  th:nth-child(3), td:nth-child(3) { width: 180px; }  /* User */
  th:nth-child(4), td:nth-child(4) { width: 120px; }  /* Category */
  th:nth-child(5), td:nth-child(5) { width: 100px; }  /* Priority */
  th:nth-child(6), td:nth-child(6) { width: 120px; }  /* Status */
  th:nth-child(7), td:nth-child(7) { width: 150px; }  /* Assigned */
  th:nth-child(8), td:nth-child(8) { width: 130px; }  /* Created */
  th:nth-child(9), td:nth-child(9) { width: 80px; }   /* Actions */
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
  .filter-tabs { display: flex; gap: var(--space-2); margin-bottom: var(--space-4); flex-wrap: wrap; }
  .filter-tabs a { padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-size: 13px; font-weight: 500; text-decoration: none; color: var(--color-text-muted); background: var(--color-bg); border: 1px solid var(--color-border); transition: var(--transition); }
  .filter-tabs a:hover { background: var(--color-primary_light); color: var(--color_primary); border-color: var(--color_primary); }
  .filter-tabs a.active { background: var(--color_primary); color: #fff; border-color: var(--color_primary); }
  .empty { text-align: center; padding: var(--space-8); color: var(--color-text-muted); }
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">All Tickets</span>
    </div>
    <div class="topbar-links">
      <a href="../dashboard.php">Dashboard</a>
      <a href="users.php">User Management</a>
      <form action="../logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-4);flex-wrap:wrap;gap:var(--space-3);">
        <h2>All Tickets</h2>
      </div>
      <div class="filter-tabs">
        <a href="tickets.php" class="<?php echo $statusFilter === '' ? 'active' : ''; ?>">All</a>
        <a href="tickets.php?status=open" class="<?php echo $statusFilter === 'open' ? 'active' : ''; ?>">Open</a>
        <a href="tickets.php?status=in_progress" class="<?php echo $statusFilter === 'in_progress' ? 'active' : ''; ?>">In Progress</a>
        <a href="tickets.php?status=pending" class="<?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
        <a href="tickets.php?status=closed" class="<?php echo $statusFilter === 'closed' ? 'active' : ''; ?>">Closed</a>
      </div>
      <?php if (empty($tickets)): ?>
        <div class="empty">No tickets found.</div>
      <?php else: ?>
        <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Title</th><th>User</th><th>Category</th><th>Priority</th><th>Status</th><th>Assigned</th><th>Created</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tickets as $t): ?>
            <tr>
              <td>#<?php echo $t['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
              <td><?php echo htmlspecialchars($t['user_name']); ?><br><small style="color:var(--color-text-muted);"><?php echo htmlspecialchars($t['user_email']); ?></small></td>
              <td><?php echo ucfirst($t['category']); ?></td>
              <td><span class="badge <?php echo $t['priority']; ?>"><?php echo ucfirst($t['priority']); ?></span></td>
              <td><span class="badge <?php echo $t['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$t['status'])); ?></span></td>
              <td><?php echo $t['assigned_name'] ? htmlspecialchars($t['assigned_name']) : '<span style="color:var(--color-text-muted);">Unassigned</span>'; ?></td>
              <td><?php echo date('M j, Y', strtotime($t['created_at'])); ?></td>
              <td class="actions" style="white-space: nowrap;"><a href="ticket_detail.php?id=<?php echo $t['id']; ?>">View</a></td>
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