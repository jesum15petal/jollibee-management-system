<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'Add Employee';
$activePage = 'employees';

$errors = [];
$data   = [
    'employee_id'     => '',
    'full_name'       => '',
    'email'           => '',
    'phone'           => '',
    'position_id'     => '',
    'department_id'   => '',
    'employment_type' => 'Full-Time',
    'status'          => 'Active',
    'hire_date'       => '',
    'address'         => '',
];

// Fetch dropdowns
$departments = $conn->query("SELECT * FROM departments ORDER BY name");
$positions   = $conn->query("SELECT p.*, d.name AS dept_name FROM positions p LEFT JOIN departments d ON p.department_id=d.id ORDER BY p.title");

// Generate next Employee ID
$lastID = $conn->query("SELECT employee_id FROM employees ORDER BY id DESC LIMIT 1")->fetch_assoc();
if ($lastID) {
    $num = (int)substr($lastID['employee_id'], 1) + 1;
    $data['employee_id'] = 'E' . str_pad($num, 5, '0', STR_PAD_LEFT);
} else {
    $data['employee_id'] = 'E00001';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $_) {
        $data[$key] = trim($_POST[$key] ?? '');
    }

    // Validate
    if (empty($data['full_name']))   $errors[] = 'Full name is required.';
    if (empty($data['employee_id'])) $errors[] = 'Employee ID is required.';
    if (empty($data['email']))       $errors[] = 'Email is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

    // Unique employee_id check
    $chk = $conn->prepare("SELECT id FROM employees WHERE employee_id = ?");
    $chk->bind_param("s", $data['employee_id']);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) $errors[] = 'Employee ID already exists.';
    $chk->close();

    // Unique email check
    $chk2 = $conn->prepare("SELECT id FROM employees WHERE email = ?");
    $chk2->bind_param("s", $data['email']);
    $chk2->execute();
    if ($chk2->get_result()->num_rows > 0) $errors[] = 'Email already in use.';
    $chk2->close();

    // Photo upload
    $photoName = null;
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid photo format. Allowed: jpg, jpeg, png, gif, webp.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Photo must be under 2MB.';
        } else {
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
        $pos = ($data['position_id']   !== '') ? (int)$data['position_id']   : 'NULL';
        $dep = ($data['department_id'] !== '') ? (int)$data['department_id'] : 'NULL';
        $et  = $conn->real_escape_string($data['employment_type']);
        $st  = $conn->real_escape_string($data['status']);
        $ph2 = $photoName ? "'" . $conn->real_escape_string($photoName) . "'" : 'NULL';
        $hd  = ($data['hire_date'] !== '') ? "'" . $conn->real_escape_string($data['hire_date']) . "'" : 'NULL';
        $ad  = $conn->real_escape_string($data['address']);

        $sql = "INSERT INTO employees
                    (employee_id, full_name, email, phone, position_id, department_id,
                     employment_type, status, photo, hire_date, address)
                VALUES
                    ('$ei', '$fn', '$em', '$ph', $pos, $dep, '$et', '$st', $ph2, $hd, '$ad')";

        if ($conn->query($sql)) {
            $_SESSION['msg']     = 'Employee added successfully!';
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
  <h1>Add Employee</h1>
  <a href="employees.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error">
    ⚠️ <div><strong>Please fix the following:</strong>
      <ul style="margin-top:6px;padding-left:20px;">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" enctype="multipart/form-data">
    <div class="form-grid">

      <!-- Photo Upload -->
      <div class="form-group-f full" style="display:flex;gap:20px;align-items:flex-start;margin-bottom:24px;">
        <div id="photoPreviewWrap" style="width:90px;height:90px;border-radius:50%;background:#f0f0f0;overflow:hidden;border:3px solid #eee;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:32px;">👤</div>
        <div>
          <label style="display:block;font-size:12px;font-weight:800;color:#555;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Employee Photo</label>
          <input type="file" name="photo" id="photoInput" accept="image/*" style="font-family:inherit;font-size:13px;">
          <p style="font-size:12px;color:#aaa;margin-top:6px;">JPG, PNG, WEBP · Max 2MB</p>
        </div>
      </div>

      <div class="form-group-f">
        <label>Employee ID <span style="color:#DA251C">*</span></label>
        <input type="text" name="employee_id" value="<?= htmlspecialchars($data['employee_id']) ?>" required>
      </div>
      <div class="form-group-f">
        <label>Full Name <span style="color:#DA251C">*</span></label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($data['full_name']) ?>" placeholder="e.g. Maria Clara Dela Cruz" required>
      </div>
      <div class="form-group-f">
        <label>Email Address <span style="color:#DA251C">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" placeholder="e.g. maria@jollibee.com.ph">
      </div>
      <div class="form-group-f">
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>" placeholder="e.g. 09171234567">
      </div>
      <div class="form-group-f">
        <label>Department</label>
        <select name="department_id">
          <option value="">— Select Department —</option>
          <?php $departments->data_seek(0); while ($d = $departments->fetch_assoc()): ?>
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
          <?php $positions->data_seek(0); while ($p = $positions->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>" <?= $data['position_id'] == $p['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['dept_name'] ?? '') ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group-f">
        <label>Employment Type</label>
        <select name="employment_type">
          <option value="Full-Time"  <?= $data['employment_type']==='Full-Time'  ? 'selected' : '' ?>>Full-Time</option>
          <option value="Part-Time"  <?= $data['employment_type']==='Part-Time'  ? 'selected' : '' ?>>Part-Time</option>
        </select>
      </div>
      <div class="form-group-f">
        <label>Status</label>
        <select name="status">
          <option value="Active"   <?= $data['status']==='Active'   ? 'selected' : '' ?>>Active</option>
          <option value="Inactive" <?= $data['status']==='Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="form-group-f">
        <label>Hire Date</label>
        <input type="date" name="hire_date" value="<?= htmlspecialchars($data['hire_date']) ?>">
      </div>
      <div class="form-group-f full">
        <label>Address</label>
        <textarea name="address" placeholder="Full address..."><?= htmlspecialchars($data['address']) ?></textarea>
      </div>

    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Employee</button>
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
      wrap.innerHTML = '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
  }
});
</script>

<?php include 'layout_footer.php'; ?>