<?php include 'includes/header.php'; ?>
<?php 
include 'includes/db.php'; 
?>

<!-- 1. HERO SECTION -->
<section class="hero" style="position: relative; overflow: hidden;">
    <!-- Animated Feathers -->
    <div class="feather" style="width:15px; height:8px; top:-50px; left:10%; animation: drift 15s infinite linear; animation-delay: 0s;"></div>
    <div class="feather" style="width:10px; height:6px; top:-50px; left:30%; animation: drift 18s infinite linear; animation-delay: 2s;"></div>
    <div class="feather" style="width:12px; height:7px; top:-50px; left:60%; animation: drift 20s infinite linear; animation-delay: 5s;"></div>
    <div class="feather" style="width:8px; height:4px; top:-50px; left:80%; animation: drift 12s infinite linear; animation-delay: 1s;"></div>
    <div class="feather" style="width:14px; height:9px; top:-50px; left:45%; animation: drift 22s infinite linear; animation-delay: 8s;"></div>

    <div class="container">
        <div class="hero-content">
            <h1>Integrated Solutions for Buying, Selling,<br>and Showcasing Poultry Products.</h1>
            <p class="tagline">Supplying quality poultry products while connecting farmers, businesses, and bulk buyers across Nigeria.</p>
            <div class="hero-buttons">
                <a href="shop.php" class="btn btn-primary">Shop Dajot Products</a>
                <a href="sell-option.php" class="btn btn-secondary">👉 Sell with Dajot</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero-bg.png" alt="Dajot Poultry Farm" style="border-radius:20px;">
        </div>
    </div>
</section>

<!-- 2. FEATURES / VALUE PROP -->
<section class="section" style="padding-top: 0; padding-bottom: 60px;">
    <div class="container">
        <h2 class="section-title" style="margin-bottom: 30px;">Our Specialized Services</h2>
        <div class="features-grid">
            <a href="shop.php" class="feature-card" style="text-decoration:none; color:inherit; display:block;">
                <div class="feature-icon"><i class="fas fa-drumstick-bite"></i></div>
                <h4>Direct Poultry Supply</h4>
                <p>Fresh broilers, eggs, layers & processed chicken.</p>
            </a>
            <a href="shop.php" class="feature-card" style="text-decoration:none; color:inherit; display:block;">
                <div class="feature-icon"><i class="fas fa-boxes"></i></div>
                <h4>Bulk & Retail Orders</h4>
                <p>Supply hotels, restaurants, schools & households.</p>
            </a>
            <a href="marketplace.php" class="feature-card" style="text-decoration:none; color:inherit; display:block;">
                <div class="feature-icon"><i class="fas fa-handshake"></i></div>
                <h4>Farmer Marketplace</h4>
                <p>Farmers showcase products, buyers connect directly.</p>
            </a>
            <a href="https://wa.me/2349034670525" class="feature-card" style="text-decoration:none; color:inherit; display:block;" target="_blank">
                <div class="feature-icon"><i class="fab fa-whatsapp"></i></div>
                <h4>WhatsApp Ordering</h4>
                <p>Fast, simple communication without complex checkout.</p>
            </a>
        </div>
    </div>
</section>


<!-- 4. SHOP DAJOT PRODUCTS -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Shop Dajot Poultry Products</h2>
        <p class="section-subtitle">Premium quality poultry straight from our farms.</p>
        
        <div class="product-grid">
            <?php
            // Limit to 4 for homepage
            $sql = "SELECT * FROM products ORDER BY name ASC LIMIT 4";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $image = !empty($row['image']) ? $row['image'] : 'assets/images/logo.png';
                    ?>
                    <div class="product-card">
                        <div class="product-img-placeholder" style="height:200px; padding:0;">
                             <img src="<?php echo $image; ?>" style="width:100%; height:100%; object-fit:cover;" alt="<?php echo $row['name']; ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo e($row['name']); ?></h3>
                            <p class="price">₦<?php echo number_format($row['price']); ?> <small>/ <?php echo e($row['price_unit']); ?></small></p>
                            <p class="text-gray" style="font-size: 0.85em; margin-bottom: 10px;">
                                <i class="fas fa-boxes"></i> Availability: Bulk & Retail
                            </p>
                            <a href="https://wa.me/2349034670525?text=I%20want%20to%20order%20<?php echo urlencode($row['name']); ?>" class="btn btn-primary btn-sm btn-block">Order Now</a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class="text-center" style="margin-top: 30px;">
            <a href="shop.php" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<!-- 5. MARKETPLACE PREVIEW -->
