<?php
include 'includes/db.php';

// Truncate (Optional, but ensures no duplicates if partial data exists)
$conn->query("TRUNCATE TABLE products");
$conn->query("TRUNCATE TABLE listings");

// Insert Products
$sql_products = "INSERT INTO `products` (`name`, `description`, `price`, `price_unit`, `category`, `image`) VALUES
('Live Broilers', 'Healthy, well-fed broilers suitable for meat.', 3500.00, 'bird', 'Broilers', 'assets/images/products/broilers.png'),
('Crates of Eggs', 'Farm fresh crates of eggs.', 2800.00, 'crate', 'Eggs', 'assets/images/products/eggs.png'),
('Processed Chicken', 'Cleaned, dressed, and frozen chicken.', 4000.00, 'kg', 'Processed', 'assets/images/products/processed.png'),
('Poultry Feed', 'Quality feed for Starter, Grower, Finisher.', 9500.00, 'bag', 'Feed', 'assets/images/products/feed.png');";

if ($conn->query($sql_products) === TRUE) {
    echo "Products inserted successfully.<br>";
} else {
    echo "Error inserting products: " . $conn->error . "<br>";
}

// Insert Listings
$sql_listings = "INSERT INTO `listings` (`farmer_name`, `phone`, `whatsapp`, `location`, `product_name`, `price`, `price_unit`, `status`, `category`, `image`) VALUES
('Ibrahim Farms', '08012345678', '08012345678', 'Ibadan, Oyo', '500 Broilers Ready for Market', 3200.00, 'bird', 'approved', 'Broilers', 'assets/images/products/broilers.png'),
('AgroConnect', '08098765432', '08098765432', 'Abeokuta, Ogun', 'Point of Lay Birds', 4500.00, 'bird', 'approved', 'Layers', 'assets/images/products/layers.png'),
('Mama Chioma', '07055555555', '07055555555', 'Lagos', 'Crates of Eggs (Large)', 2700.00, 'crate', 'approved', 'Eggs', 'assets/images/products/eggs.png');";

if ($conn->query($sql_listings) === TRUE) {
    echo "Listings inserted successfully.<br>";
} else {
    echo "Error inserting listings: " . $conn->error . "<br>";
}
?>
