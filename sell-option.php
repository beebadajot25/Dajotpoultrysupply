<?php include 'includes/header.php'; ?>

<section class="section">
    <div class="container" style="text-align: center; max-width: 800px;">
        <h1 class="section-title">Start Selling with Dajot</h1>
        <p class="section-subtitle">Choose how you want to partner with us.</p>
        
        <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 50px;">
            
            <!-- Option 1: Sell Directly -->
            <div class="feature-card" style="padding: 40px;">
                <div class="feature-icon" style="background: #e3f2fd; color: #1976d2;"><i class="fas fa-handshake"></i></div>
                <h3>Sell Directly to Dajot</h3>
                <p>We buy your birds and eggs in bulk. Best for quick off-taking and large quantities.</p>
                <a href="sell-to-us.php" class="btn btn-primary" style="margin-top: 20px;">Sell to Us</a>
            </div>

            <!-- Option 2: Marketplace -->
            <div class="feature-card" style="padding: 40px;">
                <div class="feature-icon" style="background: #e8f5e9; color: #2e7d32;"><i class="fas fa-store"></i></div>
                <h3>Sell on Marketplace</h3>
                <p>List your products on our platform and connect with buyers directly. You control the price.</p>
                <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                    <a href="vendor-register.php" class="btn btn-secondary">Register as Vendor</a>
                    <!-- <a href="vendor-login.php" style="color: #666; font-size: 0.9em;">Already have an account? Login</a> -->
                </div>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
