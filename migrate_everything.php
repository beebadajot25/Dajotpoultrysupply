<?php
/**
 * Master Database Migration - Fix Everything
 * Consolidates all migrations from Phases 1-4 and Chat system.
 */

require_once 'includes/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<head><title>Master Migration - Dajot</title></head>";
echo "<body style='font-family: sans-serif; line-height: 1.6; color: #333; padding: 40px; background: #f4f7f6;'>";
echo "<div style='max-width: 800px; margin: 0 auto; background: #white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);'>";
echo "<h2 style='color: #2e7d32; margin-top: 0;'>🚀 Dajot Marketplace - Master Migration</h2>";
echo "<p>Checking and updating your database schema...</p>";
echo "<pre style='background: #1a202c; color: #a0aec0; padding: 20px; border-radius: 8px; overflow-x: auto;'>";

$success = 0;
$skipped = 0;
$failed = 0;

/**
 * Helper function to safely add a column if it doesn't exist
 */
function addColumnIfMissing($conn, $table, $column, $definition) {
    global $success, $skipped, $failed;
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE `$table` ADD `$column` $definition";
        if ($conn->query($sql)) {
            echo "<span style='color: #68d391;'>✅ ADDED:</span> Column '$column' to '$table'\n";
            $success++;
        } else {
            echo "<span style='color: #fc8181;'>❌ FAILED:</span> Adding '$column' - " . $conn->error . "\n";
            $failed++;
        }
    } else {
        echo "<span style='color: #cbd5e0;'>⏭️ SKIPPED:</span> Column '$column' already exists in '$table'\n";
        $skipped++;
    }
}

/**
 * Helper function to safely add an index if it doesn't exist
 */
function addIndexIfMissing($conn, $table, $index, $columns) {
    global $success, $skipped, $failed;
    $check = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$index'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE `$table` ADD INDEX `$index` ($columns)";
        if ($conn->query($sql)) {
            echo "<span style='color: #68d391;'>✅ INDEXED:</span> Added index '$index' to '$table'\n";
            $success++;
        } else {
            echo "<span style='color: #fc8181;'>❌ FAILED:</span> Adding index '$index' - " . $conn->error . "\n";
            $failed++;
        }
    } else {
        echo "<span style='color: #cbd5e0;'>⏭️ SKIPPED:</span> Index '$index' already exists in '$table'\n";
        $skipped++;
    }
}

/**
 * Helper function to safely drop a foreign key
 */
function dropForeignKey($conn, $table, $column) {
    global $success, $skipped, $failed;
    // This is tricky as FK name varies, but often it's table_column_fk or similar
    // We'll search the information_schema
    $sql = "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column' 
            AND CONSTRAINT_NAME <> 'PRIMARY' LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $fk = $res->fetch_assoc()['CONSTRAINT_NAME'];
        if ($conn->query("ALTER TABLE `$table` DROP FOREIGN KEY `$fk`")) {
            echo "<span style='color: #68d391;'>🔓 DROPPED:</span> Foreign key '$fk' from '$table'\n";
            $success++;
        }
    }
}

// 1. Check Tables
$tables = [
    'reviews' => "id INT AUTO_INCREMENT PRIMARY KEY, seller_id INT NOT NULL, reviewer_name VARCHAR(100) NOT NULL, reviewer_email VARCHAR(100), rating TINYINT(1) NOT NULL, comment TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    'subscriptions' => "id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, plan VARCHAR(20) NOT NULL, amount DECIMAL(10,2) NOT NULL, reference VARCHAR(50) UNIQUE NOT NULL, status ENUM('pending', 'success', 'failed') DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    'conversations' => "id INT AUTO_INCREMENT PRIMARY KEY, buyer_id INT NULL, seller_id INT NOT NULL, guest_token VARCHAR(64) NULL, guest_name VARCHAR(100) NULL, listing_id INT DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_buyer (buyer_id), INDEX idx_seller (seller_id), INDEX idx_guest (guest_token)",
    'messages' => "id INT AUTO_INCREMENT PRIMARY KEY, conversation_id INT NOT NULL, sender_id INT NOT NULL, message TEXT NOT NULL, is_read TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_conversation (conversation_id)",
    'verification_requests' => "user_id INT PRIMARY KEY, status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($tables as $name => $def) {
    $check = $conn->query("SHOW TABLES LIKE '$name'");
    if ($check->num_rows == 0) {
        if ($conn->query("CREATE TABLE $name ($def)")) {
            echo "<span style='color: #68d391;'>✅ CREATED:</span> Table '$name'\n";
            $success++;
        } else {
            echo "<span style='color: #fc8181;'>❌ FAILED:</span> Table '$name' - " . $conn->error . "\n";
            $failed++;
        }
    } else {
        echo "<span style='color: #cbd5e0;'>⏭️ SKIPPED:</span> Table '$name' already exists\n";
        $skipped++;
    }
}

