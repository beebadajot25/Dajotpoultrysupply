<?php
include 'includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS price_alerts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_contact VARCHAR(100) NOT NULL,
    listing_id INT(11) NOT NULL,
    original_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn) {
    if ($conn->query($sql) === TRUE) {
        echo "<h2>✅ Price Alerts table ready.</h2>";
        echo "<p><a href='index.php'>Go Home</a></p>";
    } else {
        echo "<h2>❌ Error: " . $conn->error . "</h2>";
    }
} else {
    echo "<h2>❌ Database connection failed.</h2>";
}
?>
