<?php
include 'includes/db.php';

echo "<h2>Force Admin Password Reset</h2>";

$user_id = 1; // We confirmed admin is ID 1
$new_pass = 'dajot12345';
$hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

// 1. Update Password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hashed_pass, $user_id);

if ($stmt->execute()) {
    echo "<p style='color:green'>✅ Password updated database record for User ID $user_id.</p>";
} else {
    echo "<p style='color:red'>❌ Update failed: " . $stmt->error . "</p>";
}

// 2. Verify Immediately
$res = $conn->query("SELECT password FROM users WHERE id = $user_id");
$row = $res->fetch_assoc();
$stored_hash = $row['password'];

echo "<p>Testing Login Logic...</p>";
if (password_verify($new_pass, $stored_hash)) {
    echo "<h2 style='color:green'>✅ SUCCESS: Password '$new_pass' is working!</h2>";
    echo "<p>You can now <a href='admin/index.php'>Login Here</a> with:</p>";
    echo "<ul><li>Username: <strong>admin</strong></li><li>Password: <strong>$new_pass</strong></li></ul>";
} else {
    echo "<h2 style='color:red'>❌ FAIL: Verification failed despite update.</h2>";
    echo "<p>Stored Hash: $stored_hash</p>";
}
?>
