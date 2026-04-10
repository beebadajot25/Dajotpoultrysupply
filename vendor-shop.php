<?php
require_once 'includes/security.php';
session_start();
include 'includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: marketplace.php");
    exit;
}

$vendor_id = intval($_GET['id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);
    
    // Check if Guest Name is provided if not logged in
    $reviewer_name = $user_id ? $_SESSION['username'] : $conn->real_escape_string($_POST['guest_name'] ?? '');
    $reviewer_email = $user_id ? ($_SESSION['email'] ?? '') : $conn->real_escape_string($_POST['guest_email'] ?? '');

    if (empty($reviewer_name) && !$user_id) {
        $error = "Please provide your name to leave a review.";
    } elseif ($user_id == $vendor_id) {
        $error = "You cannot review your own shop.";
    } else {
        $sql = "INSERT INTO reviews (seller_id, reviewer_name, reviewer_email, rating, comment) 
                VALUES ($vendor_id, '$reviewer_name', '$reviewer_email', $rating, '$comment')";
        
        if ($conn->query($sql)) {
            $success = "Review submitted successfully!";
        } else {
            $error = "Error submitting review: " . $conn->error;
        }
    }
    }
}

// 1. Fetch Vendor Details
$v_sql = "SELECT * FROM users WHERE id = $vendor_id AND role = 'farmer'";
$v_res = $conn->query($v_sql);

if (!$v_res || $v_res->num_rows == 0) {
    echo "<h2>Vendor not found.</h2><a href='marketplace.php'>Back to Marketplace</a>";
    exit;
}

$v = $v_res->fetch_assoc();
$member_since = date('M Y', strtotime($v['created_at']));
$plan = strtoupper($v['plan'] ?? 'free');
$verification = $v['verification_level'] ?? 'basic';

// 2. Fetch Vendor's Listings
$l_sql = "SELECT * FROM listings WHERE user_id = $vendor_id AND status = 'approved' ORDER BY created_at DESC";
$l_res = $conn->query($l_sql);

// 3. Fetch Reviews
$r_sql = "SELECT * FROM reviews WHERE seller_id = $vendor_id ORDER BY created_at DESC";
$r_res = $conn->query($r_sql);
$avg_rating = 0;
$review_count = 0;

