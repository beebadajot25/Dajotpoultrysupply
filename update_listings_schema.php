<?php
include 'includes/db.php';

// Add new columns to listings table
$columns = [
    "description TEXT",
    "quantity INT(11) DEFAULT 1",
    "unit VARCHAR(20) DEFAULT 'unit'" // e.g., bird, crate, bag
];

foreach ($columns as $col) {
    $col_name = explode(' ', $col)[0];
    $check = $conn->query("SHOW COLUMNS FROM listings LIKE '$col_name'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE listings ADD COLUMN $col";
        if ($conn->query($sql) === TRUE) {
            echo "Added column: $col<br>";
        } else {
            echo "Error adding $col: " . $conn->error . "<br>";
        }
    } else {
        echo "Column $col_name already exists.<br>";
    }
}

echo "Schema Update Complete.";
?>
