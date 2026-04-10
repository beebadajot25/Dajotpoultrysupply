<?php
include 'includes/db.php';

// 1. Create Plans Table
$sql_plans = "CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT(11) DEFAULT 30,
    listing_limit INT(11) DEFAULT 0, -- 0 for unlimited? Or use -1
    description TEXT
)";

if ($conn->query($sql_plans) === TRUE) {
    echo "Table 'subscription_plans' created/exists.<br>";
} else {
    echo "Error creating table 'subscription_plans': " . $conn->error . "<br>";
}

// 2. Insert Default Plans
// Check if plans exist
$check = $conn->query("SELECT count(*) as total FROM subscription_plans");
$row = $check->fetch_assoc();
if ($row['total'] == 0) {
    $insert_plans = "INSERT INTO subscription_plans (name, price, duration_days, listing_limit, description) VALUES 
    ('Free', 0.00, 30, 1, 'Starter plan for new vendors'),
    ('Standard', 2000.00, 30, 10, 'Perfect for growing farms'),
    ('Premium', 5000.00, 30, 999999, 'Unlimited listings for large suppliers')";
    
    if ($conn->query($insert_plans) === TRUE) {
        echo "Default plans inserted.<br>";
    } else {
        echo "Error inserting plans: " . $conn->error . "<br>";
    }
}

// 3. Create User Subscriptions Table
$sql_subs = "CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    plan_id INT(11) NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    status ENUM('active', 'expired') DEFAULT 'active',
    reference VARCHAR(100), -- Paystack Reference
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
)";

if ($conn->query($sql_subs) === TRUE) {
    echo "Table 'user_subscriptions' created/exists.<br>";
} else {
    echo "Error creating table 'user_subscriptions': " . $conn->error . "<br>";
}

echo "Migration Complete.";
?>
