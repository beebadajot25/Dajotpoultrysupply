<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: vendor-login.php");
    exit;
}

require_once 'includes/db.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle Actions (Delete)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Security check: must belong to user
    $stmt = $conn->prepare("DELETE FROM listings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Listing deleted successfully.";
    } else {
        $_SESSION['flash_message'] = "Error deleting listing.";
    }
    header("Location: vendor-dashboard.php");
    exit;
}

// Fetch Listings for this user
$listings = [];
$sql = "SELECT * FROM listings WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
}

// Fetch Plan from Users Table
$user_q = $conn->query("SELECT plan FROM users WHERE id = $user_id");
$user_data = $user_q->fetch_assoc();
$plan = $user_data['plan'] ?? 'free';

$limits = ['free' => 3, 'pro' => 50, 'gold' => 99999];
$listing_limit = $limits[$plan];
$active_plan = strtoupper($plan);
$listings_count = count($listings);

$limit_reached = ($listings_count >= $listing_limit && $listing_limit != 0); 
if ($listing_limit > 1000) $limit_reached = false;

// Handle Alert Deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete_alert' && isset($_GET['alert_id'])) {
    $alert_id = intval($_GET['alert_id']);
    // Security: alert must belong to a listing owned by the user
    $sql = "DELETE pa FROM price_alerts pa 
            JOIN listings l ON pa.listing_id = l.id 
            WHERE pa.id = ? AND l.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $alert_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Alert removed.";
    }
    header("Location: vendor-dashboard.php");
    exit;
}

// Fetch Alerts for this vendor
$alerts = [];
$alert_sql = "SELECT pa.*, l.product_name 
              FROM price_alerts pa 
              JOIN listings l ON pa.listing_id = l.id 
              WHERE l.user_id = '$user_id' 
              ORDER BY pa.created_at DESC";
$alert_res = $conn->query($alert_sql);
if ($alert_res) {
    while($row = $alert_res->fetch_assoc()) {
        $alerts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - Dajot Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .listing-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .listing-info { flex: 1; }
        .listing-status { font-size: 0.85em; padding: 4px 8px; border-radius: 4px; font-weight: 600; display: inline-block; margin-top: 5px; }
        .status-pending { background: #FEFCBF; color: #744210; }
        .status-approved { background: #C6F6D5; color: #22543D; }
        .status-rejected { background: #FED7D7; color: #822727; }
        .listing-img { width: 80px; height: 80px; border-radius: 6px; object-fit: cover; background: #eee; }
        .listing-actions { display: flex; gap: 10px; }
    </style>
</head>
<body style="background: #f7fafc;">

<!-- Simple Header -->
<header style="background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 15px 0;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="index.php" style="font-weight: bold; font-size: 1.2em; color: var(--primary-green); text-decoration:none;">Dajot Marketplace</a>
        <div>
            <span style="margin-right: 15px;">Hi, <?php echo htmlspecialchars($username); ?></span>
            <a href="vendor-verification.php" class="btn btn-sm" style="background: #2e7d32; color: white; margin-right: 10px;">Get Verified</a>
            <a href="vendor-settings.php" class="btn btn-outline btn-sm" style="margin-right: 10px;">Settings</a>
            <a href="includes/logout.php" class="btn btn-outline btn-sm">Logout</a>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'upgrade_success'): ?>
        <div style="background: #f0fff4; color: #276749; padding: 15px; border-radius: 10px; border-left: 5px solid #48bb78; margin-bottom: 25px;">
            <i class="fas fa-check-circle"></i> <strong>Congratulations!</strong> Your account has been upgraded successfully.
        </div>
    <?php endif; ?>
    <!-- Buyer Interest Alerts -->
    <?php if (!empty($alerts)): ?>
        <div style="margin-bottom: 40px;">
            <h2 style="margin-bottom: 20px; color: #2d3748;"><i class="fas fa-bell" style="color: #e53e3e;"></i> Buyer Interest Alerts</h2>
            <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                <?php foreach ($alerts as $alert): ?>
                    <div style="padding: 15px 20px; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <p style="margin: 0; font-weight: 600; color: #2d3748;">
                                Interested in: <span style="color: #2e7d32;"><?php echo htmlspecialchars($alert['product_name']); ?></span>
                            </p>
                            <p style="margin: 5px 0 0; color: #4a5568; font-size: 0.9em;">
                                <i class="fas fa-user-tag"></i> Buyer: <strong><?php echo htmlspecialchars($alert['contact_info']); ?></strong>
                                <span style="margin-left: 15px;"><i class="fas fa-tags"></i> Target: <strong>₦<?php echo number_format($alert['target_price']); ?></strong></span>
                            </p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $alert['contact_info']); ?>" target="_blank" class="btn btn-sm" style="background: #25D366; color: white; border: none;"><i class="fab fa-whatsapp"></i> Chat</a>
                            <a href="vendor-dashboard.php?action=delete_alert&alert_id=<?php echo $alert['id']; ?>" class="btn btn-sm btn-outline" style="color: #e53e3e; border-color: #e53e3e;" onclick="return confirm('Dismiss this alert?')"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="dashboard-header" style="flex-wrap: wrap; gap: 10px;">
        <h1 style="margin: 0;">My Listings <span style="font-size: 0.5em; vertical-align: middle; background: #e2e8f0; padding: 4px 8px; border-radius: 4px; color: #4a5568;">Plan: <?php echo $active_plan; ?></span></h1>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div style="width: 100%; background: #c6f6d5; color: #22543d; padding: 10px; border-radius: 4px; margin-top: 10px;">
                <?php echo e($_SESSION['flash_message']); unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>


        <?php if ($limit_reached): ?>
            <a href="pricing.php" class="btn btn-secondary" style="background: #e53e3e; color: white;">Upgrade to Add More Listings</a>
        <?php else: ?>
            <a href="vendor-add-listing.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Listing</a>
        <?php endif; ?>
    </div>


    <?php if (empty($listings)): ?>
        <div style="text-align: center; padding: 50px; background: #fff; border-radius: 8px;">
            <p style="color: #666; margin-bottom: 20px;">You haven't listed any products yet.</p>
            <a href="vendor-add-listing.php" class="btn btn-primary">Create First Listing</a>
        </div>
    <?php else: ?>
        <?php foreach ($listings as $item): ?>
            <div class="listing-card">
                <img src="<?php echo !empty($item['image']) ? $item['image'] : 'assets/images/logo.png'; ?>" class="listing-img">
                <div class="listing-info">
                    <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 5px;">₦<?php echo number_format($item['price']); ?> • <?php echo e($item['category']); ?></p>
                    <span class="listing-status status-<?php echo $item['status']; ?>">
                        <?php echo ucfirst($item['status']); ?>
                    </span>
                </div>
                <div class="listing-actions">
                    <a href="product-details.php?id=<?php echo $item['id']; ?>&mode=review" class="btn btn-sm" style="background: var(--primary-green); color: white;"><i class="fas fa-eye"></i> Review</a>
                    <a href="vendor-edit-listing.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i> Edit</a>
                    <a href="vendor-dashboard.php?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline" style="color: #e53e3e; border-color: #e53e3e;" onclick="return confirm('Are you sure you want to delete this listing?')"><i class="fas fa-trash"></i> Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