<section class="section" style="background-color: #f0fff4;">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h2 style="margin:0; font-size: 1.8em;">Today's picks</h2>
            <div style="color: #2e7d32; font-weight: bold; font-size: 0.9em;">
                <i class="fas fa-map-marker-alt"></i> Jos &bull; 50 km
            </div>
        </div>
        <p class="section-subtitle" style="text-align:left; margin: -10px 0 30px;">Connect directly with verified poultry farmers.</p>
        
        <div class="product-grid">
            <?php
            // 1. Fetch up to 4 Approved Farmer Listings
            // 30-minute rotation seed
            $seed = floor(time() / 1800);
            
            // Fetch UNIQUE vendors (one product per vendor) in a rotating order
            $sql_listings = "SELECT * FROM listings 
                             WHERE status = 'approved' AND type = 'marketplace' 
                             GROUP BY user_id 
                             ORDER BY RAND($seed) 
                             LIMIT 4";
            $res_listings = $conn->query($sql_listings);
            $count = 0;

            if ($res_listings && $res_listings->num_rows > 0) {
                while($row = $res_listings->fetch_assoc()) {
                    $count++;
                    $image = !empty($row['image']) ? $row['image'] : 'assets/images/logo.png';
                    $whatsapp = !empty($row['whatsapp']) ? $row['whatsapp'] : $row['phone'];
                    
                    // Fetch user plan for badges
                    $farmer_id = $row['user_id'];
                    $plan_q = $conn->query("SELECT plan FROM users WHERE id = $farmer_id");
                    $farmer_plan = ($plan_q && $user_plan_data = $plan_q->fetch_assoc()) ? $user_plan_data['plan'] : 'free';
                    ?>
                    <div class="product-card">
                        <a href="product-details.php?id=<?php echo $row['id']; ?>" class="product-link" style="text-decoration:none; color:inherit;">
                            <div class="product-img-placeholder" style="height:200px; padding:0; position:relative;">
                                <img src="<?php echo $image; ?>" style="width:100%; height:100%; object-fit:cover;" alt="<?php echo $row['product_name']; ?>">
                                <?php if($farmer_plan == 'pro'): ?>
                                    <span style="position:absolute; top:10px; right:10px; background:#2e7d32; color:white; padding:2px 8px; border-radius:4px; font-size:0.7em;"><i class="fas fa-check-circle"></i> VERIFIED</span>
                                <?php elseif($farmer_plan == 'gold'): ?>
                                    <span style="position:absolute; top:10px; right:10px; background:#ecc94b; color:#333; padding:2px 8px; border-radius:4px; font-size:0.7em; font-weight:bold;"><i class="fas fa-crown"></i> FEATURED</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo e($row['product_name']); ?></h3>
                                <p style="font-size: 0.9em; margin-bottom: 5px;">
                                    <a href="vendor-shop.php?id=<?php echo $row['user_id']; ?>" style="color:#2e7d32; text-decoration:none; font-weight:600;">
                                        <i class="fas fa-user-circle"></i> <?php echo e($row['farmer_name']); ?>
                                    </a>
                                </p>
                                <p class="text-green" style="font-size: 0.9em; margin-bottom: 5px;"><i class="fas fa-map-marker-alt"></i> <?php echo e($row['location']); ?></p>
                                <p class="price">₦<?php echo number_format($row['price']); ?></p>
                                <p class="text-gray" style="font-size: 0.85em; margin-bottom: 10px;">
                                    <i class="fas fa-layer-group"></i> Stocks: <?php echo e($row['quantity']); ?> <?php echo e($row['unit']); ?>
                                </p>
                            </div>
                        </a>
                        <div style="padding: 0 15px 15px;">
                            <?php 
                                // Robust WhatsApp Number Formatting
                                $clean_phone = preg_replace('/[^0-9]/', '', $whatsapp); // Remove non-digits
                                if (substr($clean_phone, 0, 3) == '234') { 
                                    $wa_number = $clean_phone; 
                                } elseif (substr($clean_phone, 0, 1) == '0') { 
                                    $wa_number = '234' . substr($clean_phone, 1); 
                                } else { 
                                    $wa_number = '234' . $clean_phone; 
                                }

                                // Detailed Message
                                $msg = "Hello {$row['farmer_name']}, I am interested in your {$row['product_name']} listed on Dajot Marketplace for ₦" . number_format($row['price']);
                                $wa_link = "https://wa.me/{$wa_number}?text=" . urlencode($msg);
                            ?>
                            <a href="<?php echo $wa_link; ?>" class="btn btn-secondary btn-sm btn-block" target="_blank">Contact Seller</a>
                        </div>
                    </div>
                    <?php
                }
            }

            // 2. FILL EMPTY SLOTS: If less than 4 items, fill with Dajot Catalog
            if ($count < 4) {
                $needed = 4 - $count;
                $sql_fallback = "SELECT * FROM products ORDER BY name ASC LIMIT $needed";
                $res_fallback = $conn->query($sql_fallback);
                
                if ($res_fallback && $res_fallback->num_rows > 0) {
                    while($row = $res_fallback->fetch_assoc()) {
                        $image = !empty($row['image']) ? $row['image'] : 'assets/images/logo.png';
                        ?>
                        <div class="product-card">
                            <div class="product-img-placeholder" style="height:200px; padding:0;">
                                 <img src="<?php echo $image; ?>" style="width:100%; height:100%; object-fit:cover;" alt="<?php echo $row['name']; ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo e($row['name']); ?></h3>
                                <p class="text-gray" style="font-size: 0.9em; margin-bottom: 5px;"><i class="fas fa-certificate"></i> Official Dajot Catalog</p>
                                <p class="price">₦<?php echo number_format($row['price']); ?></p>
                                <p class="text-gray" style="font-size: 0.85em; margin-bottom: 10px;">
                                    <i class="fas fa-cubes"></i> Available: <?php echo $row['quantity'] ?? '10+'; ?> <?php echo $row['unit'] ?? 'units'; ?>
                                </p>
                                <a href="https://wa.me/2349034670525?text=I%20am%20interested%20in%20<?php echo urlencode($row['name']); ?>" class="btn btn-primary btn-sm btn-block">Order via Dajot</a>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <div class="text-center" style="margin-top: 30px;">
            <a href="marketplace.php" class="btn btn-outline">Explore Marketplace</a>
        </div>
    </div>
</section>

<!-- 6. HOW IT WORKS -->
<section class="section">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Browse Products</h3>
                <p>Explore poultry products from Dajot and verified farmers.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Connect Instantly</h3>
                <p>Chat or call sellers directly via WhatsApp.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Buy or Sell with Confidence</h3>
                <p>Bulk supply, retail orders, and farmer sourcing made easy.</p>
            </div>
        </div>
    </div>
</section>

<!-- 7. MEET THE TEAM -->
<section class="section" style="background-color: #f9f9f9;">
    <div class="container">
        <h2 class="section-title">Meet Our Team</h2>
        <p class="section-subtitle">A family-led poultry business built on trust, experience, and quality.</p>
        <div class="team-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 25px;">
            <div class="team-card" style="text-align: center; padding: 25px 15px; border-radius: 15px;">
                <img src="assets/images/buhari.jpeg" alt="Buhari Dajot" style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.12); border: 3px solid #e8f5e9;">
                <h4 style="margin-bottom: 5px;">Buhari Dajot</h4>
                <p>Business Lead</p>
            </div>
            <div class="team-card" style="text-align: center; padding: 25px 15px; border-radius: 15px;">
                <img src="assets/images/hamza.jpeg" alt="Hamza Dajot" style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.12); border: 3px solid #e8f5e9;">
                <h4 style="margin-bottom: 5px;">Hamza Dajot</h4>
                <p>Operations Lead</p>
            </div>
            <div class="team-card" style="text-align: center; padding: 25px 15px; border-radius: 15px;">
                <img src="assets/images/Duwa.png" alt="Abdul Karim Dajot" style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.12); border: 3px solid #e8f5e9;">
                <h4 style="margin-bottom: 5px;">Abdul Karim Dajot</h4>
                <p>Quality Lead</p>
            </div>
            <div class="team-card" style="text-align: center; padding: 25px 15px; border-radius: 15px;">
                <img src="assets/images/ismail.jpeg" alt="Ismail Dajot" style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.12); border: 3px solid #e8f5e9;">
                <h4 style="margin-bottom: 5px;">Ismail Dajot</h4>
                <p>Transport Lead</p>
            </div>
            <div class="team-card" style="text-align: center; padding: 25px 15px; border-radius: 15px;">
                <img src="assets/images/Beeeba.png" alt="Habeeba Shuaibu Dajot" style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.12); border: 3px solid #e8f5e9;">
                <h4 style="margin-bottom: 5px;">Habeeba Shuaibu Dajot</h4>
                <p>Digital & Systems Lead</p>
            </div>
        </div>
    </div>
