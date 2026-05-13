<?php
require_once 'db.php';
requireAdmin();

$pageTitle  = 'Positions';
$activePage = 'positions';

$errors = [];
$editPos = null;
$depts   = $conn->query("SELECT * FROM departments ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $deptId = (int)($_POST['department_id'] ?? 0);
    $editId = (int)($_POST['edit_id'] ?? 0);

    if (empty($title)) $errors[] = 'Position title is required.';

    if (empty($errors)) {
        $t  = $conn->real_escape_string($title);
        $di = $deptId ?: 'NULL';
        if ($editId) {
            $conn->query("UPDATE positions SET title='$t', department_id=$di WHERE id=$editId");
            $_SESSION['msg'] = 'Position updated.'; $_SESSION['msgType'] = 'success';
        } else {
            $conn->query("INSERT INTO positions (title, department_id) VALUES ('$t',$di)");
            $_SESSION['msg'] = 'Position added.'; $_SESSION['msgType'] = 'success';
        }
        header("Location: positions.php"); exit();
    }
}

if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $editPos = $conn->query("SELECT * FROM positions WHERE id=$eid")->fetch_assoc();
}
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $conn->query("DELETE FROM positions WHERE id=$did");
    $_SESSION['msg'] = 'Position deleted.'; $_SESSION['msgType'] = 'success';
    header("Location: positions.php"); exit();
}

$positions = $conn->query("SELECT p.*, d.name AS dept_name, COUNT(e.id) as emp_count FROM positions p LEFT JOIN departments d ON p.department_id=d.id LEFT JOIN employees e ON e.position_id=p.id GROUP BY p.id ORDER BY p.title");

$msg = $_SESSION['msg'] ?? ''; $msgType = $_SESSION['msgType'] ?? '';
unset($_SESSION['msg'], $_SESSION['msgType']);

include 'layout.php';
?>

<div class="page-header"><h1>Positions</h1></div>

<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType==='success'?'success':'error' ?>"><?= $msgType==='success'?'✅':'⚠️' ?> <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
  <div class="table-card">
    <div class="table-card-header"><h2>Position List</h2></div>
    <table class="data-table">
      <thead><tr><th>#</th><th>Title</th><th>Department</th><th>Employees</th><th>Actions</th></tr></thead>
      <tbody>
        <?php $n=1; while ($p=$positions->fetch_assoc()): ?>
        <tr>
          <td><?= $n++ ?></td>
          <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
          <td><?= htmlspecialchars($p['dept_name'] ?? '—') ?></td>
          <td><span class="badge badge-active"><?= $p['emp_count'] ?></span></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen"></i></a>
              <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this position?')"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="form-card" style="max-width:100%;">
    <h3 style="font-size:17px;font-weight:800;margin-bottom:20px;"><?= $editPos ? '✏️ Edit Position' : '➕ Add Position' ?></h3>
    <?php if ($errors): ?>
      <div class="alert alert-error">⚠️ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>
    <form method="POST">
      <?php if ($editPos): ?><input type="hidden" name="edit_id" value="<?= $editPos['id'] ?>"><?php endif; ?>
      <div style="margin-bottom:16px;">
        <div class="form-group-f">
          <label>Position Title <span style="color:#DA251C">*</span></label>
          <input type="text" name="title" value="<?= htmlspecialchars($editPos['title'] ?? '') ?>" placeholder="e.g. Service Crew" required>
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <div class="form-group-f">
          <label>Department</label>
          <select name="department_id">
            <option value="">— Select Department —</option>
            <?php
            $depts->data_seek(0);
            while ($d=$depts->fetch_assoc()):
            ?>
              <option value="<?= $d['id'] ?>" <?= ($editPos && $editPos['department_id']==$d['id'])?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> <?= $editPos ? 'Update' : 'Add Position' ?></button>
        <?php if ($editPos): ?><a href="positions.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php include 'layout_footer.php'; ?>