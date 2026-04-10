<?php
require_once __DIR__ . '/db.php';
if (!isset($_SESSION)) session_start();
// Get the current page filename to set active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dajot Poultry Supply | Buy & Sell Poultry</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Top Bar Removed as per request -->

<!-- Main Header -->
<header>
    <nav class="main-nav">
        <div class="container <?php echo ($current_page !== 'index.php') ? 'sub-page-header' : ''; ?> header-grid">
            <div class="header-col-left">
                <a href="index.php" class="logo">
                    <img src="assets/images/logo.png" alt="Dajot Poultry Supply">
                </a>
                <?php if($current_page !== 'index.php'): ?>
                    <a href="index.php" class="mobile-back-btn">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="header-col-right">
                <?php if($current_page == 'index.php'): ?>
                    <div class="hamburger">
                        <i class="fas fa-bars"></i>
                    </div>
                <?php else: ?>
                    <div class="mobile-spacer"></div>
                <?php endif; ?>
            </div>

            <div class="nav-links">
                <?php if($current_page == 'index.php'): ?>
                    <a href="index.php" class="active">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="shop.php">Shop Products</a>
                    <a href="marketplace.php">Marketplace</a>
                    <a href="sell-option.php">Sell with Dajot</a>
                    <a href="contact.php">Contact</a>
                <?php else: ?>
                    <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="includes/logout.php" class="btn btn-outline btn-sm" style="margin-left:10px; padding: 5px 15px;">Logout</a>
                <?php else: ?>
                    <a href="vendor-login.php" class="btn btn-outline btn-sm" style="margin-left:10px; padding: 5px 15px;">Login</a>
                    <?php if($current_page == 'index.php'): ?>
                        <a href="vendor-register.php" class="btn btn-primary btn-sm" style="margin-left:10px; padding: 5px 15px;">Register</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            

        </div>
    </nav>
</header>
