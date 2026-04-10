<?php include 'includes/header.php'; ?>
<?php include 'includes/db.php'; ?>

<?php
// Define product categories
$categories = [
    'Live Birds' => ['Broilers', 'Layers', 'Turkeys', 'Local Chickens', 'Cockerels'],
    'Eggs' => ['Table Eggs', 'Hatchery Eggs', 'Fertilized Eggs'],
    'Chicks' => ['Day-old Chicks', 'Week-old Chicks'],
    'Feed & Equipment' => ['Starter Feed', 'Grower Feed', 'Finisher Feed', 'Feeders', 'Drinkers'],
    'Processed' => ['Frozen Chicken', 'Dressed Chicken', 'Chicken Parts']
];
$all_categories = [];
foreach ($categories as $group => $items) {
    $all_categories = array_merge($all_categories, $items);
}
?>


<!-- Header -->
<div class="page-header text-center" style="background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); color: white; padding: 50px 0; margin-top: 15px;">
    <div class="container">
        <h1 style="font-size: 2.2em; margin-bottom: 10px;">Farmer Marketplace</h1>
        <p style="opacity: 0.9;">Connect directly with verified poultry farmers across Nigeria</p>
    </div>
</div>

<!-- ⚠️ Transaction Warning Banner -->
<div style="background: linear-gradient(90deg, #fff3cd, #ffeeba); border-bottom: 2px solid #ffc107; padding: 12px 0;">
    <div class="container" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: center;">
        <span style="background: #856404; color: white; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.85em;">
            <i class="fas fa-shield-alt"></i> NOTICE
        </span>
        <p style="margin: 0; color: #856404; font-size: 0.9em; text-align: center;">
            Transactions are completed directly between buyers and sellers via WhatsApp or phone. 
            <strong>Please confirm product details before payment.</strong>
        </p>
    </div>
</div>

