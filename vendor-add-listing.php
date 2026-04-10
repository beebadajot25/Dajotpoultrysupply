<?php
session_start();
// Ensure User is Logged In - MUST BE FIRST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: vendor-login.php");
    exit;
}

include 'includes/db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Product Categories
$categories = [
    'Live Birds' => ['Broilers', 'Layers', 'Turkeys', 'Local Chickens', 'Cockerels'],
    'Eggs' => ['Table Eggs', 'Hatchery Eggs', 'Fertilized Eggs'],
    'Chicks' => ['Day-old Chicks', 'Week-old Chicks'],
    'Feed & Equipment' => ['Starter Feed', 'Grower Feed', 'Finisher Feed', 'Feeders', 'Drinkers'],
    'Processed' => ['Frozen Chicken', 'Dressed Chicken', 'Chicken Parts']
];

// CHECK SUBSCRIPTION LIMITS
$user_q = $conn->query("SELECT plan FROM users WHERE id = $user_id");
$user_data = $user_q->fetch_assoc();
$plan = $user_data['plan'] ?? 'free';

$limits = ['free' => 3, 'pro' => 50, 'gold' => 99999];
$max_allowed = $limits[$plan];

// Count current listings for this user
$count_q = $conn->query("SELECT COUNT(*) as total FROM listings WHERE user_id = $user_id");
if ($count_q) {
    $current_count = $count_q->fetch_assoc()['total'];
} else {
    $current_count = 0;
}

if ($current_count >= $max_allowed) {
    header("Location: pricing.php?msg=limit_reached");
    exit;
}

$active_plan = strtoupper($plan);
$current_listings = $current_count;
$listing_limit = $max_allowed;

$message = "";

// Header comes AFTER redirects are ruled out
include 'includes/header.php';

// ==========================================
// HANDLE FORM SUBMISSION
// ==========================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh and try again.";
    } else {
        $type = 'marketplace';
        $product_type = $conn->real_escape_string($_POST['product_type']);
        $price = $conn->real_escape_string($_POST['price']);
    
    // New fields
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = intval($_POST['quantity']);
    $unit = $conn->real_escape_string($_POST['unit']);
    $price_type = $conn->real_escape_string($_POST['price_type']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $availability = $conn->real_escape_string($_POST['availability']);
    $availability_date = !empty($_POST['availability_date']) ? $conn->real_escape_string($_POST['availability_date']) : null;
    
    $product_name = "$product_type from $username"; 
    $status = 'pending'; 

    $phone = $conn->real_escape_string($_POST['phone']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $location = $conn->real_escape_string($_POST['location']);

    $image_path = "";
    $target_dir = "assets/images/uploads/";
    
    if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $target_file = $target_dir . uniqid() . "_" . basename($_FILES["product_image"]["name"]);
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Build SQL with new fields
    $sql = "INSERT INTO listings (user_id, farmer_name, phone, whatsapp, location, product_name, price, category, status, image, type, description, quantity, unit, price_type, stock_quantity, availability, availability_date) 
            VALUES ('$user_id', '$username', '$phone', '$whatsapp', '$location', '$product_name', '$price', '$product_type', '$status', '$image_path', '$type', '$description', '$quantity', '$unit', '$price_type', '$stock_quantity', '$availability', " . ($availability_date ? "'$availability_date'" : "NULL") . ")";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['flash_message'] = "Success! Product listed successfully. It will appear after admin approval.";
        header("Location: vendor-dashboard.php");
        exit;
    } else {
        $message = "Error: " . $conn->error;
    }
}
}
?>

<style>
.form-card {
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    border: 1px solid #eee;
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
}
.form-section {
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid #f0f0f0;
}
.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.form-section-title {
    font-size: 1.1em;
    font-weight: 600;
    color: #2e7d32;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.form-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.form-row .form-group {
    flex: 1;
    min-width: 200px;
}
.price-type-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.price-type-option {
    flex: 1;
    min-width: 120px;
}
.price-type-option input[type="radio"] {
    display: none;
}
.price-type-option label {
    display: block;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
}
.price-type-option input:checked + label {
    border-color: #2e7d32;
    background: #e8f5e9;
    color: #2e7d32;
}
.availability-note {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 10px 15px;
    border-radius: 0 8px 8px 0;
    font-size: 0.9em;
    color: #856404;
    margin-top: 10px;
}
</style>

