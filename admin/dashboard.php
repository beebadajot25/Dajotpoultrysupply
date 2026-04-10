<?php
session_start();
// Simple session check (In production, use a robust login system)
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit; }

require_once '../includes/db.php';

// Handle Actions (Approve/Reject/Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM listings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action == 'delete_report') {
        $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action == 'publish_report') {
        $stmt = $conn->prepare("UPDATE reports SET status = 'reviewed' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        
        // Debug: Check if update was successful
        if (!$result) {
            error_log("Failed to publish report ID: $id. Error: " . $stmt->error);
        } else {
            error_log("Successfully published report ID: $id. Affected rows: " . $stmt->affected_rows);
        }
    } elseif ($action == 'delete_product') {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action == 'unpublish_report') {
        $stmt = $conn->prepare("UPDATE reports SET status = 'pending' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action == 'verify_user') {
        $level = $_GET['level']; // 'basic', 'verified', 'trusted'
        $stmt = $conn->prepare("UPDATE users SET verification_level = ? WHERE id = ?");
        $stmt->bind_param("si", $level, $id);
        $stmt->execute();
        
        // Return to Verifications view
        header("Location: dashboard.php?view=verifications");
        exit;
    } else {
        $status = ($action == 'approve') ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE listings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
    
    // Redirect to remove query params, but preserve the view if it's reports
    if (in_array($action, ['publish_report', 'unpublish_report', 'delete_report'])) {
        header("Location: dashboard.php?view=reports");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

// Fetch Stats Helper Function
function getStat($conn, $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        return $row ? $row[0] : 0;
    }
    return false; // Return false on failure (e.g. table doesn't exist)
}

$pending_count = getStat($conn, "SELECT COUNT(*) FROM listings WHERE status = 'pending'");
$farmers_count = getStat($conn, "SELECT COUNT(*) FROM users WHERE role = 'farmer'"); 
$products_count = getStat($conn, "SELECT COUNT(*) FROM products");
$pending_verifications = getStat($conn, "SELECT COUNT(*) FROM verification_requests WHERE status = 'pending'");

// Additional Stats
$pro_count = getStat($conn, "SELECT COUNT(*) FROM users WHERE plan = 'pro'");
$gold_count = getStat($conn, "SELECT COUNT(*) FROM users WHERE plan = 'gold'");
$total_revenue = getStat($conn, "SELECT SUM(amount) FROM subscriptions WHERE status = 'success'");
$total_revenue = $total_revenue ? $total_revenue : 0;

// If any query failed, it likely means tables are missing
$db_error = false;
if ($pending_count === false || $farmers_count === false || $products_count === false) {
    $db_error = true;
    $pending_count = $farmers_count = $products_count = 0;
}


// Fetch Recent Listings
$recent_listings = [];
$result = $conn->query("SELECT * FROM listings ORDER BY created_at DESC LIMIT 10");
if ($result) {
    while($row = $result->fetch_assoc()) {
        $recent_listings[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dajot Poultry</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #2d3748; color: #fff; padding: 20px; }
        .sidebar h2 { margin-bottom: 40px; color: var(--primary-orange); }
        .sidebar a { display: block; color: #cbd5e0; padding: 12px 15px; margin-bottom: 5px; border-radius: 6px; }
        .sidebar a:hover, .sidebar a.active { background: #4a5568; color: #fff; }
        .main-content { flex: 1; background: #f7fafc; padding: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; }
        .stat-card h3 { font-size: 2em; margin-bottom: 5px; }
        .stat-card p { color: #718096; }
        .stat-card i { font-size: 2.5em; opacity: 0.2; }
        table { width: 100%; background: #fff; border-radius: 8px; border-collapse: collapse; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; color: #4a5568; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 600; }
        .badge.pending { background: #FEFCBF; color: #744210; }
        .badge.approved { background: #C6F6D5; color: #22543D; }
        .badge.rejected { background: #FED7D7; color: #822727; }
    </style>
</head>
<body>

<div class="admin-layout">
    <div class="sidebar">
        <h2>Dajot Admin</h2>
        <a href="dashboard.php" class="<?php echo (!isset($_GET['view'])) ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="dashboard.php?view=products" class="<?php echo (isset($_GET['view']) && $_GET['view'] == 'products') ? 'active' : ''; ?>"><i class="fas fa-box"></i> Dajot Products</a>
        <a href="dashboard.php?view=farmers" class="<?php echo (isset($_GET['view']) && $_GET['view'] == 'farmers') ? 'active' : ''; ?>"><i class="fas fa-users"></i> Farmers</a>
        <a href="dashboard.php?view=reports" class="<?php echo (isset($_GET['view']) && $_GET['view'] == 'reports') ? 'active' : ''; ?>"><i class="fas fa-shield-alt"></i> Trust Center</a>
        <a href="dashboard.php?view=verifications" class="<?php echo (isset($_GET['view']) && $_GET['view'] == 'verifications') ? 'active' : ''; ?>"><i class="fas fa-user-check"></i> Verifications</a>
        <a href="../index.php" style="margin-top: auto;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <header style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <h1>
                <?php 
                $view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';
                echo ucfirst($view) . ' Overview';
                ?>
            </h1>
            <div class="user-profile">
                <span>Welcome, Admin</span>
            </div>
        </header>

        <?php if ($view == 'dashboard'): ?>
            <!-- DASHBOARD HOME -->
            <div class="stats-grid">
                <a href="dashboard.php?view=pending" class="stat-card" style="text-decoration:none; color:inherit; transition: transform 0.2s;">
                    <div>
                        <h3><?php echo $pending_count; ?></h3>
                        <p>Pending Listings</p>
                    </div>
                    <i class="fas fa-clipboard-list text-orange"></i>
                </a>
                <a href="dashboard.php?view=farmers" class="stat-card" style="text-decoration:none; color:inherit; transition: transform 0.2s;">
                    <div>
                        <h3><?php echo $farmers_count; ?></h3>
                        <p>Registered Farmers</p>
                    </div>
                    <i class="fas fa-users text-green"></i>
                </a>
                <a href="dashboard.php?view=products" class="stat-card" style="text-decoration:none; color:inherit; transition: transform 0.2s;">
                    <div>
                        <h3><?php echo $products_count; ?></h3>
                        <p>Dajot Products</p>
                    </div>
                    <i class="fas fa-box"></i>
                </a>
                <a href="dashboard.php?view=revenue" class="stat-card" style="text-decoration:none; color:inherit; transition: transform 0.2s;">
                    <div>
                        <h3>₦<?php echo number_format($total_revenue); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                    <i class="fas fa-coins" style="color:#ecc94b;"></i>
                </a>
                <a href="dashboard.php?view=verifications" class="stat-card" style="text-decoration:none; color:inherit; transition: transform 0.2s;">
                    <div>
                        <h3><?php echo $pending_verifications; ?></h3>
                        <p>Pending Verifications</p>
                    </div>
                    <i class="fas fa-user-check" style="color:#2e7d32;"></i>
                </a>
            </div>

            <h2 style="margin-bottom: 15px;">Recent Marketplace Submissions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Farmer</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_listings)): ?>
                        <?php foreach($recent_listings as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['product_name']); ?></strong><br>
                                <small class="text-gray"><?php echo htmlspecialchars($row['location']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['farmer_name']); ?><br>
                                <small><?php echo htmlspecialchars($row['phone']); ?></small>
                            </td>
                            <td>₦<?php echo number_format($row['price']); ?></td>
                            <td><span class="badge <?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                <a href="dashboard.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary" style="margin-right: 5px;">Approve</a>
                                <a href="dashboard.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-sm" style="background: #e53e3e; color: white;">Reject</a>
                                <?php else: ?>
                                <a href="dashboard.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm" style="background: #718096; color: white;" onclick="return confirm('Are you sure you want to permanently delete this listing?');"><i class="fas fa-trash"></i> Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No listings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        
        <?php elseif ($view == 'farmers'): ?>
            <!-- FARMERS LIST -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Info</th>
                            <th>Plan</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM users WHERE role='farmer'");
                        if ($res && $res->num_rows > 0) {
                            while($r = $res->fetch_assoc()) {
                                $plan = strtoupper($r['plan'] ?? 'FREE');
                                $plan_class = ($plan == 'PRO') ? 'approved' : (($plan == 'GOLD') ? 'badge' : 'pending');
                                // Special badge color for Gold (gold/yellow)
                                $gold_style = ($plan == 'GOLD') ? 'background:#ecc94b; color:#333;' : '';
                                
                                echo "<tr>
                                    <td>#{$r['id']}</td>
                                    <td>
                                        <strong>" . e($r['username']) . "</strong><br>
                                        <small>" . e($r['email']) . "</small>
                                    </td>
                                    <td>
                                        <i class='fas fa-map-marker-alt'></i> " . ($r['location'] ?? 'N/A') . "<br>
                                        <i class='fas fa-phone'></i> " . ($r['phone'] ?? 'N/A') . "
                                    </td>
                                    <td><span class='badge $plan_class' style='$gold_style'>$plan</span></td>
                                    <td><span class='badge approved'>Farmer</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No farmers registered yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($view == 'products'): ?>
            <!-- DAJOT PRODUCTS MANAGEMENT -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2>Dajot Products (<?php echo $products_count; ?>)</h2>
                    <a href="add-product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Product</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM products ORDER BY id DESC");
                        if ($res && $res->num_rows > 0) {
                            while($r = $res->fetch_assoc()) {
                                $img = !empty($r['image']) ? "../" . $r['image'] : '../assets/images/logo.png';
                                $stock = $r['stock'] ?? 0;
                                $stock_class = ($stock > 10) ? 'approved' : (($stock > 0) ? 'pending' : 'rejected');
                                echo "<tr>
                                    <td><img src='$img' style='width:50px; height:50px; object-fit:cover; border-radius:4px;'></td>
                                    <td><strong>" . e($r['name']) . "</strong><br><small>" . e(substr($r['description'], 0, 40)) . "...</small></td>
                                    <td>" . e($r['category']) . "</td>
                                    <td>₦" . number_format($r['price']) . " / {$r['price_unit']}</td>
                                    <td><span class='badge $stock_class'>$stock units</span></td>
                                    <td>
                                        <a href='edit-product.php?id={$r['id']}' class='btn btn-sm' style='background:#4299e1; color:white;'><i class='fas fa-edit'></i></a>
                                        <a href='?action=delete_product&id={$r['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Delete this product?')\"><i class='fas fa-trash'></i></a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:40px; color:#999;'>
                                <i class='fas fa-box-open' style='font-size:48px; opacity:0.3;'></i><br><br>
                                No products yet. Click 'Add New Product' to get started!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($view == 'pending'): ?>
            <!-- PENDING LISTINGS -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h2>Pending Marketplace Listings (<?php echo $pending_count; ?>)</h2>
                <table style="margin-top:20px;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Farmer</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT l.*, u.username, u.phone FROM listings l LEFT JOIN users u ON l.user_id = u.id WHERE l.status = 'pending' ORDER BY l.created_at DESC");
                        if ($res && $res->num_rows > 0) {
                            while($item = $res->fetch_assoc()) {
                                echo "<tr>
                                    <td><strong>" . htmlspecialchars($item['product_name']) . "</strong><br><small>" . htmlspecialchars($item['category']) . "</small></td>
                                    <td>" . e($item['username']) . "<br><small>" . e($item['phone']) . "</small></td>
                                    <td>₦" . number_format($item['price']) . "</td>
                                    <td>" . date('M d, Y', strtotime($item['created_at'])) . "</td>
                                    <td>
                                        <a href='dashboard.php?action=approve&id={$item['id']}' class='btn btn-success btn-sm'><i class='fas fa-check'></i> Approve</a>
                                        <a href='dashboard.php?action=reject&id={$item['id']}' class='btn btn-danger btn-sm'><i class='fas fa-times'></i> Reject</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding:40px; color:#999;'>
                                <i class='fas fa-check-circle' style='font-size:48px; opacity:0.3;'></i><br><br>
                                No pending listings! All caught up.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($view == 'subscriptions'): ?>
            <!-- PAID SUBSCRIPTIONS -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h2>Paid Subscriptions (<?php echo $pro_count + $gold_count; ?>)</h2>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:20px 0;">
                    <div style="background:#f0fff4; padding:20px; border-radius:8px; border-left:4px solid #48bb78;">
                        <h3><?php echo $pro_count; ?></h3>
                        <p>Pro Members (₦5,000/month)</p>
                    </div>
                    <div style="background:#fef5e7; padding:20px; border-radius:8px; border-left:4px solid #ecc94b;">
                        <h3><?php echo $gold_count; ?></h3>
                        <p>Gold Members (₦12,000/month)</p>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Farmer</th>
                            <th>Plan</th>
                            <th>Expires</th>
                            <th>Listings</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM listings WHERE user_id = u.id) as listing_count FROM users u WHERE u.plan IN ('pro', 'gold') ORDER BY u.plan_expires DESC");
                        if ($res && $res->num_rows > 0) {
                            while($u = $res->fetch_assoc()) {
                                $plan = strtoupper($u['plan']);
                                $plan_class = ($plan == 'GOLD') ? 'badge' : 'approved';
                                $gold_style = ($plan == 'GOLD') ? 'background:#ecc94b; color:#333;' : '';
                                $expires = $u['plan_expires'] ? date('M d, Y', strtotime($u['plan_expires'])) : 'Never';
                                $status_class = (strtotime($u['plan_expires']) > time()) ? 'approved' : 'rejected';
                                $status_text = (strtotime($u['plan_expires']) > time()) ? 'Active' : 'Expired';
                                
                                echo "<tr>
                                    <td><strong>" . e($u['username']) . "</strong><br><small>" . e($u['email']) . "</small></td>
                                    <td><span class='badge $plan_class' style='$gold_style'>$plan</span></td>
                                    <td>$expires</td>
                                    <td>{$u['listing_count']} listings</td>
                                    <td><span class='badge $status_class'>$status_text</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding:40px; color:#999;'>
                                <i class='fas fa-star' style='font-size:48px; opacity:0.3;'></i><br><br>
                                No paid subscriptions yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($view == 'revenue'): ?>
            <!-- REVENUE OVERVIEW -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h2>Revenue Overview</h2>
                <div style="background:linear-gradient(135deg, #48bb78 0%, #2e7d32 100%); padding:40px; border-radius:12px; color:white; text-align:center; margin:20px 0;">
                    <h1 style="font-size:48px; margin:0;">₦<?php echo number_format($total_revenue); ?></h1>
                    <p style="opacity:0.9;">Total Revenue from Subscriptions</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT s.*, u.username FROM subscriptions s LEFT JOIN users u ON s.user_id = u.id WHERE s.status = 'success' ORDER BY s.created_at DESC LIMIT 50");
                        if ($res && $res->num_rows > 0) {
                            while($t = $res->fetch_assoc()) {
                                $plan = strtoupper($t['plan']);
                                echo "<tr>
                                    <td><code>" . e($t['reference']) . "</code></td>
                                    <td>" . e($t['username']) . "</td>
                                    <td><span class='badge approved'>$plan</span></td>
                                    <td>₦" . number_format($t['amount']) . "</td>
                                    <td>" . date('M d, Y H:i', strtotime($t['created_at'])) . "</td>
                                    <td><span class='badge approved'><i class='fas fa-check'></i> Paid</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:40px; color:#999;'>
                                <i class='fas fa-receipt' style='font-size:48px; opacity:0.3;'></i><br><br>
                                No transactions yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($view == 'reports'): ?>
            <!-- TRUST CENTER (Reports & Testimonies) -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Reporter</th>
                            <th>Details</th>
                            <th>Target</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Lazy create check
                        $conn->query("CREATE TABLE IF NOT EXISTS reports (
                            id INT(11) AUTO_INCREMENT PRIMARY KEY,
                            type ENUM('report', 'testimony') NOT NULL,
                            reporter_name VARCHAR(100) NOT NULL,
                            reporter_contact VARCHAR(100) NOT NULL,
                            target_farmer VARCHAR(100),
                            message TEXT NOT NULL,
                            status ENUM('pending', 'reviewed') DEFAULT 'pending',
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )");

                        $res = $conn->query("SELECT * FROM reports ORDER BY created_at DESC");
                        if ($res && $res->num_rows > 0) {
                            while($r = $res->fetch_assoc()) {
                                $badge_class = ($r['type'] == 'testimony') ? 'approved' : 'rejected';
                                $status_badge = ($r['status'] == 'reviewed') ? '<span class="badge approved">Published</span>' : '<span class="badge pending">Pending</span>';
                                
                                echo "<tr>
                                    <td><span class='badge $badge_class'>" . ucfirst($r['type']) . "</span></td>
                                    <td>
                                        <strong>" . e($r['reporter_name']) . "</strong><br>
                                        <small>" . e($r['reporter_contact']) . "</small>
                                    </td>
                                    <td>
                                        " . e($r['message']) . "<br>
                                        <div style='margin-top:5px;'>$status_badge</div>
                                    </td>
                                    <td>" . ($r['target_farmer'] ?: 'General') . "</td>
                                    <td>
                                        <a href='dashboard.php?action=delete_report&id={$r['id']}' class='btn btn-sm' style='background: #718096; color: white;' onclick=\"return confirm('Delete this report?');\">Delete</a>
                                        ";
                                        if ($r['status'] == 'pending') {
                                            echo "<a href='dashboard.php?action=publish_report&id={$r['id']}' class='btn btn-sm' style='background:#48bb78; color:white; margin-left:5px;'>Publish</a>";
                                        } else {
                                            echo "<a href='dashboard.php?action=unpublish_report&id={$r['id']}' class='btn btn-sm' style='background:#e53e3e; color:white; margin-left:5px;'>Unpublish</a>";
                                        }
                                echo "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No reports or testimonies yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($view == 'verifications'): ?>
            <!-- VERIFICATIONS -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h2>Identity Verification Requests</h2>
                <p style="color:#666; margin-bottom:20px;">Review identity documents against registration info.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Farmer / Full Name</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sql = "SELECT vr.*, u.username, u.full_name as profile_full_name, u.nin_number, u.profile_photo 
                                FROM verification_requests vr 
                                JOIN users u ON vr.user_id = u.id 
                                ORDER BY vr.requested_at DESC";
                        $res = $conn->query($sql);

                        if ($res && $res->num_rows > 0) {
                            while($r = $res->fetch_assoc()) {
                                $status_class = ($r['status'] == 'approved' ? 'approved' : ($r['status'] == 'pending' ? 'pending' : 'rejected'));
                                echo "<tr>
                                        <strong>" . htmlspecialchars($r['profile_full_name'] ?: $r['username']) . "</strong><br>
                                        <small>User: @{$r['username']}</small>
                                    </td>
                                    <td>" . date('M d, Y', strtotime($r['requested_at'])) . "</td>
                                    <td><span class='badge $status_class'>" . strtoupper($r['status']) . "</span></td>
                                    <td>
                                        <a href='verify-detail.php?id={$r['user_id']}' class='btn btn-sm btn-primary'>Review Document</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding:40px;'>No verification requests yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
