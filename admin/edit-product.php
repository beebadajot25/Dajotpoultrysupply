<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit; }

require_once '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: dashboard.php?view=products&error=notfound");
    exit;
}

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
    $image_path = $product['image']; // Keep existing image by default
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
                // Delete old image if it exists
                if (!empty($product['image']) && file_exists('../' . $product['image'])) {
                    unlink('../' . $product['image']);
                }
                $image_path = 'assets/images/products/' . $new_filename;
            }
        }
    }
    
    // Update database
    $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, price_unit = ?, description = ?, stock = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssdssssi", $name, $category, $price, $price_unit, $description, $stock, $image_path, $id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?view=products&success=updated");
        exit;
    } else {
        $error = "Failed to update product.";
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Dajot Admin</title>
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
            <h1><i class="fas fa-edit"></i> Edit Product</h1>
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
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Category *</label>
                    <select name="category" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Choose category...</option>
                        <?php
                        $categories = ['Layers', 'Broilers', 'Day-Old Chicks', 'Eggs', 'Feeds', 'Equipment', 'Medicine'];
                        foreach ($categories as $cat) {
                            $selected = ($product['category'] == $cat) ? 'selected' : '';
                            echo "<option value='$cat' $selected>$cat</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Price (₦) *</label>
                        <input type="number" name="price" min="0" step="0.01" required value="<?php echo $product['price']; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Unit *</label>
                        <select name="price_unit" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <?php
                            $units = ['bird', 'crate', 'kg', 'bag', 'piece'];
                            foreach ($units as $unit) {
                                $selected = ($product['price_unit'] == $unit) ? 'selected' : '';
                                echo "<option value='$unit' $selected>per $unit</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Stock Quantity *</label>
                    <input type="number" name="stock" min="0" required value="<?php echo $product['stock']; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #718096;">Number of units available</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Description</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Product Image</label>
                    <?php if (!empty($product['image'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="../<?php echo $product['image']; ?>" alt="Current image" style="max-width: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <p style="font-size: 0.9em; color: #718096;">Current image (upload a new one to replace)</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #718096;">Recommended: 500x500px, JPG/PNG/WEBP</small>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Product</button>
                    <a href="dashboard.php?view=products" class="btn" style="background: #718096; color: white;"><i class="fas fa-arrow-left"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
