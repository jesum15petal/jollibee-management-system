<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'View Employee';
$activePage = 'employees';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: employees.php"); exit(); }

$sql = "SELECT e.*, p.title AS position, d.name AS department
        FROM employees e
        LEFT JOIN positions p ON e.position_id = p.id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$emp) {
    header("Location: employees.php");
    exit();
}

include 'layout.php';
?>

<div class="page-header">
  <h1>Employee Details</h1>
  <div style="display:flex;gap:10px;">
    <a href="edit.php?id=<?= $id ?>" class="btn btn-warning"><i class="fas fa-pen"></i> Edit</a>
    <a href="employees.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start;">
  <!-- Profile Card -->
  <div style="background:#fff;border-radius:18px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.05);text-align:center;">
    <div style="width:110px;height:110px;border-radius:50%;overflow:hidden;background:#ffe5e5;margin:0 auto 16px;border:4px solid #f0f0f0;display:flex;align-items:center;justify-content:center;font-size:42px;">
      <?php if ($emp['photo'] && file_exists('uploads/'.$emp['photo'])): ?>
        <img src="uploads/<?= htmlspecialchars($emp['photo']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
      <?php else: ?>
        👤
      <?php endif; ?>
    </div>
    <h2 style="font-size:18px;font-weight:900;color:#1a1a1a;margin-bottom:4px;"><?= htmlspecialchars($emp['full_name']) ?></h2>
    <p style="color:#888;font-size:13px;margin-bottom:16px;"><?= htmlspecialchars($emp['position'] ?? 'No Position') ?></p>
    <span class="badge <?= $emp['status']==='Active'?'badge-active':'badge-inactive' ?>" style="font-size:13px;padding:6px 18px;">
      <?= htmlspecialchars($emp['status']) ?>
    </span>
    <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f0f0f0;">
      <div style="font-size:13px;color:#888;margin-bottom:4px;">Employee ID</div>
      <div style="font-size:18px;font-weight:900;color:#DA251C;"><?= htmlspecialchars($emp['employee_id']) ?></div>
    </div>
    <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f0f0f0;">
      <span class="badge <?= $emp['employment_type']==='Full-Time'?'badge-fulltime':'badge-parttime' ?>">
        <?= htmlspecialchars($emp['employment_type']) ?>
      </span>
    </div>
    <div style="margin-top:20px;display:flex;gap:8px;">
      <a href="edit.php?id=<?= $id ?>" class="btn btn-warning btn-sm" style="flex:1;justify-content:center;"><i class="fas fa-pen"></i> Edit</a>
      <button onclick="confirmDelete(<?= $emp['id'] ?>, '<?= htmlspecialchars(addslashes($emp['full_name'])) ?>')" class="btn btn-danger btn-sm" style="flex:1;justify-content:center;"><i class="fas fa-trash"></i> Delete</button>
    </div>
  </div>

  <!-- Details Card -->
  <div style="background:#fff;border-radius:18px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
    <h3 style="font-size:16px;font-weight:800;color:#1a1a1a;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f0f0f0;">Personal Information</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <?php
      $fields = [
        'Email'           => $emp['email'],
        'Phone'           => $emp['phone'],
        'Department'      => $emp['department'],
        'Position'        => $emp['position'],
        'Employment Type' => $emp['employment_type'],
        'Status'          => $emp['status'],
        'Hire Date'       => $emp['hire_date'] ? date('F j, Y', strtotime($emp['hire_date'])) : null,
        'Date Added'      => date('F j, Y', strtotime($emp['created_at'])),
      ];
      foreach ($fields as $label => $val):
      ?>
      <div style="<?= in_array($label,['Address'])? 'grid-column:1/-1':'' ?>">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;color:#aaa;margin-bottom:6px;"><?= $label ?></div>
        <div style="font-size:15px;font-weight:700;color:#333;"><?= htmlspecialchars($val ?? '—') ?></div>
      </div>
      <?php endforeach; ?>
      <?php if ($emp['address']): ?>
      <div style="grid-column:1/-1;">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;color:#aaa;margin-bottom:6px;">Address</div>
        <div style="font-size:15px;font-weight:700;color:#333;"><?= nl2br(htmlspecialchars($emp['address'])) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'layout_footer.php'; ?>