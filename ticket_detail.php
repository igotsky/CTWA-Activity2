<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.html'); exit; }
$user = $_SESSION['user'];

// Redirect admins to admin ticket detail
if ($user['role'] === 'admin') { header('Location: admin/ticket_detail.php?id=' . (int)($_GET['id'] ?? 0)); exit; }

require 'db.php';
$user_id = $_SESSION['user']['id'];
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT * FROM tickets WHERE id=? AND user_id=?');
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$ticket) { header('Location: tickets.php'); exit; }

// Fetch comments
$comments = $conn->query("SELECT c.*, u.name FROM comments c JOIN users u ON c.user_id=u.id WHERE c.ticket_id=$id AND c.is_internal=FALSE ORDER BY c.created_at ASC")->fetch_all(MYSQLI_ASSOC);

// Check if already rated
$rated = false;
$rating = null;
$rateCheck = $conn->query("SELECT rating, feedback FROM ratings WHERE ticket_id=$id AND user_id=$user_id");
if ($rateCheck->num_rows > 0) {
    $rated = true;
    $rating = $rateCheck->fetch_assoc();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $content = trim($_POST['comment'] ?? '');
    if ($content) {
        $stmt = $conn->prepare('INSERT INTO comments (ticket_id, user_id, content) VALUES (?,?,?)');
        $stmt->bind_param('iis', $id, $user_id, $content);
        $stmt->execute();
        header('Location: ticket_detail.php?id='.$id); exit;
    }
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $ratingVal = (int)($_POST['rating'] ?? 0);
    $feedback = trim($_POST['feedback'] ?? '');
    if ($ratingVal >= 1 && $ratingVal <= 5) {
        $stmt = $conn->prepare('INSERT INTO ratings (ticket_id, user_id, rating, feedback) VALUES (?,?,?,?)');
        $stmt->bind_param('iiis', $id, $user_id, $ratingVal, $feedback);
        $stmt->execute();
        header('Location: ticket_detail.php?id='.$id); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Ticket #<?php echo $ticket['id']; ?> - Support.ly</title>
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
  .wrap { max-width: 900px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .card {
    background: var(--color-bg-elevated);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    box-shadow: var(--shadow-md);
  }
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
  .topbar button:hover { background: var(--color-error-light); border-color: var(--color-error); color: var(--color-error); }
  .wrap { max-width: 900px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
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
  .wrap { max-width: 900px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow-md); }
  .card h2 { font-size: 18px; font-weight: 700; color: var(--color-text); margin-bottom: var(--space-4); }
  .btn { display: inline-block; padding: var(--space-3) var(--space-4); font-size: 14px; font-weight: 600; background: var(--color-primary); color: #fff; border: none; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); text-decoration: none; }
  .btn:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }
  .btn-secondary { background: transparent; color: var(--color-text); border: 1px solid var(--color-border); }
  .btn-secondary:hover { background: var(--color-primary_light); border-color: var(--color-primary); color: var(--color-primary); }
  .btn-danger { background: var(--color_error); }
  .btn-danger:hover { background: #c62828; }
  .msg { margin-bottom: var(--space-4); font-size: 13px; }
  .msg.error { color: var(--color_error); background: var(--color_error_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .msg.success { color: var(--color_success); background: var(--color_success_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .badge { display: inline-block; padding: 4px 10px; border-radius: var(--radius-sm); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge.open { background: var(--color-info-light); color: var(--color-info); }
  .badge.in_progress { background: var(--color-warning-light); color: var(--color-warning); }
  .badge.pending { background: var(--color-primary-light); color: var(--color-primary); }
  .badge.closed { background: var(--color-success-light); color: var(--color-success); }
  .badge.low { background: var(--color-success-light); color: var(--color-success); }
  .badge.medium { background: var(--color-info-light); color: var(--color-info); }
  .badge.high { background: var(--color-warning-light); color: var(--color-warning); }
  .badge.urgent { background: var(--color-error-light); color: var(--color-error); }
  .comment { background: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-4); margin-bottom: var(--space-4); }
  .comment-header { display: flex; justify-content: space-between; margin-bottom: var(--space-2); font-size: 12px; color: var(--color-text-muted); }
  .comment-content { color: var(--color-text); line-height: 1.6; }
  .rating-stars { display: flex; gap: 4px; margin-top: var(--space-2); }
  .rating-stars label { cursor: pointer; font-size: 24px; color: var(--color-text-muted); transition: var(--transition); }
  .rating-stars label:hover, .rating-stars label.active { color: #ffc107; }
  .rating-stars input { display: none; }
  .rating-stars input:checked ~ label { color: #ffc107; }
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">Ticket #<?php echo $ticket['id']; ?></span>
    </div>
    <div class="topbar-links">
      <a href="tickets.php">My Tickets</a>
      <a href="dashboard.php">Dashboard</a>
      <form action="logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:var(--space-4);flex-wrap:wrap;gap:var(--space-3);">
        <div>
          <h2><?php echo htmlspecialchars($ticket['title']); ?></h2>
          <div style="display:flex;gap:var(--space-3);margin-top:var(--space-2);flex-wrap:wrap;">
            <span class="badge <?php echo $ticket['category']; ?>"><?php echo ucfirst($ticket['category']); ?></span>
            <span class="badge <?php echo $ticket['priority']; ?>"><?php echo ucfirst($ticket['priority']); ?></span>
            <span class="badge <?php echo $ticket['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$ticket['status'])); ?></span>
          </div>
        </div>
        <a href="tickets.php" class="btn-secondary">Back to Tickets</a>
      </div>
      <div style="padding-bottom:var(--space-4);border-bottom:1px solid var(--color-border);margin-bottom:var(--space-4);">
        <div style="font-size:13px;color:var(--color-text-muted);margin-bottom:var(--space-2);">Created by <?php echo htmlspecialchars($ticket['created_at']); ?></div>
        <p style="white-space:pre-wrap;line-height:1.7;"><?php echo htmlspecialchars($ticket['description']); ?></p>
      </div>
    </div>

    <div class="card" style="margin-top:var(--space-6);">
      <h2>Comments</h2>
      <?php if (empty($comments)): ?>
        <div class="empty">No comments yet.</div>
      <?php else: ?>
        <?php foreach ($comments as $c): ?>
        <div class="comment">
          <div class="comment-header">
            <strong><?php echo htmlspecialchars($c['name']); ?></strong>
            <span><?php echo date('M j, Y H:i', strtotime($c['created_at'])); ?></span>
          </div>
          <div class="comment-content"><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
      <form method="post" style="margin-top:var(--space-6);">
        <label>Add Comment</label>
        <textarea name="comment" rows="3" placeholder="Add a comment..." required></textarea>
        <button type="submit" style="margin-top:var(--space-3);">Post Comment</button>
      </form>
    </div>

    <?php if ($ticket['status'] === 'closed' && !$rated): ?>
    <div class="card" style="margin-top:var(--space-6);">
      <h2>Rate Your Experience</h2>
      <form method="post">
        <label>Rating (1-5 stars)</label>
        <div class="rating-stars">
          <?php for ($r=5; $r>=1; $r--): ?>
            <input type="radio" name="rating" value="<?php echo $r; ?>" id="star<?php echo $r; ?>" required />
            <label for="star<?php echo $r; ?>" class="<?php echo $r===5?'active':''; ?>">★</label>
          <?php endfor; ?>
        </div>
        <label style="margin-top:var(--space-4);">Feedback (optional)</label>
        <textarea name="feedback" rows="3" placeholder="Share your thoughts..."></textarea>
        <button type="submit" name="rating" value="1" style="margin-top:var(--space-3);">Submit Rating</button>
      </form>
    </div>
    <?php elseif ($rated): ?>
    <div class="card" style="margin-top:var(--space-6);">
      <h2>Your Rating</h2>
      <div class="rating-stars" style="font-size:28px;justify-content:flex-start;">
        <?php for ($r=5; $r>=1; $r--): ?>
          <span style="color:<?php echo $r <= $rating['rating'] ? '#ffc107' : 'var(--color-text-muted)'; ?>">★</span>
        <?php endfor; ?>
      </div>
      <?php if (!empty($rating['feedback'])): ?>
        <p style="margin-top:var(--space-3);color:var(--color-text-muted);"><?php echo htmlspecialchars($rating['feedback']); ?></p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</body>
</html>