<!-- Search Strip -->
<div class="search-strip" style="background: white; padding: 25px 0; border-bottom: 1px solid #eee;">
    <div class="container">
        <form id="filterForm" action="marketplace.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: center;">
            
            <!-- Country Select -->
            <select id="country" name="country" onchange="populateStates()" style="flex: 1; min-width: 150px; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95em;">
                <option value="">Select Country</option>
                <option value="Nigeria" <?php echo (isset($_GET['country']) && $_GET['country'] == 'Nigeria') ? 'selected' : ''; ?>>Nigeria</option>
                <option value="Ghana" <?php echo (isset($_GET['country']) && $_GET['country'] == 'Ghana') ? 'selected' : ''; ?>>Ghana</option>
                <option value="Kenya" <?php echo (isset($_GET['country']) && $_GET['country'] == 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                <option value="South Africa" <?php echo (isset($_GET['country']) && $_GET['country'] == 'South Africa') ? 'selected' : ''; ?>>South Africa</option>
                <option value="Uganda" <?php echo (isset($_GET['country']) && $_GET['country'] == 'Uganda') ? 'selected' : ''; ?>>Uganda</option>
                <option value="Tanzania" <?php echo (isset($_GET['country']) && $_GET['country'] == 'Tanzania') ? 'selected' : ''; ?>>Tanzania</option>
                <option value="Rest of Africa">Rest of Africa</option>
            </select>

            <!-- State Select (Hidden by default or populated) -->
            <select id="state" name="state" onchange="submitForm()" style="flex: 1; min-width: 150px; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95em; display:none;">
                <option value="">Select State/Region</option>
            </select>
            
            <!-- Category Select -->
            <select name="category" onchange="submitForm()" style="flex: 1; min-width: 150px; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95em;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $group => $items): ?>
                    <optgroup label="<?php echo $group; ?>">
                        <?php foreach ($items as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<script>
const statesData = {
    "Nigeria": ["Abia","Adamawa","Akwa Ibom","Anambra","Bauchi","Bayelsa","Benue","Borno","Cross River","Delta","Ebonyi","Edo","Ekiti","Enugu","FCT - Abuja","Gombe","Imo","Jigawa","Kaduna","Kano","Katsina","Kebbi","Kogi","Kwara","Lagos","Nasarawa","Niger","Ogun","Ondo","Osun","Oyo","Plateau","Rivers","Sokoto","Taraba","Yobe","Zamfara"],
    "Ghana": ["Ashanti","Greater Accra","Central","Eastern","Western","Northern","Volta","Upper East","Upper West","Bono"],
    "Kenya": ["Nairobi","Mombasa","Kisumu","Nakuru","Uasin Gishu","Kiambu","Machakos","Kajiado"],
    "South Africa": ["Gauteng","Western Cape","KwaZulu-Natal","Eastern Cape","Limpopo","Mpumalanga","North West","Free State","Northern Cape"]
};

function populateStates() {
    const country = document.getElementById("country").value;
    const stateSelect = document.getElementById("state");
    const currentSelectedState = "<?php echo isset($_GET['state']) ? htmlspecialchars($_GET['state']) : ''; ?>";

    stateSelect.innerHTML = '<option value="">Select State/Region</option>';
    
    if (country && statesData[country]) {
        stateSelect.style.display = "block";
        statesData[country].forEach(state => {
            const option = document.createElement("option");
            option.value = state;
            option.textContent = state;
            if (state === currentSelectedState) option.selected = true;
            stateSelect.appendChild(option);
        });
    } else {
        stateSelect.style.display = "none";
    }
    submitForm();
}

function submitForm() {
    document.getElementById("filterForm").submit();
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    const country = document.getElementById("country").value;
    if(country) populateStates();
});
</script>

<section class="section" style="padding: 40px 0;">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin:0; font-size: 1.5em; font-weight: 700; color: #333;">
                <i class="fas fa-fire" style="color: #ed8936;"></i> Today's Picks
            </h2>
            <div style="color: #718096; font-size: 0.9em; display: flex; align-items: center; gap: 15px;">
                <span style="color: #2e7d32;"><i class="fas fa-map-marker-alt"></i> Verified Farmers</span>
            </div>
        </div>

        <?php
        $where = "status = 'approved' AND type = 'marketplace'";
        
        // Handle Country/State Logic
        if (isset($_GET['country']) && !empty($_GET['country'])) {
            $country = $conn->real_escape_string($_GET['country']);
            if($country != 'Rest of Africa') {
                // If specific country, search mostly by state if provided, or just broad match
                // We assume 'location' column contains some address text.
                // Ideally we would have separate country/state columns, but we'll LIKE match.
                // Or if we don't have country in DB, we rely on users putting it?
                // For now, let's assume filtering by State implies Country is handled if users enter "Lagos, Nigeria"
            }
        }

        if (isset($_GET['state']) && !empty($_GET['state'])) {
            $state = $conn->real_escape_string($_GET['state']);
            $where .= " AND location LIKE '%$state%'";
        } elseif (isset($_GET['country']) && !empty($_GET['country']) && $_GET['country'] != 'Rest of Africa') {
             // If only country selected (and no state), try to match popular cities or just rely on 'Verified' 
             // Actually, without separate country column, this is tricky. 
             // We will skip strict country filter for now if no state is picked, OR match widely.
             // But user asked for: "State products appear first".
        }

        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $category = $conn->real_escape_string($_GET['category']);
            $where .= " AND category = '$category'";
        }
        
        // Sorting Logic
        $order = "created_at DESC"; // Default
        if (isset($_GET['state']) && !empty($_GET['state'])) {
            $state = $conn->real_escape_string($_GET['state']);
            // Prioritize exact state match, though we already filtered by it in WHERE.
            // If user meant "Show my state first, then others", WHERE shouldn't be exclusive?
            // "peoducts listed on that state will appear fist beoe the other state" implies MIXED results.
            
            // Rewrite logic: If filtering, we usually show ONLY strict matches. 
            // BUT user said "appear first BEFORE others".
            // So we should NOT filter strictly by state in WHERE, but ORDER BY state.
            
            // Reset WHERE for state to be permissive?
            // Actually, usually a filter means "Show only this". 
            // I will implement "Show only this" for now as it's cleaner. 
            // UNLESS user explicitly wants non-state items to show at bottom.
            // "appear first before the other state" -> Implies others are visible.
            
            // Okay, let's remove strict state filtering from WHERE and move to ORDER BY.
            // But we keep Category strict.
            $where = "status = 'approved' AND type = 'marketplace'"; // Reset base
            if (isset($_GET['category']) && !empty($_GET['category'])) {
                $category = $conn->real_escape_string($_GET['category']);
                $where .= " AND category = '$category'";
            }
            
            // Custom sort
            $order = "CASE WHEN location LIKE '%$state%' THEN 0 ELSE 1 END, created_at DESC";
        }
        
        $sql = "SELECT l.*, u.verification_level, u.last_active FROM listings l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE $where ORDER BY $order";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo '<div class="product-grid">';
            while($row = $result->fetch_assoc()) {
                $image = !empty($row['image']) ? $row['image'] : 'assets/images/logo.png';
                $whatsapp = !empty($row['whatsapp']) ? $row['whatsapp'] : $row['phone'];
                $verification = $row['verification_level'] ?? 'basic';
                $price_type = $row['price_type'] ?? 'fixed';
                $stock = $row['stock_quantity'] ?? $row['quantity'] ?? 1;
                ?>
                <div class="product-card" style="border-radius: 12px; overflow: hidden; transition: all 0.3s ease;">
                    <a href="product-details.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="product-img-placeholder" style="height:200px; padding: 0; background: #fff; position: relative;">
                            <img src="<?php echo $image; ?>" style="width:100%; height:100%; object-fit:cover;" alt="<?php echo $row['product_name']; ?>">
                            
                            <!-- Verification Badge -->
                            <?php if ($verification == 'trusted'): ?>
                                <span style="position:absolute; top:10px; left:10px; background:#2e7d32; color:white; padding:4px 10px; border-radius:4px; font-size:0.75em; font-weight:600; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    <i class="fas fa-shield-check"></i> TRUSTED SELLER
                                </span>
                            <?php elseif ($verification == 'verified'): ?>
                                <span style="position:absolute; top:10px; left:10px; background:#2c5282; color:white; padding:4px 10px; border-radius:4px; font-size:0.75em; font-weight:600; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    <i class="fas fa-id-card"></i> ID VERIFIED
                                </span>
                            <?php endif; ?>
                            
                            <!-- Price Type Badge -->
                            <?php if ($price_type == 'negotiable'): ?>
                                <span style="position:absolute; top:10px; right:10px; background:#ed8936; color:white; padding:4px 8px; border-radius:4px; font-size:0.7em;">
                                    NEGOTIABLE
                                </span>
                            <?php elseif ($price_type == 'bulk'): ?>
                                <span style="position:absolute; top:10px; right:10px; background:#805ad5; color:white; padding:4px 8px; border-radius:4px; font-size:0.7em;">
                                    BULK DISCOUNT
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info" style="padding: 15px;">
                            <span style="background: #e8f5e9; color: #2e7d32; font-size: 0.75em; padding: 3px 8px; border-radius: 4px; display: inline-block; margin-bottom: 8px;">
                                <?php echo e($row['category']); ?>
                            </span>
                            <h3 style="font-size: 1.05em; margin-bottom: 8px;"><?php echo e($row['product_name']); ?></h3>
                            <p style="font-size: 0.9em; margin-bottom: 5px;">
                                <a href="vendor-shop.php?id=<?php echo $row['user_id']; ?>" class="text-green" style="text-decoration:none; font-weight:600;">
                                    <i class="fas fa-user-circle"></i> <?php echo e($row['farmer_name']); ?>
                                </a>
                            </p>
                            <p style="font-size: 0.85em; margin-bottom: 8px; color:#666;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo e($row['location']); ?>
                            </p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                <p class="price" style="margin: 0; font-size: 1.1em; color: #2e7d32; font-weight: 700;">
                                    ₦<?php echo number_format($row['price']); ?>
                                    <small style="font-weight: normal; color: #666;">/ <?php echo e(isset($row['unit']) ? $row['unit'] : 'unit'); ?></small>
                                </p>
                                <span style="font-size: 0.8em; color: #888;">
                                    <i class="fas fa-boxes"></i> <?php echo $stock; ?> in stock
                                </span>
                            </div>
                        </div>
                    </a>
                    <div class="product-info" style="padding: 0 15px 15px; border-top: 1px solid #f0f0f0; padding-top: 12px;">
                        <div style="display:flex; gap:10px;">
                            <?php 
                                $clean_phone = preg_replace('/[^0-9]/', '', $whatsapp);
                                if (substr($clean_phone, 0, 3) == '234') { 
                                    $wa_number = $clean_phone; 
                                } elseif (substr($clean_phone, 0, 1) == '0') { 
                                    $wa_number = '234' . substr($clean_phone, 1); 
                                } else { 
                                    $wa_number = '234' . $clean_phone; 
                                }

                                $msg = "Hello {$row['farmer_name']}, I am interested in your {$row['product_name']} listed on Dajot Marketplace for ₦" . number_format($row['price']);
                                $wa_link = "https://wa.me/{$wa_number}?text=" . urlencode($msg);
                            ?>
                            <a href="tel:<?php echo $row['phone']; ?>" class="btn btn-secondary btn-sm" style="flex:1; display:flex; align-items:center; justify-content:center; border-radius: 8px;">
                                <i class="fas fa-phone-alt" style="margin-right:5px;"></i> Call
                            </a>
                            <a href="<?php echo $wa_link; ?>" class="btn btn-sm" style="flex:1; display:flex; align-items:center; justify-content:center; background: #25D366; color: white; border-radius: 8px;" target="_blank">
                                <i class="fab fa-whatsapp" style="margin-right:5px;"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
        } else {
            ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="width: 100%; max-width: 400px; margin: 0 auto 30px;">
                    <i class="fas fa-store-slash" style="font-size: 80px; color: #ddd;"></i>
                </div>
                <h3 style="color: #333; margin-bottom: 10px;">No listings available yet.</h3>
                <p style="color: #666; margin-bottom: 30px;">Be among the first farmers to showcase your poultry products on Dajot Marketplace.</p>
                <div style="display:flex; gap: 20px; justify-content:center; flex-wrap:wrap;">
                    <a href="vendor-register.php" class="btn btn-primary btn-lg">Become a Vendor</a>
                    <a href="https://wa.me/2349034670525" class="btn btn-secondary btn-lg"><i class="fab fa-whatsapp"></i> Contact Support</a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</section>

<!-- CTA Section -->
<section style="background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); padding: 50px 0; margin-top: 20px;">
    <div class="container" style="text-align: center; color: white;">
        <h2 style="margin-bottom: 15px;">Are You a Poultry Farmer?</h2>
        <p style="opacity: 0.9; margin-bottom: 25px;">Join our marketplace and reach thousands of buyers across Nigeria</p>
        <a href="vendor-register.php" class="btn" style="background: white; color: #2e7d32; padding: 15px 35px; font-weight: 600;">
            <i class="fas fa-plus-circle"></i> Start Selling Today
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
