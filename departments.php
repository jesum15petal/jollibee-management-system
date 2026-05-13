<?php
require_once 'db.php';
requireAdmin();

$pageTitle = 'Departments';
$activePage = 'departments';

/* =========================
   ADD / UPDATE DEPARTMENT
========================= */
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $edit_id     = (int)($_POST['edit_id'] ?? 0);

    if ($name === '') {
        $errors[] = "Department name is required.";
    }

    if (empty($errors)) {

        $name        = $conn->real_escape_string($name);
        $description = $conn->real_escape_string($description);

        if ($edit_id > 0) {

            $conn->query("
                UPDATE departments 
                SET name='$name',
                    description='$description'
                WHERE id=$edit_id
            ");

            $_SESSION['msg'] = "Department updated successfully.";

        } else {

            $conn->query("
                INSERT INTO departments(name, description)
                VALUES('$name','$description')
            ");

            $_SESSION['msg'] = "Department added successfully.";
        }

        $_SESSION['msgType'] = "success";
        header("Location: departments.php");
        exit();
    }
}

/* =========================
   DELETE DEPARTMENT
========================= */
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $check = $conn->query("
        SELECT COUNT(*) as total 
        FROM employees 
        WHERE department_id=$id
    ");

    $count = $check->fetch_assoc()['total'];

    if ($count > 0) {

        $_SESSION['msg'] = "Cannot delete department with employees.";
        $_SESSION['msgType'] = "error";

    } else {

        $conn->query("DELETE FROM departments WHERE id=$id");

        $_SESSION['msg'] = "Department deleted successfully.";
        $_SESSION['msgType'] = "success";
    }

    header("Location: departments.php");
    exit();
}

/* =========================
   EDIT DATA
========================= */
$editData = null;

if (isset($_GET['edit'])) {

    $id = (int)$_GET['edit'];

    $res = $conn->query("SELECT * FROM departments WHERE id=$id");

    if ($res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
}

/* =========================
   FETCH DEPARTMENTS (WITH EMPLOYEES)
========================= */
$departments = $conn->query("
    SELECT d.*,
           COUNT(e.id) as employee_count,
           GROUP_CONCAT(e.full_name SEPARATOR ', ') as employee_names
    FROM departments d
    LEFT JOIN employees e
        ON e.department_id = d.id
    GROUP BY d.id
    ORDER BY d.name ASC
");

/* =========================
   MESSAGE
========================= */
$msg     = $_SESSION['msg'] ?? '';
$msgType = $_SESSION['msgType'] ?? '';

unset($_SESSION['msg'], $_SESSION['msgType']);

include 'layout.php';
?>

<div class="page-header">
    <h1>🏢 Departments Management</h1>
</div>

<?php if($msg): ?>
<div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 350px;gap:24px;align-items:start;">

    <!-- TABLE -->
    <div class="table-card">

        <div class="table-card-header">
            <h2>Department List</h2>
        </div>

        <table class="data-table">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Department Name</th>
                    <th>Description</th>
                    <th>Employees</th>
                    <th>Employee Names</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if($departments->num_rows > 0): ?>
                <?php $no = 1; while($row = $departments->fetch_assoc()): ?>

                <tr>
                    <td><?= $no++ ?></td>

                    <td>
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['description'] ?: '—') ?>
                    </td>

                    <td>
                        <span class="badge badge-active">
                            <?= $row['employee_count'] ?>
                        </span>
                    </td>

                    <td style="max-width:250px;">
                        <?= !empty($row['employee_names'])
                            ? htmlspecialchars($row['employee_names'])
                            : '<span style="color:#999;">No Employees</span>' ?>
                    </td>

                    <td>
                        <div style="display:flex;gap:6px;">

                            <a href="departments.php?edit=<?= $row['id'] ?>"
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>

                            <a href="departments.php?delete=<?= $row['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this department?')">
                                <i class="fas fa-trash"></i>
                            </a>

                        </div>
                    </td>
                </tr>

                <?php endwhile; ?>
            <?php else: ?>

                <tr>
                    <td colspan="6" style="text-align:center;padding:30px;color:#999;">
                        No departments found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- FORM -->
    <div class="form-card">

        <h2 style="margin-bottom:20px;">
            <?= $editData ? '✏️ Edit Department' : '➕ Add Department' ?>
        </h2>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach($errors as $e): ?>
                    <?= htmlspecialchars($e) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <?php if($editData): ?>
                <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
            <?php endif; ?>

            <div class="form-group-f">
                <label>Department Name</label>
                <input type="text" name="name"
                       value="<?= htmlspecialchars($editData['name'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group-f" style="margin-top:12px;">
                <label>Description</label>
                <textarea name="description" style="min-height:90px;">
<?= htmlspecialchars($editData['description'] ?? '') ?>
                </textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:15px;">

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $editData ? 'Update' : 'Add' ?>
                </button>

                <?php if($editData): ?>
                    <a href="departments.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>

            </div>

        </form>

    </div>

</div>

<?php include 'layout_footer.php'; ?>