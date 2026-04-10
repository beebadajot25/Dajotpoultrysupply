<?php
/**
 * Database Migration: Phase 3 Paystack & Subscriptions
 */

require_once 'includes/db.php';

echo "<h2>Dajot Marketplace - Database Migration (Phase 3)</h2>";
echo "<pre>";

$migrations = [
    // Create Subscriptions Table
    "CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan VARCHAR(20) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        reference VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Add Plan Expiry to Users
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS plan_expires TIMESTAMP NULL",
];

$success = 0;
$failed = 0;

foreach ($migrations as $sql) {
    if ($conn->query($sql)) {
        echo "✅ SUCCESS: " . substr($sql, 0, 60) . "...\n";
        $success++;
    } else {
        if (strpos($conn->error, 'Duplicate column') !== false) {
            echo "⏭️ SKIPPED (already exists): " . substr($sql, 0, 50) . "...\n";
        } else {
            echo "❌ FAILED: " . $conn->error . "\n";
            $failed++;
        }
    }
}

echo "\n";
echo "========================================\n";
echo "Migration Phase 3 Complete!\n";
echo "Success: $success | Failed: $failed\n";
echo "========================================\n";
echo "</pre>";

echo "<p><a href='index.php'>← Back to Homepage</a></p>";
?>
