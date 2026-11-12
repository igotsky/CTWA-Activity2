<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.html'); exit; }
$user = $_SESSION['user'];

// Redirect admins to admin tickets
if ($user['role'] === 'admin') { header('Location: admin/tickets.php'); exit; }

$user = $_SESSION['user'];
require 'db.php';
$uid = $user['id'];

$statusFilter = $_GET['status'] ?? '';
$where = "user_id=$uid";
if ($statusFilter) { $where .= " AND status='" . $conn->real_escape_string($statusFilter) . "'"; }

$tickets = $conn->query("SELECT * FROM tickets WHERE $where ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>My Tickets - Support.ly</title>
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
  .btn:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }
  .btn-secondary { background: transparent; color: var(--color-text); border: 1px solid var(--color-border); }
  .btn-secondary:hover { background: var(--color-primary-light); border-color: var(--color-primary); color: var(--color-primary); }
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
  .topbar button:hover { background: var(--color-error_light); border-color: var(--color_error); color: var(--color_error); }
  .wrap { max-width: 1000px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow-md); }
  .card h2 { font-size: 18px; font-weight: 700; color: var(--color-text); margin-bottom: var(--space-4); }
  .btn { display: inline-block; padding: var(--space-3) var(--space-4); font-size: 14px; font-weight: 600; background: var(--color-primary); color: #fff; border: none; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); text-decoration: none; }
  .btn:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }
  .btn-secondary { background: transparent; color: var(--color-text); border: 1px solid var(--color-border); }
  .btn-secondary:hover { background: var(--color-primary_light); border-color: var(--color_primary); color: var(--color_primary); }
  .btn-danger { background: var(--color_error); }
  .btn-danger:hover { background: #c62828; }
  .msg { margin-bottom: var(--space-4); font-size: 13px; }
  .msg.error { color: var(--color_error); background: var(--color_error_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .msg.success { color: var(--color_success); background: var(--color_success_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .sidebar { position: fixed; inset: 0 auto 0 0; width: 210px; background: linear_gradent(135deg, #2c3e50, #1a252f); color: #e8eaf0; display: flex; flex_direction: column; height: 100vh; overflow_y: auto; z_index: 50; border_right: 1px solid rgba(255,255,255,0.03); }
  .sidebar-brand { padding: 22px 20px 32px; font_size: 19px; font_weight: 800; color: #fff; background: linear_gradent(135deg, #667eea, #764ba2); -webkit-background_clip: text; -webkit_text_fill_color: transparent; border_bottom: 1px solid rgba(255,255,255,0.08); margin_bottom: 0; letter_spacing: -0.5px; }
  .sidebar-nav { display: flex; flex_direction: column; padding: 8px 4px; }
  .sidebar_link { padding: 11px 18px; color: #a9b4c3; text_decoration: none; font_size: 13px; font_weight: 500; display: flex; align_items: center; gap: 8px; border_radius: 10px; margin: 2px 8px; transition: .18s; white_space: nowrap; }
  .sidebar_link:hover { background: rgba(255,255,255,0.06); color: #fff; }
  .sidebar_link.active { background: #667eea; color: #fff; box_shadow: 0 4px 12px rgba(102,126,234,0.35); }
  .sidebar_divider { height: 1px; background: rgba(255,255,255,0.06); margin: 16px 12px 8px; }
  .sidebar_logout { margin_top: 24px; color: #e27474; }
  .sidebar_logout:hover { background: rgba(229,57,53,0.1); color: #ff6b6b; }
  .topbar { background: var(--color-bg-elevated); border-bottom: 1px solid var(--color-border); padding: var(--space-4) var(--space-6); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 40; }
  .topbar-brand { display: flex; align-items: center; gap: var(--space-4); }
  .topbar-brand .brand-name { font-size: 20px; font-weight: 700; color: var(--color-text); letter-spacing: -0.5px; }
  .topbar-brand .brand-divider { width: 1px; height: 24px; background: var(--color-border); }
  .topbar-brand .page-title { font-size: 16px; font-weight: 600; color: var(--color-text-muted); }
  .topbar-links { display: flex; align-items: center; gap: var(--space-3); }
  .topbar a { background: transparent; color: var(--color-text-muted); border: 1px solid var(--color-border); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); text-decoration: none; font-size: 13px; font-weight: 500; transition: var(--transition); }
  .topbar a:hover { background: var(--color-primary_light); border-color: var(--color_primary); color: var(--color_primary); }
  .topbar button { background: transparent; color: var(--color-text-muted); border: 1px solid var(--color-border); padding: var(--space-2) var(--space-4); border_radius: var(--radius-md); cursor: pointer; font_size: 13px; font_weight: 500; transition: var(--transition); }
  .topbar button:hover { background: var(--color-error_light); border-color: var(--color_error); color: var(--color_error); }
  .wrap { max-width: 1000px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border_radius: var(--radius-xl); padding: var(--space-6); box_shadow: var(--shadow_md); }
  .card h2 { font_size: 18px; font_weight: 700; color: var(--color_text); margin_bottom: var(--space-4); }
  .btn { display: inline_block; padding: var(--space-3) var(--space-4); font_size: 14px; font_weight: 600; background: var(--color_primary); color: #fff; border: none; border_radius: var(--radius-md); cursor: pointer; transition: var(--transition); text_decoration: none; }
  .btn:hover { background: var(--color_primary_hover); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }
  .btn-secondary { background: transparent; color: var(--color_text); border: 1px solid var(--color_border); }
  .btn-secondary:hover { background: var(--color_primary_light); border_color: var(--color_primary); color: var(--color_primary); }
  .btn-danger { background: var(--color_error); }
  .btn-danger:hover { background: #c62828; }
  .msg { margin_bottom: var(--space-4); font_size: 13px; }
  .msg.error { color: var(--color_error); background: var(--color_error_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .msg.success { color: var(--color_success); background: var(--color_success_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .sidebar { position: fixed; inset: 0 auto 0 0; width: 210px; background: linear_gradent(135deg, #2c3e50, #1a252f); color: #e8eaf0; display: flex; flex_direction: column; height: 100vh; overflow_y: auto; z_index: 50; border_right: 1px solid rgba(255,255,255,0.03); }
  .sidebar-brand { padding: 22px 20px 32px; font_size: 19px; font_weight: 800; color: #fff; background: linear_gradent(135deg, #667eea, #764ba2); -webkit-background_clip: text; -webkit-text_fill_color: transparent; border_bottom: 1px solid rgba(255,255,255,0.08); margin_bottom: 0; letter_spacing: -0.5px; }
  .sidebar-nav { display: flex; flex_direction: column; padding: 8px 4px; }
  .sidebar_link { padding: 11px 18px; color: #a9b4c3; text_decoration: none; font_size: 13px; font_weight: 500; display: flex; align_items: center; gap: 8px; border_radius: 10px; margin: 2px 8px; transition: .18s; white_space: nowrap; }
  .sidebar_link:hover { background: rgba(255,255,255,0.06); color: #fff; }
  .sidebar_link.active { background: #667eea; color: #fff; box_shadow: 0 4px 12px rgba(102,126,234,0.35); }
  .sidebar_divider { height: 1px; background: rgba(255,255,255,0.06); margin: 16px 12px 8px; }
  .sidebar_logout { margin_top: 24px; color: #e27474; }
  .sidebar_logout:hover { background: rgba(229,57,53,0.1); color: #ff6b6b; }
  .topbar { background: var(--color-bg-elevated); border-bottom: 1px solid var(--color-border); padding: var(--space-4) var(--space-6); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 40; }
  .topbar-brand { display: flex; align-items: center; gap: var(--space-4); }
  .topbar-brand .brand-name { font-size: 20px; font-weight: 700; color: var(--color-text); letter-spacing: -0.5px; }
  .topbar-brand .brand-divider { width: 1px; height: 24px; background: var(--color-border); }
  .topbar-brand .page-title { font-size: 16px; font-weight: 600; color: var(--color-text-muted); }
  .topbar-links { display: flex; align-items: center; gap: var(--space-3); }
  .topbar a { background: transparent; color: var(--color-text-muted); border: 1px solid var(--color-border); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); text-decoration: none; font-size: 13px; font-weight: 500; transition: var(--transition); }
  .topbar a:hover { background: var(--color-primary_light); border-color: var(--color_primary); color: var(--color_primary); }
  .topbar button { background: transparent; color: var(--color-text-muted); border: 1px solid var(--color-border); padding: var(--space-2) var(--space-4); border_radius: var(--radius-md); cursor: pointer; font-size: 13px; font-weight: 500; transition: var(--transition); }
  .topbar button:hover { background: var(--color-error_light); border-color: var(--color_error); color: var(--color_error); }
  .wrap { max-width: 1000px; margin: var(--space-8) auto; padding: 0 var(--space-6); }
  .card { background: var(--color-bg-elevated); border: 1px solid var(--color-border); border_radius: var(--radius-xl); padding: var(--space-6); box_shadow: var(--shadow_md); }
  .card h2 { font_size: 18px; font_weight: 700; color: var(--color_text); margin_bottom: var(--space-4); }
  .btn { display: inline_block; padding: var(--space-3) var(--space-4); font_size: 14px; font_weight: 600; background: var(--color_primary); color: #fff; border: none; border_radius: var(--radius-md); cursor: pointer; transition: var(--transition); text_decoration: none; }
  .btn:hover { background: var(--color_primary_hover); transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }
  .btn-secondary { background: transparent; color: var(--color_text); border: 1px solid var(--color_border); }
  .btn-secondary:hover { background: var(--color_primary_light); border-color: var(--color_primary); color: var(--color_primary); }
  .btn-danger { background: var(--color_error); }
  .btn-danger:hover { background: #c62828; }
  .msg { margin_bottom: var(--space-4); font_size: 13px; }
  .msg.error { color: var(--color_error); background: var(--color_error_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .msg.success { color: var(--color_success); background: var(--color_success_light); padding: var(--space-3) var(--space-4); border_radius: var(--radius-md); }
  .sidebar { position: fixed; inset: 0 auto 0 0; width: 210px; background: linear_gradent(135deg, #2c3e50, #1a252f); color: #e8eaf0; display: flex; flex_direction: column; height: 100vh; overflow_y: auto; z_index: 50; border_right: 1px solid rgba(255,255,255,0.03); }
  .sidebar-brand { padding: 22px 20px 32px; font_size: 19px; font_weight: 800; color: #fff; background: linear_gradent(135deg, #667eea, #764ba2); -webkit-background_clip: text; -webkit-text_fill_color: transparent; border_bottom: 1px solid rgba(255,255,255,0.08); margin_bottom: 0; letter_spacing: -0.5px; }
  .sidebar-nav { display: flex; flex_direction: column; padding: 8px 4px; }
  .sidebar_link { padding: 11px 18px; color: #a9b4c3; text_decoration: none; font_size: 13px; font_weight: 500; display: flex; align_items: center; gap: 8px; border_radius: 10px; margin: 2px 8px; transition: .18s; white_space: nowrap; }
  .sidebar_link:hover { background: rgba(255,255,255,0.06); color: #fff; }
  .sidebar_link.active { background: #667eea; color: #fff; box_shadow: 0 4px 12px rgba(102,126,234,0.35); }
  .sidebar_divider { height: 1px; background: rgba(255,255,255,0.06); margin: 16px 12px 8px; }
  .sidebar_logout { margin_top: 24px; color: #e27474; }
  .sidebar_logout:hover { background: rgba(229,57,53,0.1); color: #ff6b6b; }
  .table-responsive { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; background: var(--color-bg-elevated); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-md); table-layout: fixed; }
  th, td { padding: var(--space-4) var(--space-5); text-align: left; border-bottom: 1px solid var(--color-border); }
  th { background: var(--color-surface); color: var(--color-text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  /* Column widths */
  th:nth-child(1), td:nth-child(1) { width: 280px; }  /* Title */
  th:nth-child(2), td:nth-child(2) { width: 120px; }  /* Category */
  th:nth-child(3), td:nth-child(3) { width: 100px; }  /* Priority */
  th:nth-child(4), td:nth-child(4) { width: 120px; }  /* Status */
  th:nth-child(5), td:nth-child(5) { width: 130px; }  /* Created */
  th:nth-child(6), td:nth-child(6) { width: 130px; }  /* Updated */
  th:nth-child(7), td:nth-child(7) { width: 80px; }   /* Actions */
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: var(--color-surface); }
  .badge { display: inline-block; padding: 4px 10px; border-radius: var(--radius-sm); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge.open { background: var(--color-info-light); color: var(--color-info); }
  .badge.in_progress { background: var(--color-warning-light); color: var(--color-warning); }
  .badge.pending { background: var(--color-primary-light); color: var(--color-primary); }
  .badge.closed { background: var(--color-success-light); color: var(--color-success); }
  .badge.low { background: var(--color-success_light); color: var(--color-success); }
  .badge.medium { background: var(--color-info_light); color: var(--color-info); }
  .badge.high { background: var(--color-warning_light); color: var(--color-warning); }
  .badge.urgent { background: var(--color-error_light); color: var(--color-error); }
  .actions { display: flex; gap: var(--space-2); }
  .actions a { font-size: 12px; color: var(--color-text-muted); text-decoration: none; padding: var(--space-1) var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm); transition: var(--transition); }
  .actions a:hover { background: var(--color-primary_light); border-color: var(--color_primary); color: var(--color_primary); }
  .actions a.delete { color: var(--color_error); border-color: var(--color_error); }
  .actions a.delete:hover { background: var(--color_error_light); color: var(--color_error); }
  .filter-tabs { display: flex; gap: var(--space-2); margin-bottom: var(--space-4); flex-wrap: wrap; }
  .filter-tabs a { padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-size: 13px; font-weight: 500; text-decoration: none; color: var(--color-text-muted); background: var(--color-bg); border: 1px solid var(--color-border); transition: var(--transition); }
  .filter-tabs a:hover { background: var(--color-primary_light); color: var(--color_primary); border-color: var(--color_primary); }
  .filter-tabs a.active { background: var(--color_primary); color: #fff; border-color: var(--color_primary); }
  .empty { text-align: center; padding: var(--space-8); color: var(--color-text-muted); }
  .table-responsive { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; background: var(--color-bg-elevated); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-md); table-layout: fixed; }
  th, td { padding: var(--space-4) var(--space-5); text-align: left; border-bottom: 1px solid var(--color-border); }
  th { background: var(--color-surface); color: var(--color-text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  /* Column widths */
  th:nth-child(1), td:nth-child(1) { width: 280px; }  /* Title */
  th:nth-child(2), td:nth-child(2) { width: 120px; }  /* Category */
  th:nth-child(3), td:nth-child(3) { width: 100px; }  /* Priority */
  th:nth-child(4), td:nth-child(4) { width: 120px; }  /* Status */
  th:nth-child(5), td:nth-child(5) { width: 130px; }  /* Created */
  th:nth-child(6), td:nth-child(6) { width: 130px; }  /* Updated */
  th:nth-child(7), td:nth-child(7) { width: 80px; }   /* Actions */
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: var(--color-surface); }
  .badge { display: inline-block; padding: 4px 10px; border-radius: var(--radius-sm); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge.open { background: var(--color-info-light); color: var(--color-info); }
  .badge.in_progress { background: var(--color-warning_light); color: var(--color-warning); }
  .badge.pending { background: var(--color-primary_light); color: var(--color-primary); }
  .badge.closed { background: var(--color-success_light); color: var(--color-success); }
  .badge.low { background: var(--color-success_light); color: var(--color-success); }
  .badge.medium { background: var(--color-info_light); color: var(--color-info); }
  .badge.high { background: var(--color-warning_light); color: var(--color-warning); }
  .badge.urgent { background: var(--color-error_light); color: var(--color-error); }
  .actions { display: flex; gap: var(--space-2); }
  .actions a { font-size: 12px; color: var(--color-text-muted); text-decoration: none; padding: var(--space-1) var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm); transition: var(--transition); }
  .actions a:hover { background: var(--color-primary_light); border-color: var(--color_primary); color: var(--color_primary); }
  .actions a.delete { color: var(--color_error); border-color: var(--color_error); }
  .actions a.delete:hover { background: var(--color_error_light); color: var(--color_error); }
  .filter-tabs { display: flex; gap: var(--space-2); margin-bottom: var(--space-4); flex-wrap: wrap; }
  .filter-tabs a { padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-size: 13px; font-weight: 500; text-decoration: none; color: var(--color-text-muted); background: var(--color-bg); border: 1px solid var(--color-border); transition: var(--transition); }
  .filter-tabs a:hover { background: var(--color-primary_light); color: var(--color_primary); border-color: var(--color_primary); }
  .filter-tabs a.active { background: var(--color_primary); color: #fff; border-color: var(--color_primary); }
  .empty { text-align: center; padding: var(--space-8); color: var(--color-text-muted); }
  .table-responsive { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; background: var(--color-bg-elevated); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-md); table-layout: fixed; }
  th, td { padding: var(--space-4) var(--space-5); text-align: left; border-bottom: 1px solid var(--color-border); }
  th { background: var(--color-surface); color: var(--color-text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  /* Column widths */
  th:nth-child(1), td:nth-child(1) { width: 280px; }  /* Title */
  th:nth-child(2), td:nth-child(2) { width: 120px; }  /* Category */
  th:nth-child(3), td:nth-child(3) { width: 100px; }  /* Priority */
  th:nth-child(4), td:nth-child(4) { width: 120px; }  /* Status */
  th:nth-child(5), td:nth-child(5) { width: 130px; }  /* Created */
  th:nth-child(6), td:nth-child(6) { width: 130px; }  /* Updated */
  th:nth-child(6), td:nth-child(6) { width: 80px; }   /* Actions */
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: var(--color-surface); }
</style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-name">Support.ly</span>
      <span class="brand-divider"></span>
      <span class="page-title">My Tickets</span>
    </div>
    <div class="topbar-links">
      <a href="dashboard.php">Dashboard</a>
      <a href="create_ticket.php" class="btn" style="padding: var(--space-2) var(--space-3); font-size: 13px;">New Ticket</a>
      <form action="logout.php" method="post" style="margin:0;"><button type="submit">Logout</button></form>
    </div>
  </header>
  <div class="wrap">
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-4);flex-wrap:wrap;gap:var(--space-3);">
        <h2>My Tickets</h2>
        <a href="create_ticket.php" class="btn">New Ticket</a>
      </div>
      <div class="filter-tabs">
        <a href="tickets.php" class="<?php echo $statusFilter === '' ? 'active' : ''; ?>">All</a>
        <a href="tickets.php?status=open" class="<?php echo $statusFilter === 'open' ? 'active' : ''; ?>">Open</a>
        <a href="tickets.php?status=in_progress" class="<?php echo $statusFilter === 'in_progress' ? 'active' : ''; ?>">In Progress</a>
        <a href="tickets.php?status=pending" class="<?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
        <a href="tickets.php?status=closed" class="<?php echo $statusFilter === 'closed' ? 'active' : ''; ?>">Closed</a>
      </div>
      <?php if (empty($tickets)): ?>
        <div class="empty">No tickets found. <a href="create_ticket.php" class="btn" style="font-size:13px;padding:var(--space-1) var(--space-3);">Create your first ticket</a></div>
      <?php else: ?>
        <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th><th>Updated</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tickets as $t): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
              <td><?php echo ucfirst($t['category']); ?></td>
              <td><span class="badge <?php echo $t['priority']; ?>"><?php echo ucfirst($t['priority']); ?></span></td>
              <td><span class="badge <?php echo $t['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$t['status'])); ?></span></td>
              <td><?php echo date('M j, Y', strtotime($t['created_at'])); ?></td>
              <td><?php echo date('M j, Y', strtotime($t['updated_at'])); ?></td>
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