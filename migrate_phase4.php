<?php
/**
 * Database Migration: Phase 4 Advanced Features (Maps)
 */

require_once 'includes/db.php';

echo "<h2>Dajot Marketplace - Database Migration (Phase 4)</h2>";
echo "<pre>";

$migrations = [
    // Add Latitude and Longitude to Users
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS lat DECIMAL(10, 8) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS lng DECIMAL(11, 8) DEFAULT NULL",
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
echo "Migration Phase 4 Complete!\n";
echo "Success: $success | Failed: $failed\n";
echo "========================================\n";
echo "</pre>";

echo "<p><a href='index.php'>← Back to Homepage</a></p>";
?>
