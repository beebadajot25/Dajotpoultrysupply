<?php
include 'includes/db.php';
$res = $conn->query("SHOW TABLES LIKE 'price_alerts'");
if ($res->num_rows > 0) {
    echo "Table price_alerts exists.\n";
    $res = $conn->query("DESCRIBE price_alerts");
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Table price_alerts DOES NOT exist.\n";
}
?>
