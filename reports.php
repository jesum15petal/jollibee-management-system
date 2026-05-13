<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'Reports';
$activePage = 'reports';

// Stats
$totalEmp  = $conn->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'];
$activeEmp = $conn->query("SELECT COUNT(*) as c FROM employees WHERE status='Active'")->fetch_assoc()['c'];
$fullTime  = $conn->query("SELECT COUNT(*) as c FROM employees WHERE employment_type='Full-Time'")->fetch_assoc()['c'];
$partTime  = $conn->query("SELECT COUNT(*) as c FROM employees WHERE employment_type='Part-Time'")->fetch_assoc()['c'];

// By department
$byDept = $conn->query("SELECT d.name, COUNT(e.id) as cnt FROM departments d LEFT JOIN employees e ON e.department_id=d.id GROUP BY d.id ORDER BY cnt DESC");

// By status
$byStatus = $conn->query("SELECT status, COUNT(*) as cnt FROM employees GROUP BY status");

include 'layout.php';
?>

<div class="page-header"><h1>Reports</h1></div>

<!-- Summary Cards -->
<div class="stats-grid" style="margin-bottom:28px;">
  <div class="stat-card"><div class="stat-icon red">👥</div><div><div class="stat-label">Total</div><div class="stat-value"><?= $totalEmp ?></div></div></div>
  <div class="stat-card"><div class="stat-icon green">✅</div><div><div class="stat-label">Active</div><div class="stat-value"><?= $activeEmp ?></div></div></div>
  <div class="stat-card"><div class="stat-icon yellow">🕐</div><div><div class="stat-label">Full-Time</div><div class="stat-value"><?= $fullTime ?></div></div></div>
  <div class="stat-card"><div class="stat-icon blue">⏱️</div><div><div class="stat-label">Part-Time</div><div class="stat-value"><?= $partTime ?></div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <!-- By Department -->
  <div class="table-card">
    <div class="table-card-header"><h2>Employees by Department</h2></div>
    <table class="data-table">
      <thead><tr><th>Department</th><th>Employees</th><th>Share</th></tr></thead>
      <tbody>
        <?php while ($r = $byDept->fetch_assoc()): $pct = $totalEmp > 0 ? round($r['cnt']/$totalEmp*100) : 0; ?>
        <tr>
          <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
          <td><?= $r['cnt'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="flex:1;height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;height:100%;background:#DA251C;border-radius:4px;"></div>
              </div>
              <span style="font-size:12px;color:#888;width:32px;"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- By Status -->
  <div class="table-card">
    <div class="table-card-header"><h2>Employees by Status</h2></div>
    <table class="data-table">
      <thead><tr><th>Status</th><th>Count</th><th>Share</th></tr></thead>
      <tbody>
        <?php while ($r = $byStatus->fetch_assoc()): $pct = $totalEmp > 0 ? round($r['cnt']/$totalEmp*100) : 0; ?>
        <tr>
          <td><span class="badge <?= $r['status']==='Active'?'badge-active':'badge-inactive' ?>"><?= htmlspecialchars($r['status']) ?></span></td>
          <td><?= $r['cnt'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="flex:1;height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;height:100%;background:<?= $r['status']==='Active'?'#2E7D32':'#E65100' ?>;border-radius:4px;"></div>
              </div>
              <span style="font-size:12px;color:#888;width:32px;"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'layout_footer.php'; ?>