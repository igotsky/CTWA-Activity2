<?php
require 'db.php';

$email = 'admin@support.ly';
$password = 'admin123';
$name = 'Admin User';
$message = '';
$type = '';

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $message = 'Admin user already exists.';
    $type = 'info';
} else {
    $stmt->close();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?,?,?,"admin")');
    $stmt->bind_param('sss', $name, $email, $hash);
    if ($stmt->execute()) {
        $message = 'Admin user created successfully!';
        $type = 'success';
    } else {
        $message = 'Error: ' . $conn->error;
        $type = 'error';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Setup Admin - Support.ly</title>
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
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-6);
    color: var(--color-text);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  .card {
    background: var(--color-bg-elevated);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    width: 100%;
    max-width: 420px;
    padding: var(--space-8);
  }
  h2 { text-align: center; margin-bottom: var(--space-2); color: var(--color-text); font-size: 26px; font-weight: 700; letter-spacing: -0.5px; }
  p.sub { text-align: center; color: var(--color-text-muted); margin-bottom: var(--space-6); font-size: 14px; line-height: 1.5; }
  .msg { text-align: center; margin-bottom: var(--space-4); font-size: 13px; min-height: 18px; }
  .msg.error { color: var(--color-error); background: var(--color-error-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .msg.success { color: var(--color-success); background: var(--color-success-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .msg.info { color: var(--color-primary); background: var(--color-primary-light); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); }
  .credentials { background: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-4); margin: var(--space-4) 0; font-family: monospace; font-size: 13px; color: var(--color-text); }
  .credentials div { margin-bottom: var(--space-2); }
  .credentials strong { color: var(--color-text-muted); }
  .link { text-align: center; margin-top: var(--space-4); font-size: 13px; }
  .link a { color: var(--color-primary); text-decoration: none; font-weight: 500; }
  .link a:hover { text-decoration: underline; }
  .btn { display: inline-block; width: 100%; padding: var(--space-3) var(--space-4); font-size: 15px; font-weight: 600; background: var(--color-primary); color: #fff; border: none; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); text-decoration: none; text-align: center; }
  .btn:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
</style>
</head>
<body>
  <div class="card">
    <h2>Setup Admin</h2>
    <p class="sub">Create default admin account</p>
    <?php if ($message): ?>
      <div class="msg <?php echo $type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($type === 'success'): ?>
      <div class="credentials">
        <div><strong>Email:</strong> admin@support.ly</div>
        <div><strong>Password:</strong> admin123</div>
        <div><strong>Role:</strong> admin</div>
      </div>
      <div class="link"><a href="index.html">Go to Login</a></div>
    <?php else: ?>
      <div class="link"><a href="index.html">Go to Login</a></div>
    <?php endif; ?>
  </div>
</body>
</html>