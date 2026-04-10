<?php
include 'includes/db.php';

echo "<h1>Debug Info</h1>";

// Check Products
echo "<h2>Products Table</h2>";
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - Name: " . $row["name"]. " - Image: " . $row["image"] . "<br>";
        if (!empty($row["image"])) {
            if (file_exists($row["image"])) {
                echo "<span style='color:green'>Image file exists.</span><br>";
            } else {
                 echo "<span style='color:red'>Image file NOT found: " . getcwd() . '/' . $row["image"] . "</span><br>";
            }
        }
    }
} else {
    echo "0 results in products table";
}

// Check Listings
echo "<h2>Listings Table</h2>";
$sql = "SELECT * FROM listings";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
         echo "ID: " . $row["id"]. " - Name: " . $row["product_name"]. " - Image: " . $row["image"] . " - Status: " . $row['status'] . "<br>";
    }
} else {
    echo "0 results in listings table";
}

// Check Directory
echo "<h2>Assets Directory</h2>";
$dir = "assets/images/products/";
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            echo "filename: $file : filetype: " . filetype($dir . $file) . "<br>";
        }
        closedir($dh);
    }
} else {
    echo "Directory not found: $dir";
}
?>