// 2. Check User Columns
addColumnIfMissing($conn, 'users', 'last_active', "TIMESTAMP NULL");
addColumnIfMissing($conn, 'users', 'verification_level', "ENUM('basic', 'verified', 'trusted') DEFAULT 'basic'");
addColumnIfMissing($conn, 'users', 'phone_verified', "BOOLEAN DEFAULT FALSE");
addColumnIfMissing($conn, 'users', 'profile_photo', "VARCHAR(255) NULL");
addColumnIfMissing($conn, 'users', 'bio', "TEXT NULL");
addColumnIfMissing($conn, 'users', 'farm_name', "VARCHAR(100) NULL");
addColumnIfMissing($conn, 'users', 'farm_location', "VARCHAR(255) NULL");
addColumnIfMissing($conn, 'users', 'verification_requested_at', "TIMESTAMP NULL");
addColumnIfMissing($conn, 'users', 'plan', "VARCHAR(20) DEFAULT 'free'");
addColumnIfMissing($conn, 'users', 'plan_expires', "TIMESTAMP NULL");
addColumnIfMissing($conn, 'users', 'lat', "DECIMAL(10, 8) DEFAULT NULL");
addColumnIfMissing($conn, 'users', 'lng', "DECIMAL(11, 8) DEFAULT NULL");
addColumnIfMissing($conn, 'users', 'whatsapp', "VARCHAR(20) NULL");
addColumnIfMissing($conn, 'users', 'nin_number', "VARCHAR(20) NULL");
addColumnIfMissing($conn, 'users', 'nin_document', "VARCHAR(255) NULL");
addColumnIfMissing($conn, 'users', 'full_name', "VARCHAR(255) NULL");

// Extra for Guest Chat (Ensure buyer_id is nullable and columns exist)
$conn->query("ALTER TABLE conversations MODIFY buyer_id INT NULL");
addColumnIfMissing($conn, 'conversations', 'guest_token', "VARCHAR(64) NULL");
addColumnIfMissing($conn, 'conversations', 'guest_name', "VARCHAR(100) NULL");
addColumnIfMissing($conn, 'conversations', 'updated_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
addIndexIfMissing($conn, 'conversations', 'idx_guest', 'guest_token');

// Ensure Message columns exist
addColumnIfMissing($conn, 'messages', 'conversation_id', "INT NOT NULL");
addColumnIfMissing($conn, 'messages', 'sender_id', "INT NOT NULL");
addColumnIfMissing($conn, 'messages', 'message', "TEXT NOT NULL");
addColumnIfMissing($conn, 'messages', 'is_read', "TINYINT(1) DEFAULT 0");

// Relax constraints for guests
dropForeignKey($conn, 'messages', 'sender_id');
dropForeignKey($conn, 'conversations', 'buyer_id');
addIndexIfMissing($conn, 'messages', 'idx_conversation', 'conversation_id');

// 3. Check Listing Columns
addColumnIfMissing($conn, 'listings', 'price_type', "ENUM('fixed', 'negotiable', 'bulk') DEFAULT 'fixed'");
addColumnIfMissing($conn, 'listings', 'stock_quantity', "INT DEFAULT 0");
addColumnIfMissing($conn, 'listings', 'availability', "ENUM('ready_now', 'available_from') DEFAULT 'ready_now'");
addColumnIfMissing($conn, 'listings', 'availability_date', "DATE NULL");
addColumnIfMissing($conn, 'listings', 'views', "INT DEFAULT 0");

echo "\n";
echo "========================================\n";
echo "Migration Results:\n";
echo "----------------------------------------\n";
echo "Updated: $success | Skipped: $skipped | Failed: $failed\n";

echo "\n========================================\n";
echo "🔍 DEBUG: Current Users Table Structure:\n";
echo "----------------------------------------\n";
$desc = $conn->query("DESCRIBE users");
if ($desc) {
    while($row = $desc->fetch_assoc()) {
        echo str_pad($row['Field'], 25) . " | " . $row['Type'] . "\n";
    }
}
echo "========================================\n";
echo "</pre>";


if ($failed == 0) {
    echo "<div style='background: #c6f6d5; color: #22543d; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
    echo "🎉 <strong>All systems go!</strong> Your database is now fully up to date. You can now use all features including Chat, Maps, and Reviews.";
    echo "</div>";
} else {
    echo "<div style='background: #fed7d7; color: #822727; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
    echo "⚠️ <strong>Some updates failed.</strong> Please check the detailed log above for errors.";
    echo "</div>";
}

echo "<p style='margin-top: 30px; text-align: center;'><a href='index.php' style='color: #2e7d32; font-weight: bold; text-decoration: none;'>← Return to Homepage</a></p>";
echo "</div></body>";
?>
