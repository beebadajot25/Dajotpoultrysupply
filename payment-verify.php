<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/paystack.php';

if (!isset($_GET['reference'])) {
    die("No reference supplied");
}

$reference = $_GET['reference'];

// Verify with Paystack
$result = Paystack::verify($reference);

if ($result && $result['status'] && $result['data']['status'] == 'success') {
    // Payment Successful
    
    // 1. Update Subscription Record
    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'success' WHERE reference = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    
    // 2. Update User Plan
    // Get plan details from subscription
    $sub_q = $conn->query("SELECT * FROM subscriptions WHERE reference = '$reference'");
    $sub = $sub_q->fetch_assoc();
    
    if ($sub) {
        $user_id = $sub['user_id'];
        $plan = $sub['plan'];
        
        // Calculate Expiry (30 days from now)
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $upd = $conn->prepare("UPDATE users SET plan = ?, plan_expires = ? WHERE id = ?");
        $upd->bind_param("ssi", $plan, $expiry, $user_id);
        
        if ($upd->execute()) {
            
            // Send Notification
            require_once 'includes/notifications.php';
            $user_email = $_SESSION['email'] ?? 'user@example.com'; // In real app, fetch from DB
            $user_phone = '08000000000'; // Fetch from DB
            
            NotificationSystem::sendEmail($user_email, "Plan Upgraded", "You are now on the $plan plan!");
            NotificationSystem::sendSMS($user_phone, "Dajot Market: Plan upgraded to " . ucfirst($plan));
            
            $_SESSION['flash_message'] = "Payment successful! You are now on the " . ucfirst($plan) . " plan.";
            header("Location: vendor-dashboard.php?msg=upgrade_success");
            exit;
        } else {
            echo "Error updating user profile.";
        }
    } else {
        echo "Subscription record not found.";
    }
    
} else {
    // Payment Failed
    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'failed' WHERE reference = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    
    echo "<h2>Payment Validation Failed</h2>";
    echo "<p>" . ($result['message'] ?? 'Unknown error') . "</p>";
    echo "<a href='pricing.php'>Try Again</a>";
}
?>
