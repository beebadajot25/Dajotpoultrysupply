<?php
include 'includes/db.php';

// New image path
$new_image = 'assets/images/products/layers.png';
$source_image = 'C:/Users/user/.gemini/antigravity/brain/c116d95a-8074-4b8f-9023-ba9435da6a1e/layers_chickens_1770204004365.png';

// Try to copy the image using PHP
if (file_exists($source_image)) {
    if (!is_dir('assets/images/products')) {
        mkdir('assets/images/products', 0777, true);
    }
    if (copy($source_image, $new_image)) {
        echo "<p style='color:green'>✅ Product image transferred successfully.</p>";
    } else {
        echo "<p style='color:orange'>⚠️ Could not copy image. Please manually save your generated 'layers' image to <code>assets/images/products/layers.png</code></p>";
    }
} else {
    echo "<p style='color:orange'>⚠️ Source image not found. Please manually save your generated 'layers' image to <code>assets/images/products/layers.png</code></p>";
}

// Update table
$sql = "UPDATE products SET 
        name = 'Point of Lay (Layers)', 
        price = 4500, 
        price_unit = 'bird', 
        description = 'Premium point of lay hens, healthy and ready for production.',
        image = '$new_image'
        WHERE name = 'Poultry Feed' OR name LIKE '%Feed%'";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "<h2>✅ Catalog Updated!</h2>";
        echo "<p>Poultry Feed has been replaced with <strong>Point of Lay (Layers)</strong>.</p>";
    } else {
        echo "<h2>ℹ️ No changes made.</h2>";
        echo "<p>Could not find 'Poultry Feed' in products table.</p>";
    }
} else {
    echo "<h2>❌ Error</h2>" . $conn->error;
}

echo "<hr><p><a href='index.php'>Go to Homepage</a></p>";
?>