</section>

<!-- 8. CTA BANNER -->
<section class="cta-banner">
    <div class="container">
        <h2>Are You a Poultry Farmer or Bulk Buyer?</h2>
        <div class="cta-buttons">
            <a href="sell-to-us.php" class="btn btn-light"><i class="fas fa-store"></i> Sell to Dajot</a>
            <a href="vendor-register.php" class="btn btn-primary"><i class="fas fa-users"></i> Join the Marketplace</a>
            <a href="https://wa.me/2349034670525" class="btn btn-outline" style="border-color:#fff; color:#fff;"><i class="fab fa-whatsapp"></i> Contact Us on WhatsApp</a>
        </div>
    </div>
    </div>
</section>

<section class="section" style="background-color: #fff; border-top: 1px solid #eee;">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
             <div>
                <h2 class="section-title">Community Trust Wall</h2>
                <p class="section-subtitle">Real feedback from verified purchases.</p>
             </div>
             <a href="reviews.php" class="btn btn-outline btn-sm">View More</a>
        </div>

        <div class="cat-grid">
            <?php
            // Fetch High Rated Reviews (3+ Stars)
            $sql_reviews = "SELECT r.*, u.username as farmer_name, u.farm_name 
                            FROM reviews r 
                            JOIN users u ON r.seller_id = u.id 
                            WHERE r.rating >= 3 
                            ORDER BY r.created_at DESC LIMIT 3";
            $res_reviews = $conn->query($sql_reviews);

            if ($res_reviews && $res_reviews->num_rows > 0) {
                while($rev = $res_reviews->fetch_assoc()) {
                    ?>
                    <div style="background: #f9fff9; padding: 25px; border-radius: 8px; border-left: 4px solid #48bb78; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div style="margin-bottom: 15px;">
                            <?php for($i=0; $i<$rev['rating']; $i++) echo '<i class="fas fa-star" style="color:#ecc94b;"></i>'; ?>
                        </div>
                        <p style="font-style: italic; color: #555; margin-bottom: 20px;">"<?php echo htmlspecialchars($rev['comment']); ?>"</p>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h5 style="margin: 0; font-size: 14px;"><?php echo htmlspecialchars($rev['reviewer_name']); ?></h5>
                                <?php $disp_name = !empty($rev['farm_name']) ? $rev['farm_name'] : $rev['farmer_name']; ?>
                                <small style="color: #888;">Bought from <strong><?php echo htmlspecialchars($disp_name); ?></strong></small>
                            </div>
                            <small style="color: #cbd5e0;"><?php echo date('M d', strtotime($rev['created_at'])); ?></small>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div style="grid-column: 1 / -1; text-align: center; color: #888; padding: 20px;">
                        <i class="far fa-comments" style="font-size: 40px; opacity: 0.3;"></i>
                        <p>No reviews yet. Start buying and sharing your experience!</p>
                        <a href="marketplace.php" class="btn btn-primary btn-sm">Start Shopping</a>
                      </div>';
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
