<?php
require_once 'includes/security.php';
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: vendor-login.php");
    exit;
}

$plan = $_GET['plan'] ?? 'pro';
$prices = ['pro' => 5000, 'gold' => 12000];
$price = $prices[$plan] ?? 5000;

// Simulation Mode
if (isset($_POST['simulate_payment'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        $user_id = $_SESSION['user_id'];
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $conn->prepare("UPDATE users SET plan = ?, plan_expires = ? WHERE id = ?");
    $stmt->bind_param("ssi", $plan, $expires, $user_id);
    
    if ($stmt->execute()) {
        header("Location: vendor-dashboard.php?msg=upgrade_success");
        exit;
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Dajot</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-box { max-width: 500px; margin: 100px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .price-tag { font-size: 2.5em; font-weight: bold; color: #2e7d32; margin: 20px 0; }
    </style>
</head>
<body style="background: #f4f7f6;">
    <div class="checkout-box">
        <h2>Complete Your Upgrade</h2>
        <p>You are upgrading to the <strong><?php echo ucfirst($plan); ?> Plan</strong></p>
        <div class="price-tag">₦<?php echo number_format($price); ?></div>
        
        <div style="background: #e6fffa; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9em; color: #2c7a7b;">
            <i class="fas fa-info-circle"></i> This is a payment simulation for testing.
        </div>
        
        <form method="POST">
            <?php csrf_input(); ?>
            <button type="submit" name="simulate_payment" class="btn btn-primary btn-block" style="padding: 15px;">
                Secure Pay with Paystack (Simulated)
            </button>
        </form>
        <br>
        <a href="pricing.php" style="color: #718096; text-decoration: none;">Cancel</a>
    </div>
</body>
</html>