<div class="page-header text-center" style="background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);">
    <div class="container">
        <h1>Add New Product</h1>
        <p>Plan: <?php echo $active_plan; ?> (<?php echo $current_listings; ?>/<?php echo ($listing_limit > 1000) ? 'Unlimited' : $listing_limit; ?> Used)</p>
    </div>
</div>

<section class="section" style="padding-top: 40px;">
    <div class="container">
        
        <?php if (!empty($message)) { ?>
            <div style="background: #ffebee; color: #c62828; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center; border-left: 4px solid #c62828;">
                <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>
            </div>
        <?php } ?>

        <div style="max-width:750px; margin:0 auto;">
            <div class="form-card">
                
                <form action="vendor-add-listing.php" method="POST" enctype="multipart/form-data">
                    <?php csrf_input(); ?>
                    
                    <!-- Product Details Section -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-box"></i> Product Details</h3>
                        
                        <div class="form-group">
                            <label>Product Category <span style="color: red;">*</span></label>
                            <select name="product_type" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $group => $items): ?>
                                    <optgroup label="<?php echo $group; ?>">
                                        <?php foreach ($items as $cat): ?>
                                            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe your product: breed, age of birds, feed brand, etc."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Product Image <span style="color: red;">*</span></label>
                            <input type="file" name="product_image" class="form-control" accept="image/*" required>
                            <small style="color: #666;">Upload a clear photo of your product (max 5MB)</small>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-tags"></i> Pricing & Stock</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Price (₦) <span style="color: red;">*</span></label>
                                <input type="number" name="price" class="form-control" placeholder="e.g. 5000" required>
                            </div>
                            <div class="form-group">
                                <label>Unit</label>
                                <select name="unit" class="form-control">
                                    <option value="bird">Per Bird</option>
                                    <option value="crate">Per Crate</option>
                                    <option value="bag">Per Bag</option>
                                    <option value="kg">Per KG</option>
                                    <option value="piece">Per Piece</option>
                                    <option value="unit">Per Unit</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Price Type</label>
                            <div class="price-type-options">
                                <div class="price-type-option">
                                    <input type="radio" name="price_type" value="fixed" id="price_fixed" checked>
                                    <label for="price_fixed"><i class="fas fa-lock"></i> Fixed Price</label>
                                </div>
                                <div class="price-type-option">
                                    <input type="radio" name="price_type" value="negotiable" id="price_negotiable">
                                    <label for="price_negotiable"><i class="fas fa-handshake"></i> Negotiable</label>
                                </div>
                                <div class="price-type-option">
                                    <input type="radio" name="price_type" value="bulk" id="price_bulk">
                                    <label for="price_bulk"><i class="fas fa-cubes"></i> Bulk Discount</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Stock Quantity <span style="color: red;">*</span></label>
                                <input type="number" name="stock_quantity" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="form-group">
                                <label>Quantity Available</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1">
                            </div>
                        </div>
                    </div>

                    <!-- Availability Section -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-calendar-check"></i> Availability</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Availability Status</label>
                                <select name="availability" class="form-control" id="availability_select" onchange="toggleDateField()">
                                    <option value="ready_now">🟢 Ready for Pickup Now</option>
                                    <option value="available_from">📅 Available from Date</option>
                                </select>
                            </div>
                            <div class="form-group" id="date_field" style="display: none;">
                                <label>Available From</label>
                                <input type="date" name="availability_date" class="form-control">
                            </div>
                        </div>
                        
                        <div class="availability-note">
                            <i class="fas fa-info-circle"></i> Products marked "Ready Now" appear higher in search results.
                        </div>
                    </div>

                    <!-- Contact Details Section -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-phone-alt"></i> Contact Details</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone Number <span style="color: red;">*</span></label>
                                <input type="text" name="phone" class="form-control" placeholder="08012345678" required>
                            </div>
                            <div class="form-group">
                                <label>WhatsApp Number</label>
                                <input type="text" name="whatsapp" class="form-control" placeholder="Same as phone if empty">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Location <span style="color: red;">*</span></label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Jos, Plateau State" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="padding: 15px; font-size: 1.1em;">
                        <i class="fas fa-paper-plane"></i> Submit Listing for Approval
                    </button>
                    <a href="vendor-dashboard.php" class="btn btn-outline btn-block" style="text-align:center; display:block; margin-top:12px;">
                        Cancel
                    </a>
                </form>
            </div>
        </div>

    </div>
</section>

<script>
function toggleDateField() {
    var select = document.getElementById('availability_select');
    var dateField = document.getElementById('date_field');
    if (select.value === 'available_from') {
        dateField.style.display = 'block';
    } else {
        dateField.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
