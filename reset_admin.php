<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/db.php';

$username = 'admin';
$password = 'admin123'; 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Admin Password Reset Tool</h3>";
echo "Target Username: <strong>$username</strong><br>";
echo "Target Password: <strong>$password</strong><br>";
echo "Generated Hash: " . substr($hashed_password, 0, 20) . "...<br><br>";

// 1. Check if user exists
$check_sql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "User found. Updating password...<br>";
    $row = $result->fetch_assoc();
    echo "Old stored password (first 20 chars): " . substr($row['password'], 0, 20) . "...<br>";
    
    // Update existing admin
    $stmt = $conn->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
    $stmt->bind_param("ss", $hashed_password, $username);
    
    if ($stmt->execute()) {
        echo "<h4 style='color:green'>Success! Password updated.</h4>";
    } else {
        echo "<h4 style='color:red'>Error updating: " . $conn->error . "</h4>";
    }
    $stmt->close();
} else {
    echo "User NOT found. Creating new admin user...<br>";
    // Create admin if not exists
    $email = 'admin@dajot.com';
    $role = 'admin';
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "<h4 style='color:green'>Success! Admin account created.</h4>";
    } else {
        echo "<h4 style='color:red'>Error creating: " . $conn->error . "</h4>";
    }
    $stmt->close();
}

// 2. Verification Check
$verify_sql = "SELECT password FROM users WHERE username = '$username'";
$verify_res = $conn->query($verify_sql);
$verify_row = $verify_res->fetch_assoc();

echo "<br><strong>Verification:</strong><br>";
if (password_verify($password, $verify_row['password'])) {
    echo "<span style='color:green; font-weight:bold;'>CHECK PASSED: Login should work now.</span>";
} else {
    echo "<span style='color:red; font-weight:bold;'>CHECK FAILED: DB update didn't persist correctly.</span>";
}
?>
