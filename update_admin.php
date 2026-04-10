<?php
include 'includes/db.php';

$username = 'admin';
$password = 'admin123'; // The default password we want to hash

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$check_sql = "SELECT id FROM users WHERE username = '$username'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    // Update existing admin
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashed_password, $username);
    
    if ($stmt->execute()) {
        echo "Success: Admin password has been updated to a secure hash.<br>";
        echo "You can now login with: <b>$password</b>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
} else {
    // Create admin if not exists
    $email = 'admin@dajot.com';
    $role = 'admin';
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "Success: Admin account created with secure hash.<br>";
        echo "You can now login with: <b>$password</b>";
    } else {
        echo "Error creating record: " . $conn->error;
    }
    $stmt->close();
}
?>
