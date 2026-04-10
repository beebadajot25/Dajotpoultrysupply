<?php
include 'includes/db.php';

// Columns to add to products table
$cols = [
    "quantity INT(11) DEFAULT 100",
    "description TEXT",
    "category VARCHAR(100)"
];

foreach ($cols as $col) {
    $parts = explode(' ', $col);
    $col_name = $parts[0];
    
    // Check if exists
    $check = $conn->query("SHOW COLUMNS FROM products LIKE '$col_name'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE products ADD $col");
        echo "<p>Added $col_name to products table.</p>";
    }
}

echo "<p>Done.</p>";
?>
