<?php
/**
 * Database Migration: Phase 2 Reviews & Ratings
 */

require_once 'includes/db.php';

echo "<h2>Dajot Marketplace - Database Migration (Phase 2)</h2>";
echo "<pre>";

$migrations = [
    // Create Reviews Table
    "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        reviewer_name VARCHAR(100) NOT NULL,
        reviewer_email VARCHAR(100),
        rating TINYINT(1) NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    // Add Views column to listings if not exists (missed in some environments?)
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS views INT DEFAULT 0",
    
    // Add Last Active and Verification Request columns if needed
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_requested_at TIMESTAMP NULL",
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
echo "Migration Phase 2 Complete!\n";
echo "Success: $success | Failed: $failed\n";
echo "========================================\n";
echo "</pre>";

echo "<p><a href='index.php'>← Back to Homepage</a></p>";
?>
