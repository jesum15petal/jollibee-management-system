<?php
require_once 'db.php';
requireAdmin();

// Check if ID exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: employees.php");
    exit();
}

$id = (int)$_GET['id'];

// Get employee photo first
$stmt = $conn->prepare("SELECT photo FROM employees WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    // Delete photo file if exists
    if (!empty($row['photo']) && file_exists("uploads/" . $row['photo'])) {
        unlink("uploads/" . $row['photo']);
    }

    // Delete employee from database
    $delete = $conn->prepare("DELETE FROM employees WHERE id=?");
    $delete->bind_param("i", $id);

    if ($delete->execute()) {
        $_SESSION['msg'] = "Employee deleted successfully.";
        $_SESSION['msgType'] = "success";
    } else {
        $_SESSION['msg'] = "Delete failed.";
        $_SESSION['msgType'] = "error";
    }
}

// Redirect back
header("Location: employees.php");
exit();
?>