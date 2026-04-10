<?php
include 'includes/db.php';

function executeQuery($conn, $sql, $message) {
    if ($conn->query($sql) === TRUE) {
        echo "[SUCCESS] " . $message . "\n";
    } else {
        // If error contains "Duplicate column name", it's fine, already exists.
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "[INFO] " . $message . " (Already exists)\n";
        } else {
            echo "[ERROR] " . $message . ": " . $conn->error . "\n";
        }
    }
}

echo "Starting Database Migration...\n";

// 1. Update users table role
// Note: We use MODIFY to change the definition if it exists
$sql = "ALTER TABLE `users` MODIFY `role` ENUM('admin','vendor','farmer') DEFAULT 'vendor'";
executeQuery($conn, $sql, "Updated 'users' table role column");

// 2. Add subscription_plan to users
$sql = "ALTER TABLE `users` ADD COLUMN `subscription_plan` ENUM('free','premium') DEFAULT 'free'";
executeQuery($conn, $sql, "Added 'subscription_plan' to 'users' table");

// 3. Add vendor_id to listings
$sql = "ALTER TABLE `listings` ADD COLUMN `vendor_id` INT(11) AFTER `id`";
executeQuery($conn, $sql, "Added 'vendor_id' to 'listings' table");

// 4. Add quantity to listings
$sql = "ALTER TABLE `listings` ADD COLUMN `quantity` VARCHAR(50) AFTER `price`";
executeQuery($conn, $sql, "Added 'quantity' to 'listings' table");

echo "Migration Completed.\n";
?>
