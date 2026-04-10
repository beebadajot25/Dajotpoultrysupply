<?php 
require_once 'includes/security.php';
include 'includes/header.php'; 
?>
<?php 
include 'includes/db.php'; 

$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh and try again.";
    } else {
        $type = $_POST['form_type']; // 'bulk' or 'marketplace'
    $name = $conn->real_escape_string($_POST['name']);
    $farm = $conn->real_escape_string($_POST['farm_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $location = $conn->real_escape_string($_POST['location']);
    $product_type = $conn->real_escape_string($_POST['product_type']);
    $quantity = $conn->real_escape_string($_POST['quantity']);
    $price = $conn->real_escape_string($_POST['price']);
    
    // Construct product name if not provided (bulk offers usually don't have a flashy name)
    $product_name = "$product_type from $name"; 
    
    // Status Logic
    $status = 'approved'; 

    $image_path = "";
    
    // Only handle image upload for Marketplace or if user uploaded one for bulk
    $target_dir = "assets/images/uploads/";
    if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        
        if($check !== false) {
             $new_filename = uniqid() . "." . $imageFileType;
             $target_file = $target_dir . $new_filename;
             if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                 $image_path = $target_file;
             }
        }
    }

    $sql = "INSERT INTO listings (farmer_name, phone, whatsapp, location, product_name, price, category, status, image, type) 
            VALUES ('$name', '$phone', '$whatsapp', '$location', '$product_name', '$price', '$product_type', '$status', '$image_path', '$type')";
    
    if ($conn->query($sql) === TRUE) {
        if ($type == 'marketplace') {
            $message = "Success! Your listing is now live on the <a href='marketplace.php'>Marketplace</a>.";
        } else {
             $message = "Offer received! We will contact you regarding your bulk supply.";
        }
    } else {
        $message = "Error: " . $conn->error;
    }
    }
}
?>

<div class="page-header text-center">
    <div class="container">
        <h1>Sell to Dajot</h1>
        <p>Supply poultry products directly to us in bulk</p>
    </div>
</div>

<section class="section" style="padding-top: 40px;">
    <div class="container">
        
        <?php if (!empty($message)) { ?>
             <div style="background: <?php echo strpos($message, 'Error') !== false ? '#ffebee' : '#e8f5e9'; ?>; 
                        color: <?php echo strpos($message, 'Error') !== false ? '#c62828' : '#2e7d32'; ?>; 
                        padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center; border: 1px solid #ddd;">
                <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i> <?php echo e($message); ?>
            </div>
        <?php } ?>

        <!-- BULK FORM -->
        <div style="max-width:700px; margin:0 auto;">
            <div style="background:#fff; padding:40px; border-radius:10px; border:1px solid #eee; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                <h2 class="section-title" style="text-align:left; margin-bottom:5px;">Bulk Offer Form</h2>
                <p style="color:#666; margin-bottom:30px;">Submit your bulk supply availability to Dajot.</p>
                
                <form action="sell-to-us.php" method="POST" enctype="multipart/form-data">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="form_type" value="bulk">
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                        <div class="form-group">
                        <label>Farm Name</label>
                        <input type="text" name="farm_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                        <div class="form-group">
                        <label>WhatsApp Number</label>
                        <input type="text" name="whatsapp" class="form-control">
                    </div>
                        <div class="form-group">
                        <label>Location (State/LGA)</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                    
                    <hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
                    
                    <div class="form-group">
                        <label>Product Type</label>
                            <select name="product_type" class="form-control">
                            <option>Broilers</option>
                            <option>Layers</option>
                            <option>Eggs</option>
                            <option>Feed</option>
                        </select>
                    </div>
                        <div class="form-group">
                        <label>Quantity Available (Bulk Only)</label>
                        <input type="number" name="quantity" class="form-control" placeholder="e.g. 500" required>
                    </div>
                        <div class="form-group">
                        <label>Asking Price (Total or Per Unit)</label>
                        <input type="text" name="price" class="form-control" placeholder="e.g. 1500 per bird" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Submit Bulk Offer</button>
                    <p style="margin-top:15px; font-size:0.9em; text-align:center;">
                        Want to list individual items instead? <a href="vendor-register.php" class="text-green">Sell on Marketplace</a>
                    </p>
                </form>
            </div>

            <!-- ALTERNATIVE CONTACT -->
            <div style="margin-top:40px; text-align:center;">
                <p style="color: #718096; margin-bottom: 20px;">&mdash; OR SEND US A DIRECT MESSAGE &mdash;</p>
                <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                    <a href="https://wa.me/2349034670525?text=Hello%20Dajot,%20I%20have%20poultry%20products%20available%20for%20bulk%20supply." 
                       class="btn btn-outline" 
                       style="color: #25D366; border-color: #25D366; display:flex; align-items:center; gap:8px;">
                        <i class="fab fa-whatsapp" style="font-size: 1.2em;"></i> WhatsApp Us
                    </a>
                    <a href="tel:+2349034670525" 
                       class="btn btn-outline" 
                       style="color: #2e7d32; border-color: #2e7d32; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-phone-alt"></i> Call Support
                    </a>
                </div>
                <p style="margin-top:20px; font-size:0.85em; color:#888;">
                    Available Monday - Saturday: 8 AM - 6 PM
                </p>
            </div>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
