<?php
session_start();
include 'includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: marketplace.php");
    exit;
}

$id = intval($_GET['id']);

$is_review = (isset($_GET['mode']) && $_GET['mode'] === 'review');
$is_owner = false;

// Fetch Listing and Seller Info (Join listings with users)
// Allow the owner of the listing to see it even if it's pending
$current_user_id = $_SESSION['user_id'] ?? 0;
$sql = "SELECT l.*, u.username as farmer_name, u.location as farmer_location, u.phone as farmer_phone, 
               u.whatsapp as farmer_whatsapp, u.created_at as member_since, u.plan as farmer_plan, u.profile_photo, u.verification_level
        FROM listings l 
        JOIN users u ON l.user_id = u.id 
        WHERE l.id = $id AND (l.status = 'approved' OR l.user_id = $current_user_id)";

$res = $conn->query($sql);
if (!$res || $res->num_rows == 0) {
    echo "<div style='padding:50px; text-align:center;'><h2>Product not found or pending approval.</h2><p>Only the owner can preview pending items.</p><a href='marketplace.php'>Back to Marketplace</a></div>";
    exit;
}

$p = $res->fetch_assoc();
$is_owner = ($current_user_id > 0 && $current_user_id == $p['user_id']);
$image = !empty($p['image']) ? $p['image'] : 'assets/images/logo.png';

// Human-readable date
$listed_date = date('M d, Y', strtotime($p['created_at']));
$member_since = date('M Y', strtotime($p['member_since']));

// WhatsApp formatting
$whatsapp = !empty($p['farmer_whatsapp']) ? $p['farmer_whatsapp'] : $p['farmer_phone'];
$clean_phone = preg_replace('/[^0-9]/', '', $whatsapp);
$wa_number = (substr($clean_phone, 0, 1) == '0') ? '234' . substr($clean_phone, 1) : $clean_phone;
if(strlen($wa_number) < 13 && substr($wa_number, 0, 3) != '234') $wa_number = '234'.$wa_number;

