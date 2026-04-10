<?php
/**
 * Database Migration: Phase 5 Identity Verification (NIN)
 */

require_once 'includes/db.php';

echo "<h2>Dajot Marketplace - Database Migration (Phase 5 - ID Verification)</h2>";
echo "<pre>";

$migrations = [
    // Add NIN columns to Users table
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS nin_number VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS nin_document VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS id_verified_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none'",
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
echo "Migration Phase 5 Complete!\n";
echo "Success: $success | Failed: $failed\n";
echo "========================================\n";
echo "</pre>";

echo "<p><a href='index.php'>← Back to Homepage</a></p>";
?>