if ($r_res && $r_res->num_rows > 0) {
    $total_stars = 0;
    $review_count = $r_res->num_rows;
    while($r = $r_res->fetch_assoc()) {
        $reviews[] = $r;
        $total_stars += $r['rating'];
    }
    $avg_rating = round($total_stars / $review_count, 1);
    // Reset pointer for display loop
    $r_res->data_seek(0); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $v['username']; ?>'s Shop - Dajot Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .shop-header { background: #fff; border-bottom: 1px solid #eee; padding: 40px 0; margin-bottom: 40px; position:relative; overflow:hidden; }
        .shop-header::before { content:''; position:absolute; top:0; left:0; width:100%; height:5px; background: linear-gradient(90deg, #2e7d32, #48bb78); }
        
        .vendor-profile { display: flex; align-items: center; gap: 30px; }
        .vendor-avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .vendor-avatar-default { width: 100px; height: 100px; background: #2e7d32; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3em; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8em; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .badge-verified { background: #4299e1; color: white; }
        .badge-trusted { background: #2e7d32; color: white; }
        
        .shop-container { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }
        .contact-card { background: #fff; padding: 25px; border-radius: 15px; border: 1px solid #eee; position: sticky; top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        
        .rating-stars { color: #ecc94b; }
        .review-card { background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #f0f0f0; }
        .review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        
        .tab-btn { background: none; border: none; padding: 10px 20px; cursor: pointer; font-weight: 600; color: #718096; border-bottom: 2px solid transparent; }
        .tab-btn.active { color: #2e7d32; border-bottom-color: #2e7d32; }
        
        @media (max-width: 900px) {
            .shop-container { grid-template-columns: 1fr; }
            .vendor-profile { flex-direction: column; text-align: center; }
            .contact-card { position: static; }
        }
    </style>
</head>
<body style="background: #f8fafc;">

<?php include 'includes/header.php'; ?>

<div class="shop-header">
    <div class="container">
        <a href="marketplace.php" style="color: #2e7d32; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to Marketplace
        </a>
        <div class="vendor-profile">
            <?php if(!empty($v['profile_photo'])): ?>
                <img src="<?php echo $v['profile_photo']; ?>" class="vendor-avatar">
            <?php else: ?>
                <div class="vendor-avatar-default"><?php echo strtoupper(substr($v['username'], 0, 1)); ?></div>
            <?php endif; ?>
            
            <div>
                <h1 style="margin:0 0 10px; font-size: 2.5em; display: flex; align-items: center; gap: 15px; flex-wrap:wrap; justify-content:center;">
                    <?php echo isset($v['farm_name']) && $v['farm_name'] ? $v['farm_name'] : $v['username']; ?>
                    
                    <?php if($verification == 'trusted'): ?>
                        <span class="badge badge-trusted" title="Trusted & Identity Verified"><i class="fas fa-shield-check"></i> TRUSTED SELLER</span>
                    <?php elseif($verification == 'verified'): ?>
                        <span class="badge badge-verified" style="background: #2e7d32;" title="Government ID Verified"><i class="fas fa-id-card"></i> IDENTITY VERIFIED</span>
                    <?php endif; ?>
                    
                    <button onclick="shareShop()" class="btn btn-outline btn-sm" style="font-size: 0.4em; padding: 5px 10px; border-radius: 20px;">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                </h1>
                
                <div style="margin-bottom: 15px; display: flex; gap: 15px; align-items: center; flex-wrap:wrap; justify-content:center;">
                    <span style="color: #4a5568;"><i class="fas fa-map-marker-alt"></i> <?php echo isset($v['farm_location']) && $v['farm_location'] ? $v['farm_location'] : $v['location']; ?></span>
                    <span style="color: #4a5568;">
                        <i class="fas fa-star text-orange"></i> <strong><?php echo $avg_rating; ?></strong> (<?php echo $review_count; ?> reviews)
                    </span>
                    <span style="color: #718096;">Member since <?php echo $member_since; ?></span>
                </div>
                
                <?php if(!empty($v['bio'])): ?>
                    <p style="color: #4a5568; margin:0; max-width:600px;"><?php echo nl2br($v['bio']); ?></p>
                <?php else: ?>
                    <p style="color: #718096; margin:0; font-style:italic;">No bio available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function shareShop() {
    if (navigator.share) {
        navigator.share({
            title: "Check out <?php echo addslashes($v['username']); ?>'s Shop on Dajot",
            text: "Reliable poultry farmer on Dajot Marketplace. Rating: <?php echo $avg_rating; ?>/5",
            url: window.location.href,
        }).catch(console.error);
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Shop link copied to clipboard!');
    }
}
</script>

<div class="container">
    <div class="shop-container">
        <!-- Main Content (Tabs) -->
        <div class="products-area">
            
            <div style="border-bottom: 1px solid #e2e8f0; margin-bottom: 25px;">
                <button onclick="showTab('products')" id="tab-products" class="tab-btn active">Products (<?php echo $l_res->num_rows; ?>)</button>
                <button onclick="showTab('reviews')" id="tab-reviews" class="tab-btn">Reviews (<?php echo $review_count; ?>)</button>
            </div>

            <?php if (isset($success)): ?>
                <div style="background:#c6f6d5; color:#22543d; padding:15px; border-radius:8px; margin-bottom:20px;"><?php echo e($success); ?></div>
            <?php endif; ?>

            <!-- Products Tab -->
            <div id="view-products">
                <?php if ($l_res && $l_res->num_rows > 0): ?>
                    <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));">
                        <?php while($row = $l_res->fetch_assoc()): 
                            $image = !empty($row['image']) ? $row['image'] : 'assets/images/logo.png';
                            $stock_qty = isset($row['stock_quantity']) ? $row['stock_quantity'] : 0;
                            $stock_msg = $stock_qty > 0 ? $stock_qty . ' in stock' : 'Out of stock';
                            $price_type = isset($row['price_type']) ? $row['price_type'] : 'fixed';
                            $unit = isset($row['unit']) ? $row['unit'] : 'unit';
                        ?>
                            <div class="product-card">
                                <a href="product-details.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit;">
                                    <div class="product-img-placeholder" style="height:180px; padding:0; position:relative;">
                                        <img src="<?php echo $image; ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php if($price_type == 'negotiable'): ?>
                                            <span style="position:absolute; bottom:10px; right:10px; background:rgba(0,0,0,0.6); color:white; padding:2px 8px; border-radius:4px; font-size:0.7em;">Negotiable</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3 style="font-size:1em;"><?php echo $row['product_name']; ?></h3>
                                        <p class="price" style="margin: 8px 0; font-size:1.1em;">₦<?php echo number_format($row['price']); ?> <small style="color:#666; font-size:0.6em; font-weight:normal;">/<?php echo $unit; ?></small></p>
                                        <div style="display:flex; justify-content:space-between; font-size: 0.8em; color: #718096;">
                                            <span><?php echo $row['category']; ?></span>
                                            <span><?php echo $stock_msg; ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e0;">
                        <i class="fas fa-box-open" style="font-size: 3em; color: #cbd5e0; margin-bottom: 20px;"></i>
                        <p style="color: #718096;">This vendor has no active listings at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reviews Tab -->
            <div id="view-reviews" style="display:none;">
                
                <div style="background:#fff; padding:25px; border-radius:12px; border:1px solid #eee; margin-bottom:30px;">
                    <h3 style="margin-top:0;">Write a Review</h3>
                    
                    <?php if (isset($error)): ?>
                        <div style="background:#fed7d7; color:#822727; padding:10px; border-radius:8px; margin-bottom:15px; font-size:0.9em;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($user_id != $vendor_id): ?>
                        <form action="" method="POST">
                            <?php csrf_input(); ?>
                            <?php if (!$user_id): ?>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div class="form-group">
                                        <label>Your Name</label>
                                        <input type="text" name="guest_name" class="form-control" placeholder="e.g. John Doe" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email (Optional)</label>
                                        <input type="email" name="guest_email" class="form-control" placeholder="john@example.com">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Rating</label>
                                <div class="rating-input">
                                    <select name="rating" class="form-control" style="width:100%; max-width: 250px;">
                                        <option value="5">⭐⭐⭐⭐⭐ (5 - Excellent)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 - Good)</option>
                                        <option value="3">⭐⭐⭐ (3 - Average)</option>
                                        <option value="2">⭐⭐ (2 - Poor)</option>
                                        <option value="1">⭐ (1 - Terrible)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Comment</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience dealing with this seller..." required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary btn-block">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <p style="color:#666; text-align: center;"><i class="fas fa-info-circle"></i> You cannot review your own shop.</p>
                    <?php endif; ?>
                </div>

                <h3>Recent Reviews</h3>
                <?php if ($review_count > 0): ?>
                    <?php 
                    // Need to reset fetching if we looped before? Yes, we did a count check.
                    // But we stored results in array or need to re-query? 
                    // Let's rely on data_seek done above.
                    while($review = $r_res->fetch_assoc()): 
                    ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                                <span style="color:#718096; font-size:0.85em;"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="rating-stars" style="margin-bottom:10px;">
                                <?php for($i=0; $i<$review['rating']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                                <?php for($i=$review['rating']; $i<5; $i++) echo '<i class="far fa-star" style="color:#cbd5e0;"></i>'; ?>
                            </div>
                            <p style="margin:0; color:#4a5568; line-height:1.5;"><?php echo htmlspecialchars($review['comment']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#718096;">No reviews yet. Be the first to review!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="contact-area">
            <div class="contact-card">
                <h3 style="margin:0 0 20px;">Contact Seller</h3>
                
                <?php 
                    $wa = !empty($v['whatsapp']) ? $v['whatsapp'] : $v['phone'];
                    $clean_wa = preg_replace('/[^0-9]/', '', $wa);
                    $wa_num = (substr($clean_wa, 0, 1) == '0') ? '234' . substr($clean_wa, 1) : $clean_wa;
                    $wa_link = "https://wa.me/{$wa_num}?text=" . urlencode("Hello {$v['username']}, I saw your shop on Dajot Marketplace.");
                ?>


                <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-primary btn-block" style="background:#25D366; border:none; margin-bottom:15px; display:flex; align-items:center; justify-content:center; gap:10px;">
                    <i class="fab fa-whatsapp" style="font-size:1.2em;"></i> Message on WhatsApp
                </a>
                
                <a href="tel:<?php echo $v['phone']; ?>" class="btn btn-outline btn-block" style="display:flex; align-items:center; justify-content:center; gap:10px; border-color:#2e7d32; color:#2e7d32;">
                    <i class="fas fa-phone-alt"></i> Call <?php echo $v['phone']; ?>
                </a>

                <?php if(isset($v['last_active']) && $v['last_active']): ?>
                <div style="margin-top:20px; text-align:center; font-size:0.85em; color:#718096;">
                    <i class="fas fa-circle" style="color:#48bb78; font-size:0.7em;"></i> Active <?php echo time_elapsed_string($v['last_active']); ?>
                </div>
                <?php endif; ?>

                <div style="margin-top:25px; padding-top:20px; border-top: 1px solid #eee; font-size: 0.9em; color: #718096;">
                    <?php if($verification == 'verified' || $verification == 'trusted'): ?>
                        <p style="color:#2e7d32;"><i class="fas fa-id-card" style="width:20px; text-align:center;"></i> Government ID Verified</p>
                    <?php else: ?>
                        <p><i class="fas fa-shield-alt" style="width:20px; text-align:center;"></i> Identity Pending Review</p>
                    <?php endif; ?>
                    <p><i class="fas fa-map-marker-alt" style="width:20px; text-align:center;"></i> Based in <?php echo $v['location']; ?></p>
                    <p><i class="fas fa-user-friends" style="width:20px; text-align:center;"></i> Community Member since <?php echo date('Y', strtotime($v['created_at'])); ?></p>
                </div>
            </div>

            <?php if(!empty($v['lat'])): ?>
            <div style="background:#fff; padding:15px; border-radius:15px; border:1px solid #eee; margin-top:20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <h4 style="margin:0 0 10px;">Farm Location</h4>
                <div id="shop-map" style="height:200px; width:100%; border-radius:8px;"></div>
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
                <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var map = L.map('shop-map').setView([<?php echo $v['lat']; ?>, <?php echo $v['lng']; ?>], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: 'OpenStreetMap'
                        }).addTo(map);
                        L.marker([<?php echo $v['lat']; ?>, <?php echo $v['lng']; ?>]).addTo(map)
                            .bindPopup("<?php echo addslashes($v['farm_name']); ?>")
                            .openPopup();
                    });
                </script>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.getElementById('view-products').style.display = 'none';
    document.getElementById('view-reviews').style.display = 'none';
    document.getElementById('tab-products').classList.remove('active');
    document.getElementById('tab-reviews').classList.remove('active');
    
    document.getElementById('view-' + tabName).style.display = 'block';
    document.getElementById('tab-' + tabName).classList.add('active');
}

// Auto-switch to reviews tab if URL has #reviews hash
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#reviews' || window.location.hash === '#view-reviews') {
        showTab('reviews');
        setTimeout(function() {
            document.getElementById('view-reviews').scrollIntoView({ behavior: 'smooth' });
        }, 100);
    }
});
</script>

<?php 
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    // Create a custom array to map values since we calculated weeks manually
    $values = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $diff->d,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];

    foreach ($string as $k => &$v) {
        if ($values[$k]) {
            $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

include 'includes/footer.php'; 
?>
