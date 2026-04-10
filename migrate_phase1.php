<?php
/**
 * Database Migration: Phase 1 Quick Wins
 * Run this file once to add new columns
 */

require_once 'includes/db.php';

echo "<h2>Dajot Marketplace - Database Migration (Phase 1)</h2>";
echo "<pre>";

$migrations = [
    // Listings table enhancements
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS price_type ENUM('fixed', 'negotiable', 'bulk') DEFAULT 'fixed'",
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0",
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS availability ENUM('ready_now', 'available_from') DEFAULT 'ready_now'",
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS availability_date DATE NULL",
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS views INT DEFAULT 0",
    
    // Users table enhancements
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_active TIMESTAMP NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_level ENUM('basic', 'verified', 'trusted') DEFAULT 'basic'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone_verified BOOLEAN DEFAULT FALSE",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS farm_name VARCHAR(100) NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS farm_location VARCHAR(255) NULL",
];

$success = 0;
$failed = 0;

foreach ($migrations as $sql) {
    if ($conn->query($sql)) {
        echo "✅ SUCCESS: " . substr($sql, 0, 60) . "...\n";
        $success++;
    } else {
        // Check if it's a duplicate column error (which is OK)
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
echo "Migration Complete!\n";
echo "Success: $success | Failed: $failed\n";
echo "========================================\n";
echo "</pre>";

echo "<p><a href='index.php'>← Back to Homepage</a></p>";
?>
