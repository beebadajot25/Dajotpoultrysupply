<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/paystack.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: vendor-login.php?msg=login_required");
    exit;
}

if (!isset($_GET['plan'])) {
    header("Location: pricing.php");
    exit;
}

$plan = strtolower($_GET['plan']);
$valid_plans = ['pro' => 5000, 'gold' => 12000];

if (!array_key_exists($plan, $valid_plans)) {
    die("Invalid plan selected.");
}

$amount = $valid_plans[$plan];
$email = $_SESSION['email'] ?? 'customer@example.com'; // Should ensure email is in session
// Retrieve email from DB if not in session
if (!isset($_SESSION['email'])) {
    $uid = $_SESSION['user_id'];
    $u = $conn->query("SELECT email FROM users WHERE id = $uid")->fetch_assoc();
    $email = $u['email'];
}

$reference = 'DJT-' . strtoupper(uniqid()); 
$callback_url = "http://localhost/dajotpoultrysupply/payment-verify.php"; 

// Create pending subscription record
// We need a 'subscriptions' table. Let's assume it exists or create it.
// Phase 3 needs migrations too.

$user_id = $_SESSION['user_id'];
// Check for tables existence (lazy migration for demo)
$conn->query("CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan, amount, reference, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("isds", $user_id, $plan, $amount, $reference);
$stmt->execute();

// Initialize Paystack
$result = Paystack::initialize($email, $amount, $reference, $callback_url);

if ($result && $result['status']) {
    // Redirect to Paystack
    header("Location: " . $result['data']['authorization_url']);
    exit;
} else {
    echo "<h2>Error Initializing Payment</h2>";
    echo "<p>" . ($result['message'] ?? 'Unknown error') . "</p>";
    echo "<a href='pricing.php'>Back to Pricing</a>";
}
?>
