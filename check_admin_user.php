<?php
include 'includes/db.php';

echo "<h2>Admin User Check</h2>";

$sql = "SELECT id, username, role FROM users WHERE role = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p style='color:green'>✅ Found " . $result->num_rows . " admin user(s):</p>";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Username: <strong>" . $row['username'] . "</strong> | Role: " . $row['role'] . "<br>";
    }
} else {
    echo "<p style='color:red'>❌ No user with role 'admin' found.</p>";
    echo "<p>Attempting to create default admin...</p>";
    
    // Create default admin
    $username = 'admin';
    $password = password_hash('dajot12345', PASSWORD_DEFAULT);
    $role = 'admin';
    $email = 'admin@dajot.com';
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $role, $email);
    
    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ Created new admin user: <strong>admin</strong> (Password: dajot12345)</p>";
    } else {
        echo "<p style='color:red'>❌ Error creating admin: " . $conn->error . "</p>";
    }
}
?>
