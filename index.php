<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

// Stats
$totalEmp    = $conn->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'];
$fullTime    = $conn->query("SELECT COUNT(*) as c FROM employees WHERE employment_type='Full-Time'")->fetch_assoc()['c'];
$partTime    = $conn->query("SELECT COUNT(*) as c FROM employees WHERE employment_type='Part-Time'")->fetch_assoc()['c'];
$activeEmp   = $conn->query("SELECT COUNT(*) as c FROM employees WHERE status='Active'")->fetch_assoc()['c'];

// Recent employees
$recentSQL = "SELECT e.*, p.title AS position, d.name AS department
              FROM employees e
              LEFT JOIN positions p ON e.position_id = p.id
              LEFT JOIN departments d ON e.department_id = d.id
              ORDER BY e.created_at DESC LIMIT 5";
$recentResult = $conn->query($recentSQL);

include 'layout.php';
?>

<!-- Stats -->
<div class="page-header">
  <h1>Dashboard</h1>
  <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Employee</a>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon red">👥</div>
    <div>
      <div class="stat-label">Total Employees</div>
      <div class="stat-value"><?= $totalEmp ?></div>
      <div class="stat-sub">Active employees</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow">🕐</div>
    <div>
      <div class="stat-label">Full-Time</div>
      <div class="stat-value"><?= $fullTime ?></div>
      <div class="stat-sub">Employees</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">⏱️</div>
    <div>
      <div class="stat-label">Part-Time</div>
      <div class="stat-value"><?= $partTime ?></div>
      <div class="stat-sub">Employees</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">✅</div>
    <div>
      <div class="stat-label">Active</div>
      <div class="stat-value"><?= $activeEmp ?></div>
      <div class="stat-sub">Employees</div>
    </div>
  </div>
</div>

<!-- Recent Employees Table -->
<div class="table-card">
  <div class="table-card-header">
    <h2>Recent Employees</h2>
    <a href="employees.php" class="btn btn-secondary btn-sm">View All</a>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Employee ID</th>
        <th>Full Name</th>
        <th>Position</th>
        <th>Department</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; while ($row = $recentResult->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><strong><?= htmlspecialchars($row['employee_id']) ?></strong></td>
        <td>
          <div class="emp-name-cell">
            <?php if ($row['photo'] && file_exists('uploads/'.$row['photo'])): ?>
              <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" class="emp-avatar" alt="">
            <?php else: ?>
              <div class="emp-avatar" style="background:#ffe5e5;display:flex;align-items:center;justify-content:center;font-size:16px;">👤</div>
            <?php endif; ?>
            <span class="emp-name"><?= htmlspecialchars($row['full_name']) ?></span>
          </div>
        </td>
        <td><?= htmlspecialchars($row['position'] ?? '—') ?></td>
        <td><?= htmlspecialchars($row['department'] ?? '—') ?></td>
        <td>
          <span class="badge <?= $row['status']==='Active' ? 'badge-active' : 'badge-inactive' ?>">
            <?= htmlspecialchars($row['status']) ?>
          </span>
        </td>
        <td>
          <div style="display:flex;gap:6px;">
            <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen"></i></a>
            <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['full_name'])) ?>')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
      <?php if ($recentResult->num_rows === 0): ?>
      <tr><td colspan="7" style="text-align:center;color:#aaa;padding:32px;">No employees found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include 'layout_footer.php'; ?>