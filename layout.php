
<?php
// layout.php – shared header & sidebar
// Usage: include 'layout.php'; at the top of every admin page (after requireAdmin())
// Requires $pageTitle and $activePage to be set before including.
$pageTitle  = $pageTitle  ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jollibee EMS – <?= htmlspecialchars($pageTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ===== RESET & BASE ===== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Nunito', sans-serif;
  background: #f4f6fb;
  color: #333;
  display: flex;
  min-height: 100vh;
}

/* ===== SIDEBAR ===== */
.sidebar {
  width: 240px;
  min-height: 100vh;
  background: linear-gradient(180deg, #DA251C 0%, #9B1B13 100%);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 100;
  box-shadow: 4px 0 20px rgba(0,0,0,0.15);
}
.sidebar-brand {
  padding: 28px 24px 20px;
  text-align: center;
  border-bottom: 1px solid rgba(255,255,255,0.12);
}
.sidebar-brand img {
  width: 72px;
  filter: drop-shadow(0 3px 8px rgba(0,0,0,0.25));
}
.sidebar-brand .brand-name {
  color: #fff;
  font-size: 20px;
  font-weight: 900;
  margin-top: 8px;
  letter-spacing: 0.5px;
}
.sidebar-brand .brand-sub {
  color: rgba(255,255,255,0.6);
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
}

.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  overflow-y: auto;
}
.nav-label {
  color: rgba(255,255,255,0.4);
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  padding: 12px 12px 6px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  border-radius: 12px;
  color: rgba(255,255,255,0.75);
  text-decoration: none;
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 2px;
  transition: all 0.2s;
}
.nav-item i { width: 18px; text-align: center; font-size: 15px; }
.nav-item:hover { background: rgba(255,255,255,0.12); color: #fff; }
.nav-item.active {
  background: rgba(255,255,255,0.18);
  color: #fff;
  box-shadow: inset 3px 0 0 #FFB300;
}

.sidebar-user {
  padding: 16px;
  border-top: 1px solid rgba(255,255,255,0.12);
  display: flex;
  align-items: center;
  gap: 10px;
}
.user-avatar {
  width: 40px; height: 40px;
  background: rgba(255,255,255,0.15);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.user-info .user-name { color: #fff; font-size: 13px; font-weight: 800; }
.user-info .user-role { color: rgba(255,255,255,0.55); font-size: 11px; }
.logout-btn {
  margin-left: auto;
  color: rgba(255,255,255,0.5);
  font-size: 16px;
  text-decoration: none;
  transition: color 0.2s;
}
.logout-btn:hover { color: #fff; }

/* ===== MAIN CONTENT ===== */
.main-wrapper {
  margin-left: 240px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* ===== TOPBAR ===== */
.topbar {
  background: #fff;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  border-bottom: 1px solid #eee;
  position: sticky;
  top: 0; z-index: 50;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.topbar-left { display: flex; align-items: center; gap: 10px; }
.hamburger { font-size: 20px; color: #666; cursor: pointer; }
.breadcrumb { font-size: 13px; color: #999; }
.breadcrumb a { color: #999; text-decoration: none; }
.breadcrumb span { color: #333; font-weight: 700; }
.topbar-right { display: flex; align-items: center; gap: 16px; }
.notif-btn {
  position: relative;
  background: #f4f6fb;
  border: none;
  width: 38px; height: 38px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 16px;
  color: #666;
  transition: background 0.2s;
}
.notif-btn:hover { background: #ffe5e5; color: #DA251C; }
.notif-badge {
  position: absolute;
  top: 4px; right: 4px;
  background: #DA251C;
  color: #fff;
  font-size: 9px;
  font-weight: 800;
  width: 16px; height: 16px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

/* ===== PAGE CONTENT ===== */
.page-content { padding: 28px; flex: 1; }

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}
.page-header h1 { font-size: 28px; font-weight: 900; color: #1a1a1a; }

/* ===== BUTTONS ===== */
.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 10px 20px;
  border-radius: 10px;
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  border: none;
  transition: all 0.2s;
}
.btn-primary { background: #DA251C; color: #fff; }
.btn-primary:hover { background: #b71c1c; box-shadow: 0 4px 14px rgba(218,37,28,0.3); transform: translateY(-1px); }
.btn-secondary { background: #f4f6fb; color: #555; border: 1px solid #e0e0e0; }
.btn-secondary:hover { background: #e8eaf6; }
.btn-warning { background: #fff8e1; color: #F57F17; border: 1px solid #FFE082; }
.btn-warning:hover { background: #FFE082; }
.btn-danger { background: #fff5f5; color: #DA251C; border: 1px solid #ffcdd2; }
.btn-danger:hover { background: #ffcdd2; }
.btn-success { background: #e8f5e9; color: #2E7D32; border: 1px solid #A5D6A7; }
.btn-success:hover { background: #A5D6A7; }
.btn-sm { padding: 7px 14px; font-size: 12px; border-radius: 8px; }

/* ===== STAT CARDS ===== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-bottom: 28px;
}
.stat-card {
  background: #fff;
  border-radius: 16px;
  padding: 20px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.09); }
.stat-icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}
.stat-icon.red { background: #fff0f0; }
.stat-icon.yellow { background: #fffde7; }
.stat-icon.blue { background: #e3f2fd; }
.stat-icon.green { background: #e8f5e9; }
.stat-value { font-size: 30px; font-weight: 900; color: #1a1a1a; line-height: 1; }
.stat-label { font-size: 14px; font-weight: 700; color: #666; margin-bottom: 2px; }
.stat-sub { font-size: 12px; color: #aaa; }

/* ===== TABLE CARD ===== */
.table-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  overflow: hidden;
}
.table-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #f0f0f0;
  flex-wrap: wrap;
  gap: 12px;
}
.table-card-header h2 { font-size: 18px; font-weight: 800; color: #1a1a1a; }
.table-actions { display: flex; align-items: center; gap: 10px; }

.search-box {
  position: relative;
}
.search-box input {
  padding: 9px 14px 9px 36px;
  border: 2px solid #eee;
  border-radius: 10px;
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  outline: none;
  width: 220px;
  transition: border-color 0.2s;
}
.search-box input:focus { border-color: #DA251C; }
.search-box i {
  position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
  color: #bbb; font-size: 14px;
}

/* ===== DATA TABLE ===== */
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th {
  padding: 12px 18px;
  text-align: left;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #888;
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
}
.data-table tbody tr {
  border-bottom: 1px solid #f8f8f8;
  transition: background 0.15s;
}
.data-table tbody tr:hover { background: #fafafa; }
.data-table tbody td {
  padding: 14px 18px;
  font-size: 14px;
  color: #444;
  vertical-align: middle;
}
.data-table tbody tr:last-child { border-bottom: none; }

.emp-avatar {
  width: 38px; height: 38px;
  border-radius: 50%;
  object-fit: cover;
  background: #f0f0f0;
  flex-shrink: 0;
}
.emp-name-cell { display: flex; align-items: center; gap: 12px; }
.emp-name { font-weight: 700; color: #222; }

/* ===== BADGES ===== */
.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
}
.badge-active { background: #e8f5e9; color: #2E7D32; }
.badge-inactive { background: #fff8f0; color: #E65100; }
.badge-fulltime { background: #e3f2fd; color: #1565C0; }
.badge-parttime { background: #f3e5f5; color: #6A1B9A; }

/* ===== PAGINATION ===== */
.table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-top: 1px solid #f0f0f0;
  font-size: 13px;
  color: #888;
}
.pagination { display: flex; gap: 6px; }
.page-btn {
  width: 34px; height: 34px;
  border-radius: 8px;
  border: 1px solid #eee;
  background: #fff;
  font-family: 'Nunito', sans-serif;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: #666;
  text-decoration: none;
  transition: all 0.15s;
}
.page-btn:hover { border-color: #DA251C; color: #DA251C; }
.page-btn.active { background: #DA251C; color: #fff; border-color: #DA251C; }
.page-btn:disabled { opacity: 0.4; cursor: default; }

/* ===== ALERTS ===== */
.alert {
  padding: 13px 18px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 14px;
  font-weight: 600;
  display: flex; align-items: center; gap: 10px;
}
.alert-success { background: #e8f5e9; color: #2E7D32; border: 1px solid #A5D6A7; }
.alert-error { background: #fff0f0; color: #c62828; border: 1px solid #ffcdd2; }

/* ===== FORMS ===== */
.form-card {
  background: #fff;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  max-width: 760px;
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-grid .full { grid-column: 1 / -1; }
.form-group-f { margin-bottom: 0; }
.form-group-f label {
  display: block;
  font-size: 12px;
  font-weight: 800;
  color: #555;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}
.form-group-f input,
.form-group-f select,
.form-group-f textarea {
  width: 100%;
  padding: 11px 14px;
  border: 2px solid #eee;
  border-radius: 10px;
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  color: #333;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #fff;
}
.form-group-f input:focus,
.form-group-f select:focus,
.form-group-f textarea:focus {
  border-color: #DA251C;
  box-shadow: 0 0 0 4px rgba(218,37,28,0.07);
}
.form-group-f textarea { resize: vertical; min-height: 90px; }
.form-actions { display: flex; gap: 12px; margin-top: 28px; }

/* ===== MODAL ===== */
.modal-overlay {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 200;
  align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal {
  background: #fff;
  border-radius: 18px;
  padding: 32px;
  width: 440px;
  max-width: 95vw;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.modal h3 { font-size: 20px; font-weight: 900; color: #1a1a1a; margin-bottom: 10px; }
.modal p { color: #666; font-size: 14px; line-height: 1.6; }
.modal-actions { display: flex; gap: 10px; margin-top: 24px; justify-content: flex-end; }

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
  .stats-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
  .sidebar { display: none; }
  .main-wrapper { margin-left: 0; }
  .stats-grid { grid-template-columns: 1fr; }
  .form-grid { grid-template-columns: 1fr; }
  .form-grid .full { grid-column: 1; }
}
</style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/8/84/Jollibee_2011_logo.svg/1200px-Jollibee_2011_logo.svg.png"
         alt="Jollibee" onerror="this.style.display='none'">
    <div class="brand-name">Jollibee</div>
    <div class="brand-sub">Employee Management</div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Main Menu</div>
    <a href="index.php" class="nav-item <?= $activePage==='dashboard'?'active':'' ?>">
      <i class="fas fa-home"></i> Dashboard
    </a>
    <a href="employees.php" class="nav-item <?= $activePage==='employees'?'active':'' ?>">
      <i class="fas fa-users"></i> Employees
    </a>
    <a href="departments.php" class="nav-item <?= $activePage==='departments'?'active':'' ?>">
      <i class="fas fa-building"></i> Departments
    </a>
    <a href="positions.php" class="nav-item <?= $activePage==='positions'?'active':'' ?>">
      <i class="fas fa-briefcase"></i> Positions
    </a>

    <div class="nav-label" style="margin-top:8px;">System</div>
    <a href="reports.php" class="nav-item <?= $activePage==='reports'?'active':'' ?>">
      <i class="fas fa-chart-bar"></i> Reports
    </a>
    <a href="settings.php" class="nav-item <?= $activePage==='settings'?'active':'' ?>">
      <i class="fas fa-cog"></i> Settings
    </a>
  </nav>

  <div class="sidebar-user">
    <div class="user-avatar">👨‍💼</div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
      <div class="user-role"><?= htmlspecialchars($_SESSION['admin_role'] ?? 'Administrator') ?></div>
    </div>
    <a href="logout.php" class="logout-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
  </div>
</aside>

<!-- ===== MAIN ===== -->
<div class="main-wrapper">
  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <div class="hamburger">☰</div>
      <div class="breadcrumb">
        <a href="index.php">Dashboard</a>
        <?php if ($pageTitle !== 'Dashboard'): ?>
          &nbsp;›&nbsp;<span><?= htmlspecialchars($pageTitle) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="topbar-right">
      <button class="notif-btn">
        <i class="fas fa-bell"></i>
        <span class="notif-badge">3</span>
      </button>
    </div>
  </header>

  <!-- Page Content starts here -->
  <div class="page-content">