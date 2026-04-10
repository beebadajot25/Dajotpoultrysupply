<?php
include 'includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS price_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    contact_info VARCHAR(255) NOT NULL,
    target_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Table price_alerts created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
