<?php
include 'includes/db.php';

$username = 'admin'; // Default admin username
$new_password = 'dajot12345';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update all admin users (or specific one if you prefer)
$sql = "UPDATE users SET password = '$hashed_password' WHERE role = 'admin'";

if ($conn->query($sql) === TRUE) {
    echo "<h1>Admin Password Updated</h1>";
    echo "<p>The password for all admin accounts has been changed to: <strong>$new_password</strong></p>";
    echo "<p><a href='admin/index.php'>Go to Admin Login</a></p>";
} else {
    echo "Error updating record: " . $conn->error;
}
?>
