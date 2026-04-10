<?php
include 'includes/db.php';

$columns = [
    "phone VARCHAR(50)" => "phone",
    "whatsapp VARCHAR(50)" => "whatsapp",
    "location VARCHAR(255)" => "location",
    "nin VARCHAR(50)" => "nin",
    "status VARCHAR(20) DEFAULT 'active'" => "status",
    "plan VARCHAR(20) DEFAULT 'free'" => "plan",
    "plan_expires DATETIME NULL" => "plan_expires"
];

echo "<h2>Updating Users Table Schema</h2>";

foreach ($columns as $def => $col) {
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
    if ($check && $check->num_rows == 0) {
        // Add column
        $sql = "ALTER TABLE users ADD COLUMN $def";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>✅ Added column: <strong>$col</strong></p>";
        } else {
            echo "<p style='color:red'>❌ Error adding $col: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Column <strong>$col</strong> already exists.</p>";
    }
}

// Create Reports/Testimonies Table for the second part of the request
echo "<h2>Creating Reports/Testimonies Table</h2>";
$sql_reports = "CREATE TABLE IF NOT EXISTS reports (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    type ENUM('report', 'testimony') NOT NULL,
    reporter_name VARCHAR(100) NOT NULL,
    reporter_contact VARCHAR(100) NOT NULL,
    target_farmer VARCHAR(100), -- Name of farmer being reported/praised
    message TEXT NOT NULL,
    status ENUM('pending', 'published') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_reports) === TRUE) {
    echo "<p style='color:green'>✅ Reports table ready.</p>";
} else {
    echo "<p style='color:red'>❌ Error creating reports table: " . $conn->error . "</p>";
}

// Create Subscriptions Table
echo "<h2>Creating Subscriptions Table</h2>";
$sql_subs = "CREATE TABLE IF NOT EXISTS subscriptions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    plan VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(100),
    status VARCHAR(20) DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_subs) === TRUE) {
    echo "<p style='color:green'>✅ Subscriptions table ready.</p>";
} else {
    echo "<p style='color:red'>❌ Error creating subscriptions table: " . $conn->error . "</p>";
}

echo "<hr><p>Done. <a href='index.php'>Go Home</a></p>";
?>
