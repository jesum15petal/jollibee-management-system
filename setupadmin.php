<?php
// Run this file ONCE to create the admin account with a proper hashed password
// Access: http://localhost/jollibee_ems/setup_admin.php
// Then DELETE this file after running it!

require_once 'db.php';

$username  = 'jesum';
$password  = 'jesum0987'; // Change this to your desired password
$full_name = 'Admin Manager';
$role      = 'Super Administrator';
$hashed    = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (username, password, full_name, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)");
$stmt->bind_param("ssss", $username, $hashed, $full_name, $role);

if ($stmt->execute()) {
    echo "<h2 style='font-family:sans-serif;color:green;'>✅ Admin account created/updated!</h2>";
    echo "<p style='font-family:sans-serif;'>Username: <b>$username</b><br>Password: <b>$password</b></p>";
    echo "<p style='font-family:sans-serif;color:red;'><b>⚠️ DELETE this file (setup_admin.php) now for security!</b></p>";
    echo "<a href='login.php' style='font-family:sans-serif;'>Go to Login</a>";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>