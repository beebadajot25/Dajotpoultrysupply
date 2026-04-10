<?php
/**
 * Database Migration: Fix All Missing Columns
 * Ensures all Phase 1-4 columns exist.
 */

require_once 'includes/db.php';

echo "<h2>Dajot Marketplace - Comprehensive Repair</h2>";
echo "<pre>";

$migrations = [
    // Users Table
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS farm_name VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS farm_location VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_active TIMESTAMP NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_level ENUM('basic', 'verified', 'trusted') DEFAULT 'basic'",
    
    // Listings Table
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0",
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS price_type ENUM('fixed', 'negotiable', 'bulk_discount') DEFAULT 'fixed'",
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS unit VARCHAR(50) DEFAULT 'unit'",
    "ALTER TABLE listings ADD COLUMN IF NOT EXISTS availability ENUM('ready_now', 'available_from') DEFAULT 'ready_now'",
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
echo "Reform Repair Complete!\n";
echo "</pre>";
echo "<p><a href='vendor-shop.php?id=" . ($_SESSION['user_id'] ?? 1) . "'>Check Vendor Shop</a></p>";
?>
