<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.html'); exit; }
$user = $_SESSION['user'];
require 'db.php';
$res = $conn->query('SELECT COUNT(*) AS c FROM users');
$row = $res->fetch_assoc();
$userCount = $row['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6fb; color: #333; }
  .topbar { background: #667eea; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
  .topbar h1 { font-size: 18px; }
  .topbar button { background: #fff; color: #667eea; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
  .wrap { max-width: 900px; margin: 30px auto; padding: 0 20px; }
  .welcome { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.06); margin-bottom: 20px; }
  .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 16px; }
  .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
  .card h3 { font-size: 13px; color: #888; margin-bottom: 6px; }
  .card .val { font-size: 26px; font-weight: 700; color: #667eea; }
</style>
</head>
<body>
  <div class="topbar">
    <h1>Dashboard</h1>
    <form action="logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
  </div>
  <div class="wrap">
    <div class="welcome">
      <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
      <p style="color:#777;margin-top:4px;"><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    <div class="grid">
      <div class="card"><h3>Users</h3><div class="val"><?php echo (int)$userCount; ?></div></div>
      <div class="card"><h3>Revenue</h3><div class="val">$8,420</div></div>
      <div class="card"><h3>Orders</h3><div class="val">312</div></div>
      <div class="card"><h3>Growth</h3><div class="val">+12%</div></div>
    </div>
  </div>
</body>
</html>
