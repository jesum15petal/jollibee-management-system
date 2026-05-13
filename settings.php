<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'Settings';
$activePage = 'settings';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = trim($_POST['current_password'] ?? '');
    $newPass     = trim($_POST['new_password'] ?? '');
    $confirmPass = trim($_POST['confirm_password'] ?? '');
    $fullName    = trim($_POST['full_name'] ?? '');

    $adminId = $_SESSION['admin_id'];

    // Get current admin
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($currentPass, $admin['password'])) {
        $errors[] = 'Current password is incorrect.';
    }

    if (!empty($newPass)) {
        if (strlen($newPass) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($newPass !== $confirmPass) $errors[] = 'New passwords do not match.';
    }

    if (empty($errors)) {
        $fn = $conn->real_escape_string($fullName);
        if (!empty($newPass)) {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $hp = $conn->real_escape_string($hashed);
            $conn->query("UPDATE admins SET full_name='$fn', password='$hp' WHERE id=$adminId");
        } else {
            $conn->query("UPDATE admins SET full_name='$fn' WHERE id=$adminId");
        }
        $_SESSION['admin_name'] = $fullName;
        $success = 'Settings updated successfully!';
    }
}

// Get current admin
$adminId = $_SESSION['admin_id'];
$admin = $conn->query("SELECT * FROM admins WHERE id=$adminId")->fetch_assoc();

include 'layout.php';
?>

<div class="page-header"><h1>Settings</h1></div>

<?php if ($success): ?>
  <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="alert alert-error">⚠️ <div><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div></div>
<?php endif; ?>

<div class="form-card">
  <h3 style="font-size:17px;font-weight:800;margin-bottom:24px;">Account Settings</h3>
  <form method="POST">
    <div class="form-grid">
      <div class="form-group-f full">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>">
      </div>
      <div class="form-group-f full">
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($admin['username']) ?>" disabled style="background:#f9f9f9;color:#aaa;cursor:not-allowed;">
        <p style="font-size:12px;color:#aaa;margin-top:4px;">Username cannot be changed.</p>
      </div>
      <div class="form-group-f full" style="border-top:1px solid #f0f0f0;padding-top:20px;margin-top:8px;">
        <label style="font-size:14px;font-weight:800;color:#333;text-transform:none;letter-spacing:0;">Change Password</label>
        <p style="font-size:12px;color:#aaa;margin-bottom:16px;">Leave blank to keep current password.</p>
      </div>
      <div class="form-group-f">
        <label>Current Password <span style="color:#DA251C">*</span></label>
        <input type="password" name="current_password" placeholder="Enter current password" required>
      </div>
      <div class="form-group-f"></div>
      <div class="form-group-f">
        <label>New Password</label>
        <input type="password" name="new_password" placeholder="Min. 6 characters">
      </div>
      <div class="form-group-f">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" placeholder="Repeat new password">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </div>
  </form>
</div>

<?php include 'layout_footer.php'; ?>