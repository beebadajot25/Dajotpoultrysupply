<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/db.php';

echo "<h1>Database Setup & Repair Tool</h1>";
echo "<hr>";

// ==========================================
// 1. ADD COLUMNS (user_id to listings)
// ==========================================
echo "<h3>1. Checking Listings Table Structure...</h3>";
$sql = "SHOW COLUMNS FROM listings LIKE 'user_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alter_sql = "ALTER TABLE listings ADD COLUMN user_id INT(11) DEFAULT NULL AFTER id";
    if ($conn->query($alter_sql) === TRUE) {
        echo "<span style='color:green'>Success: user_id column added to listings table.</span><br>";
    } else {
        echo "<span style='color:red'>Error adding column: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span style='color:blue'>Column user_id already exists. OK.</span><br>";
}

// ==========================================
// 2. SUBSCRIPTION TABLES
// ==========================================
echo "<h3>2. Checking Subscription Tables...</h3>";

// Plans Table
$sql_plans = "CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT(11) DEFAULT 30,
    listing_limit INT(11) DEFAULT 0,
    description TEXT
)";

if ($conn->query($sql_plans) === TRUE) {
    echo "Table 'subscription_plans' check passed.<br>";
} else {
    echo "<span style='color:red'>Error 'subscription_plans': " . $conn->error . "</span><br>";
}

// Insert Default Plans
$check = $conn->query("SELECT count(*) as total FROM subscription_plans");
$row = $check->fetch_assoc();
if ($row['total'] == 0) {
    $insert_plans = "INSERT INTO subscription_plans (name, price, duration_days, listing_limit, description) VALUES 
    ('Free', 0.00, 30, 1, 'Starter plan for new vendors'),
    ('Standard', 2000.00, 30, 10, 'Perfect for growing farms'),
    ('Premium', 5000.00, 30, 999999, 'Unlimited listings for large suppliers')";
    
    if ($conn->query($insert_plans) === TRUE) {
        echo "<span style='color:green'>Default plans inserted.</span><br>";
    } else {
        echo "<span style='color:red'>Error inserting plans: " . $conn->error . "</span><br>";
    }
}

// User Subscriptions Table
$sql_subs = "CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    plan_id INT(11) NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    status ENUM('active', 'expired') DEFAULT 'active',
    reference VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
)";

if ($conn->query($sql_subs) === TRUE) {
    echo "Table 'user_subscriptions' check passed.<br>";
} else {
    echo "<span style='color:red'>Error 'user_subscriptions': " . $conn->error . "</span><br>";
}

// ==========================================
// 3. ADMIN PASSWORD REPAIR
// ==========================================
echo "<h3>3. Verifying Admin Access...</h3>";
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$check_sql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Check if password matches 'admin123'
    if (!password_verify($password, $row['password'])) {
        echo "Updating Admin password hash...<br>";
        $stmt = $conn->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $username);
        $stmt->execute();
        $stmt->close();
        echo "<span style='color:green'>Admin password repaired.</span><br>";
    } else {
         echo "<span style='color:blue'>Admin password is correct. OK.</span><br>";
    }
} else {
    echo "Creating Admin user...<br>";
    $email = 'admin@dajot.com';
    $role = 'admin';
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
    $stmt->execute();
    $stmt->close();
    echo "<span style='color:green'>Admin user created.</span><br>";
}

echo "<hr><h1>SETUP COMPLETE</h1>";
echo "<p>You can now <a href='index.php'>Return to Home</a> or <a href='vendor-register.php'>Try Registering again</a>.</p>";
?>
