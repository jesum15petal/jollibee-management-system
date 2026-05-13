<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'Employees';
$activePage = 'employees';

// Pagination
$perPage    = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $perPage;

// Search & filters
$search   = trim($_GET['search'] ?? '');
$deptId   = (int)($_GET['dept'] ?? 0);
$statusF  = trim($_GET['status'] ?? '');
$typeF    = trim($_GET['type'] ?? '');

$where = "WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $where .= " AND (e.full_name LIKE ? OR e.employee_id LIKE ? OR e.email LIKE ?)";
    $s = "%$search%";
    $params[] = $s; $params[] = $s; $params[] = $s;
    $types .= 'sss';
}
if ($deptId) {
    $where .= " AND e.department_id = ?";
    $params[] = $deptId;
    $types .= 'i';
}
if ($statusF !== '') {
    $where .= " AND e.status = ?";
    $params[] = $statusF;
    $types .= 's';
}
if ($typeF !== '') {
    $where .= " AND e.employment_type = ?";
    $params[] = $typeF;
    $types .= 's';
}

$countSQL = "SELECT COUNT(*) as c FROM employees e $where";
$dataSQL  = "SELECT e.*, p.title AS position, d.name AS department
             FROM employees e
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN departments d ON e.department_id = d.id
             $where
             ORDER BY e.id ASC
             LIMIT ? OFFSET ?";

// Count
if ($params) {
    $stmt = $conn->prepare($countSQL);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    $allParams = array_merge($params, [$perPage, $offset]);
    $allTypes  = $types . 'ii';
    $stmt = $conn->prepare($dataSQL);
    $stmt->bind_param($allTypes, ...$allParams);
} else {
    $total = $conn->query($countSQL)->fetch_assoc()['c'];
    $stmt  = $conn->prepare($dataSQL);
    $stmt->bind_param('ii', $perPage, $offset);
}

$stmt->execute();
$employees = $stmt->get_result();
$stmt->close();

$totalPages = ceil($total / $perPage);

// Departments for filter dropdown
$depts = $conn->query("SELECT * FROM departments ORDER BY name");

// Success/error message
$msg     = $_SESSION['msg']     ?? '';
$msgType = $_SESSION['msgType'] ?? '';
unset($_SESSION['msg'], $_SESSION['msgType']);

include 'layout.php';
?>

<div class="page-header">
  <h1>Employees</h1>
  <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Employee</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
    <?= $msgType === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<!-- Stats Row -->
<?php
$totalEmp  = $conn->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'];
$fullTime  = $conn->query("SELECT COUNT(*) as c FROM employees WHERE employment_type='Full-Time'")->fetch_assoc()['c'];
$partTime  = $conn->query("SELECT COUNT(*) as c FROM employees WHERE employment_type='Part-Time'")->fetch_assoc()['c'];
$activeEmp = $conn->query("SELECT COUNT(*) as c FROM employees WHERE status='Active'")->fetch_assoc()['c'];
?>
<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-icon red">👥</div>
    <div><div class="stat-label">Total Employees</div><div class="stat-value"><?= $totalEmp ?></div><div class="stat-sub">Active employees</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow">🕐</div>
    <div><div class="stat-label">Full-Time</div><div class="stat-value"><?= $fullTime ?></div><div class="stat-sub">Employees</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">⏱️</div>
    <div><div class="stat-label">Part-Time</div><div class="stat-value"><?= $partTime ?></div><div class="stat-sub">Employees</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">✅</div>
    <div><div class="stat-label">Active</div><div class="stat-value"><?= $activeEmp ?></div><div class="stat-sub">Employees</div></div>
  </div>
</div>

<!-- Employee List Table -->
<div class="table-card">
  <div class="table-card-header">
    <h2>Employee List</h2>
    <div class="table-actions" style="flex-wrap:wrap;gap:8px;">
      <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search employees..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="dept" style="padding:9px 12px;border:2px solid #eee;border-radius:10px;font-family:inherit;font-size:13px;outline:none;">
          <option value="">All Depts</option>
          <?php while ($d = $depts->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>" <?= $deptId == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
          <?php endwhile; ?>
        </select>
        <select name="status" style="padding:9px 12px;border:2px solid #eee;border-radius:10px;font-family:inherit;font-size:13px;outline:none;">
          <option value="">All Status</option>
          <option value="Active" <?= $statusF==='Active'?'selected':'' ?>>Active</option>
          <option value="Inactive" <?= $statusF==='Inactive'?'selected':'' ?>>Inactive</option>
        </select>
        <select name="type" style="padding:9px 12px;border:2px solid #eee;border-radius:10px;font-family:inherit;font-size:13px;outline:none;">
          <option value="">All Types</option>
          <option value="Full-Time" <?= $typeF==='Full-Time'?'selected':'' ?>>Full-Time</option>
          <option value="Part-Time" <?= $typeF==='Part-Time'?'selected':'' ?>>Part-Time</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
        <a href="employees.php" class="btn btn-secondary btn-sm"><i class="fas fa-sync"></i> Reset</a>
      </form>
    </div>
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Employee ID</th>
        <th>Full Name</th>
        <th>Position</th>
        <th>Department</th>
        <th>Type</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = $offset + 1; while ($row = $employees->fetch_assoc()): ?>
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
          <span class="badge <?= $row['employment_type']==='Full-Time' ? 'badge-fulltime' : 'badge-parttime' ?>">
            <?= htmlspecialchars($row['employment_type']) ?>
          </span>
        </td>
        <td>
          <span class="badge <?= $row['status']==='Active' ? 'badge-active' : 'badge-inactive' ?>">
            <?= htmlspecialchars($row['status']) ?>
          </span>
        </td>
        <td>
          <div style="display:flex;gap:6px;">
            <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="View"><i class="fas fa-eye"></i></a>
            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
            <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['full_name'])) ?>')" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
      <?php if ($total === 0): ?>
      <tr><td colspan="8" style="text-align:center;color:#aaa;padding:40px;">No employees found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="table-footer">
    <span>Showing <?= $total > 0 ? $offset+1 : 0 ?> to <?= min($offset+$perPage, $total) ?> of <?= $total ?> entries</span>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php
      $q = $_GET; unset($q['page']);
      $qs = http_build_query($q);
      $qs = $qs ? "&$qs" : '';
      ?>
      <a href="?page=<?= max(1,$page-1).$qs ?>" class="page-btn <?= $page<=1?'disabled':'' ?>">‹</a>
      <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
        <a href="?page=<?= $i.$qs ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page+2 < $totalPages): ?>
        <span class="page-btn" style="cursor:default">…</span>
        <a href="?page=<?= $totalPages.$qs ?>" class="page-btn"><?= $totalPages ?></a>
      <?php endif; ?>
      <a href="?page=<?= min($totalPages,$page+1).$qs ?>" class="page-btn <?= $page>=$totalPages?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'layout_footer.php'; ?>