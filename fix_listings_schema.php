<?php
include 'includes/db.php';

echo "<h2>Fixing Listings Table Schema</h2>";

$cols = [
    "user_id INT(11)" => "user_id",
    "description TEXT" => "description",
    "quantity INT(11) DEFAULT 1" => "quantity",
    "unit VARCHAR(50) DEFAULT 'unit'" => "unit"
];

foreach ($cols as $sql_part => $col) {
    $check = $conn->query("SHOW COLUMNS FROM listings LIKE '$col'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE listings ADD $sql_part");
        echo "<p>Added $col to listings table.</p>";
    }
}

echo "<p>Done.</p>";
?>
