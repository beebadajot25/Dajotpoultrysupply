<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: vendor-login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// In a real Paystack flow, we would verify the reference $_GET['reference'] via API
// For this demo, we simulate success if POSTed from pricing.php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['plan_id'])) {
    $plan_id = intval($_POST['plan_id']);
    
    // Calculate dates
    $start_date = date('Y-m-d H:i:s');
    $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    $status = 'active';
    $reference = 'SIM_' . uniqid(); // Simulated reference

    // Deactivate previous subscriptions
    $conn->query("UPDATE user_subscriptions SET status='expired' WHERE user_id='$user_id'");

    // Insert new subscription
    $stmt = $conn->prepare("INSERT INTO user_subscriptions (user_id, plan_id, start_date, end_date, status, reference) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $user_id, $plan_id, $start_date, $end_date, $status, $reference);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Subscription upgraded successfully!";
        header("Location: vendor-dashboard.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();

} else {
    // If accessed directly or invalid
    header("Location: pricing.php");
    exit;
}
?>
