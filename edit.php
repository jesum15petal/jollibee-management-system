<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'Edit Employee';
$activePage = 'employees';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: employees.php"); exit(); }

// Fetch employee
$empSQL  = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$empSQL->bind_param("i", $id);
$empSQL->execute();
$empResult = $empSQL->get_result();
$emp = $empResult->fetch_assoc();
$empSQL->close();

if (!$emp) {
    $_SESSION['msg']     = 'Employee not found.';
    $_SESSION['msgType'] = 'error';
    header("Location: employees.php");
    exit();
}

$errors = [];
$data   = $emp; // pre-fill with current data

// Fetch dropdowns
$departments = $conn->query("SELECT * FROM departments ORDER BY name");
$positions   = $conn->query("SELECT p.*, d.name AS dept_name FROM positions p LEFT JOIN departments d ON p.department_id=d.id ORDER BY p.title");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect input
    $fields = ['employee_id','full_name','email','phone','position_id','department_id','employment_type','status','hire_date','address'];
    foreach ($fields as $key) {
        $data[$key] = trim($_POST[$key] ?? '');
    }

    // Validate
    if (empty($data['full_name']))   $errors[] = 'Full name is required.';
    if (empty($data['employee_id'])) $errors[] = 'Employee ID is required.';
    if (empty($data['email']))       $errors[] = 'Email is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

    // Unique employee_id (excluding self)
    $chk = $conn->prepare("SELECT id FROM employees WHERE employee_id = ? AND id != ?");
    $chk->bind_param("si", $data['employee_id'], $id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) $errors[] = 'Employee ID already exists.';
    $chk->close();

    // Unique email (excluding self)
    $chk2 = $conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
    $chk2->bind_param("si", $data['email'], $id);
    $chk2->execute();
    if ($chk2->get_result()->num_rows > 0) $errors[] = 'Email already in use.';
    $chk2->close();

    // Handle photo upload
    $photoName = $emp['photo']; // keep old photo by default
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid photo format.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Photo must be under 2MB.';
        } else {
            // Delete old photo
            if ($emp['photo'] && file_exists('uploads/'.$emp['photo'])) {
                unlink('uploads/'.$emp['photo']);
            }
            $photoName = uniqid('emp_') . '.' . $ext;
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photoName);
        }
    }

    if (empty($errors)) {
        $ei  = $conn->real_escape_string($data['employee_id']);
        $fn  = $conn->real_escape_string($data['full_name']);
        $em  = $conn->real_escape_string($data['email']);
        $ph  = $conn->real_escape_string($data['phone']);
        $pos = $data['position_id']   !== '' ? (int)$data['position_id']   : 'NULL';
        $dep = $data['department_id'] !== '' ? (int)$data['department_id'] : 'NULL';
        $et  = $conn->real_escape_string($data['employment_type']);
        $st  = $conn->real_escape_string($data['status']);
        $ph2 = $photoName ? "'".$conn->real_escape_string($photoName)."'" : 'NULL';
        $hd2 = $data['hire_date'] !== '' ? "'".$conn->real_escape_string($data['hire_date'])."'" : 'NULL';
        $ad  = $conn->real_escape_string($data['address']);

        $updateSQL = "UPDATE employees SET
            employee_id = '$ei', full_name = '$fn', email = '$em', phone = '$ph',
            position_id = $pos, department_id = $dep,
            employment_type = '$et', status = '$st',
            photo = $ph2, hire_date = $hd2, address = '$ad'
            WHERE id = $id";

        if ($conn->query($updateSQL)) {
            $_SESSION['msg']     = 'Employee updated successfully!';
            $_SESSION['msgType'] = 'success';
            header("Location: employees.php");
            exit();
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
}

include 'layout.php';
?>

<div class="page-header">
  <h1>Edit Employee</h1>
  <a href="employees.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error">
    ⚠️ <div><strong>Please fix the following:</strong>
    <ul style="margin-top:6px;padding-left:20px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  </div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" enctype="multipart/form-data">
    <div class="form-grid">

      <!-- Photo -->
      <div class="form-group-f full" style="display:flex;gap:20px;align-items:flex-start;margin-bottom:24px;">
        <div id="photoPreviewWrap" style="width:90px;height:90px;border-radius:50%;background:#f0f0f0;overflow:hidden;border:3px solid #eee;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:32px;">
          <?php if ($data['photo'] && file_exists('uploads/'.$data['photo'])): ?>
            <img src="uploads/<?= htmlspecialchars($data['photo']) ?>" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            👤
          <?php endif; ?>
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:800;color:#555;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Employee Photo</label>
          <input type="file" name="photo" id="photoInput" accept="image/*" style="font-family:inherit;font-size:13px;">
          <p style="font-size:12px;color:#aaa;margin-top:6px;">Leave empty to keep current photo · Max 2MB</p>
        </div>
      </div>

      <div class="form-group-f">
        <label>Employee ID <span style="color:#DA251C">*</span></label>
        <input type="text" name="employee_id" value="<?= htmlspecialchars($data['employee_id']) ?>" required>
      </div>
      <div class="form-group-f">
        <label>Full Name <span style="color:#DA251C">*</span></label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($data['full_name']) ?>" required>
      </div>
      <div class="form-group-f">
        <label>Email Address <span style="color:#DA251C">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>">
      </div>
      <div class="form-group-f">
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>">
      </div>
      <div class="form-group-f">
        <label>Department</label>
        <select name="department_id">
          <option value="">— Select Department —</option>
          <?php
          $departments->data_seek(0);
          while ($d = $departments->fetch_assoc()):
          ?>
            <option value="<?= $d['id'] ?>" <?= $data['department_id'] == $d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group-f">
        <label>Position</label>
        <select name="position_id">
          <option value="">— Select Position —</option>
          <?php
          $positions->data_seek(0);
          while ($p = $positions->fetch_assoc()):
          ?>
            <option value="<?= $p['id'] ?>" <?= $data['position_id'] == $p['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['dept_name'] ?? '') ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group-f">
        <label>Employment Type</label>
        <select name="employment_type">
          <option value="Full-Time"  <?= $data['employment_type']==='Full-Time'  ?'selected':'' ?>>Full-Time</option>
          <option value="Part-Time"  <?= $data['employment_type']==='Part-Time'  ?'selected':'' ?>>Part-Time</option>
        </select>
      </div>
      <div class="form-group-f">
        <label>Status</label>
        <select name="status">
          <option value="Active"   <?= $data['status']==='Active'   ?'selected':'' ?>>Active</option>
          <option value="Inactive" <?= $data['status']==='Inactive' ?'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <div class="form-group-f">
        <label>Hire Date</label>
        <input type="date" name="hire_date" value="<?= htmlspecialchars($data['hire_date'] ?? '') ?>">
      </div>
      <div class="form-group-f full">
        <label>Address</label>
        <textarea name="address"><?= htmlspecialchars($data['address'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Employee</button>
      <a href="employees.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(ev) {
      const wrap = document.getElementById('photoPreviewWrap');
      wrap.innerHTML = '<img src="'+ev.target.result+'" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
  }
});
</script>

<?php include 'layout_footer.php'; ?>