<?php
session_start();
include 'includes/db.php';
$current_page = 'pricing.php';

// Check if vendor is logged in to show "Upgrade" instead of "Get Started"
$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['role'] == 'farmer';

// Get current plan if logged in
$current_plan = 'free';
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $user_q = $conn->query("SELECT plan FROM users WHERE id = $user_id");
    if ($user_q && $user_data = $user_q->fetch_assoc()) {
        $current_plan = $user_data['plan'] ?? 'free';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans - Dajot Poultry Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .pricing-section { padding: 80px 0; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); }
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-top: 50px; }
        .pricing-card { background: white; padding: 40px; border-radius: 20px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.08); transition: all 0.3s ease; position: relative; border: 2px solid transparent; }
        .pricing-card:hover { transform: translateY(-8px); box-shadow: 0 15px 50px rgba(0,0,0,0.12); }
        .pricing-card.popular { border-color: #2e7d32; background: linear-gradient(180deg, #ffffff 0%, #f0fff4 100%); }
        .badge-popular { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(90deg, #2e7d32, #48bb78); color: white; padding: 8px 25px; border-radius: 25px; font-size: 0.85em; font-weight: bold; box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3); }
        
        .plan-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.8em; }
        .plan-icon.free { background: #edf2f7; color: #718096; }
        .plan-icon.pro { background: #e8f5e9; color: #2e7d32; }
        .plan-icon.gold { background: #fef3c7; color: #d69e2e; }
        
        .plan-name { font-size: 1.6em; font-weight: bold; color: #1a202c; margin-bottom: 5px; }
        .plan-desc { font-size: 0.9em; color: #718096; margin-bottom: 20px; }
        .plan-price { font-size: 3.5em; font-weight: 800; color: #2d3748; margin-bottom: 10px; line-height: 1; }
        .plan-price span { font-size: 0.35em; color: #718096; font-weight: 500; }
        
        .plan-features { list-style: none; padding: 0; margin: 30px 0; text-align: left; }
        .plan-features li { margin-bottom: 15px; color: #4a5568; display: flex; align-items: center; font-size: 0.95em; }
        .plan-features li i { width: 22px; margin-right: 12px; font-size: 1.1em; }
        .plan-features li i.fa-check-circle { color: #48bb78; }
        .plan-features li i.fa-times-circle { color: #cbd5e0; }
        .plan-features li.disabled { color: #a0aec0; }
        
        .btn-pricing { display: block; width: 100%; padding: 16px; border-radius: 12px; font-weight: bold; text-decoration: none; transition: all 0.3s; font-size: 1em; }
        .btn-free { background: #edf2f7; color: #4a5568; }
        .btn-free:hover { background: #e2e8f0; }
        .btn-pro { background: linear-gradient(90deg, #2e7d32, #48bb78); color: white; box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3); }
        .btn-pro:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46, 125, 50, 0.4); }
        .btn-gold { background: linear-gradient(90deg, #1a202c, #2d3748); color: white; }
        .btn-gold:hover { transform: translateY(-2px); }
        .btn-current { background: #c6f6d5; color: #22543d; cursor: default; }
        
        .comparison-section { padding: 60px 0; background: #fff; }
        .comparison-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .comparison-table th, .comparison-table td { padding: 15px 20px; text-align: center; border-bottom: 1px solid #eee; }
        .comparison-table th { background: #f8fafc; font-weight: 600; color: #4a5568; }
        .comparison-table td:first-child { text-align: left; font-weight: 500; }
        .comparison-table tr:hover { background: #f8fafc; }
        .check-yes { color: #48bb78; font-size: 1.2em; }
        .check-no { color: #cbd5e0; }
        
        @media (max-width: 768px) {
            .pricing-grid { gap: 20px; }
            .pricing-card { padding: 30px 25px; }
            .plan-price { font-size: 2.8em; }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="pricing-section">
    <div class="container">
        <div class="text-center">
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'limit_reached'): ?>
                <div style="background: linear-gradient(90deg, #fff5f5, #fed7d7); color: #c53030; padding: 18px 25px; border-radius: 12px; border-left: 5px solid #c53030; margin-bottom: 30px; display: inline-block;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Limit Reached:</strong> You have used all your free listings. Upgrade to continue selling.
                </div>
            <?php endif; ?>
            <h1 class="section-title" style="font-size: 2.5em; margin-bottom: 15px;">Scale Your Poultry Business</h1>
            <p class="section-subtitle" style="max-width: 600px; margin: 0 auto;">Choose a plan that fits your farm's growth. Connect with bulk buyers across Nigeria.</p>
        </div>

        <div class="pricing-grid">
            <!-- Starter Plan -->
            <div class="pricing-card">
                <div class="plan-icon free"><i class="fas fa-seedling"></i></div>
                <div class="plan-name">Starter</div>
                <div class="plan-desc">Perfect for trying out the marketplace</div>
                <div class="plan-price">₦0<span>/forever</span></div>
                <ul class="plan-features">
                    <li><i class="fas fa-check-circle"></i> 3 Active Listings</li>
                    <li><i class="fas fa-check-circle"></i> 2 Images per Listing</li>
                    <li><i class="fas fa-check-circle"></i> WhatsApp Integration</li>
                    <li><i class="fas fa-check-circle"></i> Standard Placement</li>
                    <li class="disabled"><i class="fas fa-times-circle"></i> Listing Boost</li>
                    <li class="disabled"><i class="fas fa-times-circle"></i> Verified Badge</li>
                    <li class="disabled"><i class="fas fa-times-circle"></i> Analytics Dashboard</li>
                </ul>
                <?php if($current_plan == 'free'): ?>
                    <span class="btn-pricing btn-current"><i class="fas fa-check"></i> Current Plan</span>
                <?php elseif($is_logged_in): ?>
                    <span class="btn-pricing btn-free">Downgrade</span>
                <?php else: ?>
                    <a href="vendor-register.php" class="btn-pricing btn-free">Get Started Free</a>
                <?php endif; ?>
            </div>

            <!-- Pro Plan -->
            <div class="pricing-card popular">
                <div class="badge-popular"><i class="fas fa-star"></i> Most Popular</div>
                <div class="plan-icon pro"><i class="fas fa-rocket"></i></div>
                <div class="plan-name">Poultry Pro</div>
                <div class="plan-desc">For serious farmers growing their business</div>
                <div class="plan-price">₦5,000<span>/month</span></div>
                <ul class="plan-features">
                    <li><i class="fas fa-check-circle"></i> <strong>50 Active Listings</strong></li>
                    <li><i class="fas fa-check-circle"></i> 5 Images per Listing</li>
                    <li><i class="fas fa-check-circle"></i> 1 Listing Boost / Month</li>
                    <li><i class="fas fa-check-circle"></i> <strong>✅ Verified Seller Badge</strong></li>
                    <li><i class="fas fa-check-circle"></i> Basic Analytics Dashboard</li>
                    <li><i class="fas fa-check-circle"></i> Priority WhatsApp Support</li>
                    <li class="disabled"><i class="fas fa-times-circle"></i> Bulk CSV Upload</li>
                </ul>
                <?php if($current_plan == 'pro'): ?>
                    <span class="btn-pricing btn-current"><i class="fas fa-check"></i> Current Plan</span>
                <?php else: ?>
                    <a href="<?php echo $is_logged_in ? 'subscribe.php?plan=pro' : 'vendor-register.php?plan=pro'; ?>" class="btn-pricing btn-pro"><?php echo $is_logged_in ? 'Upgrade to Pro' : 'Join Pro'; ?></a>
                <?php endif; ?>
            </div>

            <!-- Gold Plan -->
            <div class="pricing-card">
                <div class="plan-icon gold"><i class="fas fa-crown"></i></div>
                <div class="plan-name">Farm Gold</div>
                <div class="plan-desc">For large farms & cooperatives</div>
                <div class="plan-price">₦15,000<span>/month</span></div>
                <ul class="plan-features">
                    <li><i class="fas fa-check-circle"></i> <strong>Unlimited Listings</strong></li>
                    <li><i class="fas fa-check-circle"></i> 10 Images per Listing</li>
                    <li><i class="fas fa-check-circle"></i> 5 Listing Boosts / Month</li>
                    <li><i class="fas fa-check-circle"></i> <strong>⭐ Trusted Seller Badge</strong></li>
                    <li><i class="fas fa-check-circle"></i> Advanced Analytics</li>
                    <li><i class="fas fa-check-circle"></i> Bulk CSV Upload</li>
                    <li><i class="fas fa-check-circle"></i> Dedicated Account Manager</li>
                </ul>
                <?php if($current_plan == 'gold'): ?>
                    <span class="btn-pricing btn-current"><i class="fas fa-check"></i> Current Plan</span>
                <?php else: ?>
                    <a href="<?php echo $is_logged_in ? 'subscribe.php?plan=gold' : 'vendor-register.php?plan=gold'; ?>" class="btn-pricing btn-gold"><?php echo $is_logged_in ? 'Go Unlimited' : 'Join Gold'; ?></a>
                <?php endif; ?>
            </div>
        </div>
        
        <p class="text-center" style="margin-top: 40px; color: #718096;">
            Need a custom enterprise plan for large cooperatives? <a href="contact.php" style="color: #2e7d32; font-weight: 600;">Contact Us</a>
        </p>
    </div>
</section>

<!-- Feature Comparison Table -->
<section class="comparison-section">
    <div class="container">
        <h2 class="section-title text-center">Compare All Features</h2>
        
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Starter</th>
                    <th style="background: #f0fff4;">Pro</th>
                    <th>Gold</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Active Listings</td>
                    <td>3</td>
                    <td style="background: #f0fff4;">50</td>
                    <td>Unlimited</td>
                </tr>
                <tr>
                    <td>Images per Listing</td>
                    <td>2</td>
                    <td style="background: #f0fff4;">5</td>
                    <td>10</td>
                </tr>
                <tr>
                    <td>Listing Boost</td>
                    <td><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td style="background: #f0fff4;">1/month</td>
                    <td>5/month</td>
                </tr>
                <tr>
                    <td>Verified Badge</td>
                    <td><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td style="background: #f0fff4;"><span class="check-yes"><i class="fas fa-check"></i></span></td>
                    <td><span class="check-yes"><i class="fas fa-check"></i></span></td>
                </tr>
                <tr>
                    <td>Analytics Dashboard</td>
                    <td><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td style="background: #f0fff4;">Basic</td>
                    <td>Advanced</td>
                </tr>
                <tr>
                    <td>Priority Support</td>
                    <td><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td style="background: #f0fff4;"><span class="check-yes"><i class="fas fa-check"></i></span></td>
                    <td><span class="check-yes"><i class="fas fa-check"></i></span></td>
                </tr>
                <tr>
                    <td>Bulk CSV Upload</td>
                    <td><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td style="background: #f0fff4;"><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td><span class="check-yes"><i class="fas fa-check"></i></span></td>
                </tr>
                <tr>
                    <td>Account Manager</td>
                    <td><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td style="background: #f0fff4;"><span class="check-no"><i class="fas fa-times"></i></span></td>
                    <td><span class="check-yes"><i class="fas fa-check"></i></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<!-- FAQ Section -->
<section class="section" style="background: #f8fafc;">
    <div class="container" style="max-width: 800px;">
        <h2 class="section-title text-center">Frequently Asked Questions</h2>
        
        <div style="margin-top: 40px;">
            <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h4 style="margin-bottom: 10px; color: #2e7d32;"><i class="fas fa-question-circle"></i> How do I upgrade my plan?</h4>
                <p style="color: #666; margin: 0;">Click on any upgrade button and you'll be directed to our secure payment page. Once payment is confirmed, your plan is upgraded instantly.</p>
            </div>
            <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h4 style="margin-bottom: 10px; color: #2e7d32;"><i class="fas fa-question-circle"></i> Can I cancel anytime?</h4>
                <p style="color: #666; margin: 0;">Yes! You can cancel your subscription anytime. Your plan stays active until the end of your billing period.</p>
            </div>
            <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h4 style="margin-bottom: 10px; color: #2e7d32;"><i class="fas fa-question-circle"></i> What's a Listing Boost?</h4>
                <p style="color: #666; margin: 0;">A boost puts your listing at the top of search results for 7 days, giving it maximum visibility to buyers.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
