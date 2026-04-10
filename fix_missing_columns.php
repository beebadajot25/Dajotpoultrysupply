<?php
include 'includes/db.php';

echo "<h1>Database Repair Tool</h1>";

// 1. Add Missing Columns to listings
echo "<h3>Checking 'listings' table...</h3>";
$columns = [
    "description" => "TEXT",
    "quantity" => "INT(11) DEFAULT 1",
    "unit" => "VARCHAR(20) DEFAULT 'unit'"
];

foreach ($columns as $col => $def) {
    $check = $conn->query("SHOW COLUMNS FROM listings LIKE '$col'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE listings ADD COLUMN $col $def";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>✅ Added column: <strong>$col</strong></p>";
        } else {
            echo "<p style='color:red'>❌ Error adding $col: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Column <strong>$col</strong> already exists.</p>";
    }
}

// 2. Update Free Plan Limit
echo "<h3>Updating Subscription Plans...</h3>";
$sql_plan = "UPDATE subscription_plans SET listing_limit = 3 WHERE name = 'Free'";
if ($conn->query($sql_plan) === TRUE) {
    echo "<p style='color:green'>✅ Free plan limit updated to 3.</p>";
} else {
    echo "<p style='color:red'>❌ Error updating plan: " . $conn->error . "</p>";
}

echo "<hr>";
echo "<h2>🎉 Repair Complete!</h2>";
echo "<p>You can now <a href='vendor-dashboard.php'>Return to Dashboard</a> and try adding your listing again.</p>";
?>
