<?php
include 'includes/db.php';

$sql = "UPDATE subscription_plans SET listing_limit = 3 WHERE name = 'Free'";

if ($conn->query($sql) === TRUE) {
    echo "Success: Free plan limit updated to 3 listings.<br>";
} else {
    echo "Error updating record: " . $conn->error;
}
?>
