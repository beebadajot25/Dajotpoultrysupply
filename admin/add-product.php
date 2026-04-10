<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit; }

require_once '../includes/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        $name = $_POST['name'];
    $category = $_POST['category'];
    $price = floatval($_POST['price']);
    $price_unit = $_POST['price_unit'];
    $description = $_POST['description'];
    $stock = intval($_POST['stock']);
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../assets/images/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'assets/images/products/' . $new_filename;
            }
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO products (name, category, price, price_unit, description, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssss", $name, $category, $price, $price_unit, $description, $stock, $image_path);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?view=products&success=1");
        exit;
    } else {
        $error = "Failed to add product.";
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Dajot Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: #f5f7fa;">

<div style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    <div style="margin-bottom: 20px;">
        <a href="dashboard.php?view=products" style="color: #2e7d32; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    
    <div class="main-content">
        <header style="margin-bottom: 30px;">
            <h1><i class="fas fa-plus"></i> Add New Product</h1>
        </header>

        <?php if (isset($error)): ?>
            <div style="background: #fee; color: #c00; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <div style="background: white; padding: 30px; border-radius: 8px; max-width: 700px;">
            <form method="POST" enctype="multipart/form-data">
                <?php csrf_input(); ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Product Name *</label>
                    <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Category *</label>
                    <select name="category" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Choose category...</option>
                        <option value="Layers">Layers</option>
                        <option value="Broilers">Broilers</option>
                        <option value="Day-Old Chicks">Day-Old Chicks</option>
                        <option value="Eggs">Eggs</option>
                        <option value="Feeds">Feeds</option>
                        <option value="Equipment">Equipment</option>
                        <option value="Medicine">Medicine</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Price (₦) *</label>
                        <input type="number" name="price" min="0" step="0.01" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Unit *</label>
                        <select name="price_unit" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="bird">per bird</option>
                            <option value="crate">per crate</option>
                            <option value="kg">per kg</option>
                            <option value="bag">per bag</option>
                            <option value="piece">per piece</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Stock Quantity *</label>
                    <input type="number" name="stock" min="0" required value="0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #718096;">Number of units available</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Description</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Product Image</label>
                    <input type="file" name="image" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #718096;">Recommended: 500x500px, JPG/PNG/WEBP</small>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add Product</button>
                    <a href="dashboard.php?view=products" class="btn" style="background: #718096; color: white;"><i class="fas fa-arrow-left"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