$msg = "Hello {$p['farmer_name']}, I saw your listing '{$p['product_name']}' on Dajot Marketplace. Is it still available?";
$wa_link = "https://wa.me/{$wa_number}?text=" . urlencode($msg);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_review ? 'Review Listing' : $p['product_name']; ?> - Dajot Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .details-container { max-width: 900px; margin: 40px auto; padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .product-gallery img { width: 100%; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-meta h1 { font-size: 2.5em; margin-bottom: 5px; color: #1a202c; }
        .p-price { font-size: 1.8em; font-weight: bold; color: #2e7d32; margin: 15px 0; }
        .p-meta-info { color: #718096; font-size: 0.95em; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        
        .whatsapp-card { background: #f0fff4; padding: 20px; border-radius: 12px; border: 1px solid #c6f6d5; margin-bottom: 30px; }
        .seller-info-box { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #eee; }
        .seller-header { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .seller-avatar { width: 50px; height: 50px; background: #2e7d32; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5em; font-weight: bold; }
        
        .action-btns { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 20px 0; }
        .action-btn { background: #f7fafc; border: none; padding: 10px; border-radius: 8px; text-align: center; color: #4a5568; cursor: pointer; transition: 0.3s; }
        .action-btn:hover { background: #edf2f7; }
        .action-btn i { display: block; margin-bottom: 5px; font-size: 1.2em; }

        @media (max-width: 768px) { .details-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body style="background: #f8fafc;">

<?php if (!$is_review): ?>
    <?php include 'includes/header.php'; ?>

    <!-- ⚠️ Transaction Warning Banner -->
    <div style="background: linear-gradient(90deg, #fff3cd, #ffeeba); border-bottom: 2px solid #ffc107; padding: 12px 0;">
        <div class="container" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: center;">
            <span style="background: #856404; color: white; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.85em;">
                <i class="fas fa-shield-alt"></i> NOTICE
            </span>
            <p style="margin: 0; color: #856404; font-size: 0.9em; text-align: center;">
                Transactions are completed directly via WhatsApp or phone. 
                <strong>Please confirm product details before payment.</strong>
            </p>
        </div>
    </div>

    <div class="container" style="margin-top: 20px;">
        <a href="vendor-shop.php?id=<?php echo $p['user_id']; ?>" style="color: #2e7d32; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-weight: 500;">
            <i class="fas fa-arrow-left"></i> Back to <?php echo $p['farmer_name']; ?>'s Shop
        </a>
    </div>
<?php else: ?>
    <!-- Review Mode Header -->
    <div style="background: white; padding: 20px 0; border-bottom: 1px solid #eee; margin-bottom: 30px;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin:0; color: #2d3748;"><i class="fas fa-eye" style="color:#2e7d32;"></i> Review Your Listing</h2>
            <a href="vendor-dashboard.php" class="btn btn-primary" style="background: #2e7d32;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
<?php endif; ?>

<div class="details-container">
    <!-- Left: Image -->
    <div class="product-gallery">
        <img src="<?php echo $image; ?>" alt="<?php echo $p['product_name']; ?>">
    </div>

    <!-- Right: Content -->
    <div class="product-meta">
        <h1><?php echo e($p['product_name']); ?></h1>
        <div class="p-price">₦<?php echo number_format($p['price']); ?> <span style="font-size: 0.5em; color: #718096;">per <?php echo e($p['unit']); ?></span></div>
        
        <div class="p-meta-info">
            <i class="fas fa-clock"></i> Listed <?php echo $listed_date; ?> in <strong><?php echo e($p['location']); ?></strong><br>
            <i class="fas fa-check-circle" style="color:#2e7d32;"></i> In Stock: <?php echo e($p['quantity']); ?> <?php echo e($p['unit']); ?> available
        </div>

        <!-- Product Description (Moved Up) -->
        <div style="margin-bottom: 25px; font-size: 1.05em; line-height: 1.6; color: #2d3748;">
            <?php echo nl2br($p['description']); ?>
        </div>

        <?php if (!$is_review && !$is_owner): ?>
        <div class="whatsapp-card">
            <h4 style="margin:0 0 10px; color:#276749;"><i class="fas fa-comments"></i> Contact Seller</h4>
            
            <div style="background: #fff; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9em; color:#4a5568; border-left: 3px solid #25D366;">
                "Hi, is this <strong><?php echo e($p['product_name']); ?></strong> still available?"
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-primary btn-block" style="background:#25D366; border:none; display:flex; align-items:center; justify-content:center; gap:5px;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="tel:<?php echo $p['farmer_phone']; ?>" class="btn btn-outline btn-block" style="color:#2e7d32; border:1px solid #2e7d32; display:flex; align-items:center; justify-content:center; gap:5px;">
                    <i class="fas fa-phone-alt"></i> Call
                </a>
            </div>
        </div>

        <div class="action-btns">
            <button class="action-btn" onclick="openAlertModal()">
                <i class="fas fa-bell"></i><small>Alert</small>
            </button>
            <a href="vendor-shop.php?id=<?php echo $p['user_id']; ?>" class="action-btn" style="text-decoration:none;">
                <i class="fas fa-store"></i><small>Visit Shop</small>
            </a>
            <button class="action-btn" id="saveBtn" onclick="toggleSave()">
                <i class="fas fa-bookmark" id="saveIcon"></i><small id="saveText">Save</small>
            </button>
            <button class="action-btn" onclick="shareProduct()">
                <i class="fas fa-share"></i><small>Share</small>
            </button>
        </div>
        <?php elseif($is_owner && !$is_review): ?>
        <div style="background: #e6fffa; padding: 15px; border-radius: 8px; border: 1px solid #b2f5ea; margin-bottom: 20px; color: #2c7a7b;">
            <i class="fas fa-info-circle"></i> This is your own listing. Customers will see contact options here.
        </div>
        <?php endif; ?>

        <!-- Price Alert Modal -->
        <div id="alertModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
            <div class="modal-content" style="background:white; padding:30px; border-radius:15px; max-width:400px; width:90%; position:relative;">
                <button onclick="closeAlertModal()" style="position:absolute; top:10px; right:15px; border:none; background:none; font-size:1.5em; cursor:pointer;">&times;</button>
                <h3 style="margin-top:0;"><i class="fas fa-bell text-green"></i> Price Drop Alert</h3>
                <p style="font-size:0.9em; color:#666;">We'll notify you if the price of <strong><?php echo e($p['product_name']); ?></strong> drops below ₦<?php echo number_format($p['price']); ?>.</p>
                
                <input type="text" id="alertContact" placeholder="Your WhatsApp or Email" style="width:100%; padding:12px; margin:15px 0; border:1px solid #ddd; border-radius:8px;">
                <button onclick="saveAlert()" id="saveAlertBtn" class="btn btn-primary btn-block">Set Alert</button>
                <div id="alertMsg" style="margin-top:10px; font-size:0.85em; display:none;"></div>
            </div>
        </div>

        <script>
        // Alert Functions
        function openAlertModal() {
            document.getElementById('alertModal').style.display = 'flex';
        }
        function closeAlertModal() {
            document.getElementById('alertModal').style.display = 'none';
        }
        function saveAlert() {
            const contact = document.getElementById('alertContact').value;
            const btn = document.getElementById('saveAlertBtn');
            const msg = document.getElementById('alertMsg');
            
            if(!contact) {
                alert('Please enter your contact details.');
                return;
            }

            btn.disabled = true;
            btn.innerText = 'Setting...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('save-alert.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `contact=${encodeURIComponent(contact)}&listing_id=<?php echo $id; ?>&price=<?php echo $p['price']; ?>&csrf_token=${csrfToken}`
            })
            .then(res => res.json())
            .then(data => {
                msg.style.display = 'block';
                if(data.status === 'success') {
                    msg.innerHTML = '<span style="color:green">✅ Alert set! We\'ll be in touch.</span>';
                    setTimeout(closeAlertModal, 2000);
                } else {
                    msg.innerHTML = `<span style="color:red">❌ ${data.message || 'Error. Try again.'}</span>`;
                    btn.disabled = false;
                    btn.innerText = 'Set Alert';
                }
            })
            .catch(err => {
                console.error(err);
                msg.innerHTML = '<span style="color:red">❌ Network Error.</span>';
                btn.disabled = false;
            });
        }

        // Save Functionality with LocalStorage
        const productId = "<?php echo $id; ?>";
        document.addEventListener('DOMContentLoaded', () => {
            if(localStorage.getItem('saved_product_' + productId)) {
                setSavedState(true);
            }
        });

        function setSavedState(isSaved) {
            const icon = document.getElementById('saveIcon');
            const text = document.getElementById('saveText');
            if(isSaved) {
                icon.style.color = '#2e7d32';
                text.innerText = 'Saved';
            } else {
                icon.style.color = '#666';
                text.innerText = 'Save';
            }
        }

        function toggleSave() {
            if(localStorage.getItem('saved_product_' + productId)) {
                localStorage.removeItem('saved_product_' + productId);
                setSavedState(false);
            } else {
                localStorage.setItem('saved_product_' + productId, 'true');
                setSavedState(true);
            }
        }

        // Share Functionality with Fallback
        function shareProduct() {
            const url = window.location.href;
            if (navigator.share) {
                navigator.share({
                    title: "<?php echo addslashes($p['product_name']); ?> on Dajot",
                    text: "Check out this <?php echo addslashes($p['product_name']); ?> from <?php echo addslashes($p['farmer_name']); ?>",
                    url: url,
                }).catch(console.error);
            } else {
                // Clipboard safe approach
                const textArea = document.createElement("textarea");
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    alert('Link copied to clipboard!');
                } catch (err) {
                    prompt("Copy this link:", url);
                }
                document.body.removeChild(textArea);
            }
        }
        </script>

        <?php if (!$is_review): ?>
        <!-- Seller Details Section -->
        <h3 style="margin-top: 30px;">Seller Details</h3>
        <div class="seller-info-box">
            <div class="seller-header">
                <div class="seller-avatar">
                <?php if(!empty($p['profile_photo'])): ?>
                    <img src="<?php echo $p['profile_photo']; ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                <?php else: ?>
                    <?php echo strtoupper(substr($p['farmer_name'], 0, 1)); ?>
                <?php endif; ?>
                </div>
                <div>
                    <h4 style="margin:0; display:flex; align-items:center; gap:8px;">
                        <a href="vendor-shop.php?id=<?php echo $p['user_id']; ?>" style="color:inherit; text-decoration:none;">
                            <?php echo e($p['farmer_name']); ?>
                        </a>
                        <?php if($p['verification_level'] == 'trusted'): ?>
                            <i class="fas fa-shield-check" style="color:#2e7d32;" title="Trusted Seller"></i>
                        <?php elseif($p['verification_level'] == 'verified'): ?>
                            <i class="fas fa-id-card" style="color:#2c5282;" title="ID Verified"></i>
                        <?php endif; ?>
                    </h4>
                    <small style="color:#718096;">Member since <?php echo $member_since; ?></small>
                </div>
            </div>
            
            <?php
            // Fetch Rating
            $rating_q = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE seller_id = {$p['user_id']}");
            if ($rating_q) {
                $rating_data = $rating_q->fetch_assoc();
                $avg = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
                $count = $rating_data['count'];
            } else {
                $avg = 0;
                $count = 0;
            }
            ?>
            
            <div style="margin-bottom: 15px; font-size: 0.9em;">
                <span style="color: #ecc94b;">
                    <?php 
                    for($i=1; $i<=5; $i++) {
                        if($i <= $avg) echo '<i class="fas fa-star"></i>';
                        elseif($i - 0.5 <= $avg) echo '<i class="fas fa-star-half-alt"></i>';
                        else echo '<i class="far fa-star" style="color:#cbd5e0;"></i>';
                    }
                    ?>
                </span>
                <span style="color: #666; margin-left: 5px; font-weight: bold;"><?php echo $avg; ?></span>
                <span style="color: #a0aec0; font-size: 0.9em;">(<?php echo $count; ?> reviews)</span>
            </div>

            <div style="font-size: 0.9em; color:#4a5568; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                <p><i class="fas fa-map-marker-alt"></i> <?php echo e($p['farmer_location']); ?></p>
                <!-- Location Map -->
                <div style="margin-top: 10px; border-radius: 12px; overflow: hidden; border: 1px solid #eee;">
                    <iframe 
                        width="100%" 
                        height="150" 
                        style="border:0;" 
                        loading="lazy" 
                        allowfullscreen 
                        src="https://maps.google.com/maps?q=<?php echo urlencode($p['farmer_location']); ?>&t=&z=13&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
                
                <div style="margin-top: 15px; text-align: center;">
                     <a href="vendor-shop.php?id=<?php echo $p['user_id']; ?>#reviews" class="btn btn-outline btn-block" style="font-size: 0.9em;">
                        <i class="fas fa-pen"></i> Write a Review
                     </a>
                </div>
            </div>
            
            <!-- Recent Reviews Preview -->
            <div style="margin-top: 15px;">
                <h5 style="margin: 0 0 10px;">Recent Reviews</h5>
                <?php
                $rev_sql = "SELECT * FROM reviews WHERE seller_id = {$p['user_id']} ORDER BY created_at DESC LIMIT 2";
                $rev_res = $conn->query($rev_sql);
                if($rev_res && $rev_res->num_rows > 0):
                    while($rev = $rev_res->fetch_assoc()):
                ?>
                    <div style="background: #f7fafc; padding: 10px; border-radius: 6px; margin-bottom: 8px; font-size: 0.85em;">
                        <div style="display:flex; justify-content:space-between; margin-bottom: 5px;">
                            <strong><?php echo htmlspecialchars($rev['reviewer_name']); ?></strong>
                            <span style="color:#ecc94b;">
                                <?php for($k=0;$k<$rev['rating'];$k++) echo '<i class="fas fa-star" style="font-size:0.8em;"></i>'; ?>
                            </span>
                        </div>
                        <p style="margin:0; color:#555;"><?php echo htmlspecialchars($rev['comment']); ?></p>
                    </div>
                <?php endwhile; ?>
                <a href="vendor-shop.php?id=<?php echo $p['user_id']; ?>#view-reviews" style="font-size: 0.85em; color: #2e7d32;">View all reviews</a>
                <?php else: ?>
                    <p style="font-size: 0.85em; color: #999; font-style: italic;">No reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php if (!$is_review) include 'includes/footer.php'; ?>

</body>
</html>
