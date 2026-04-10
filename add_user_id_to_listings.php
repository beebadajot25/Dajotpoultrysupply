<?php
include 'includes/db.php';

// Add user_id column if it doesn't exist
$sql = "SHOW COLUMNS FROM listings LIKE 'user_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alter_sql = "ALTER TABLE listings ADD COLUMN user_id INT(11) DEFAULT NULL AFTER id";
    if ($conn->query($alter_sql) === TRUE) {
        echo "Success: user_id column added to listings table.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "Column user_id already exists.<br>";
}

// Optional: Add Foreign Key constraint if users table exists
// $fk_sql = "ALTER TABLE listings ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
// $conn->query($fk_sql);

echo "Migration check complete.";
?>
