<?php
include 'includes/db.php';
require_once 'includes/security.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'Security validation failed. Please refresh.']);
        exit;
    }
    $contact = $conn->real_escape_string($_POST['contact'] ?? '');
    $listing_id = intval($_POST['listing_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    if (empty($contact) || $listing_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid contact info or listing ID']);
        exit;
    }

    // Create table if not exists (Lazy migration)
    $create_sql = "CREATE TABLE IF NOT EXISTS price_alerts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        listing_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_sql)) {
        echo json_encode(['status' => 'error', 'message' => 'Database initialization failed: ' . $conn->error]);
        exit;
    }

    // Check and add missing columns (Schema update)
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM price_alerts");
    while($row = $res->fetch_assoc()) $cols[] = $row['Field'];

    if (!in_array('contact_info', $cols)) {
        $conn->query("ALTER TABLE price_alerts ADD COLUMN contact_info VARCHAR(255) NOT NULL AFTER listing_id");
    }
    if (!in_array('target_price', $cols)) {
        $conn->query("ALTER TABLE price_alerts ADD COLUMN target_price DECIMAL(10,2) NOT NULL AFTER contact_info");
    }

    $sql = "INSERT INTO price_alerts (listing_id, contact_info, target_price) VALUES ($listing_id, '$contact', $price)";
    
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $conn->error]);
    }
}
?>
