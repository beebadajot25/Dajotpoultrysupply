<?php
require_once 'includes/security.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: vendor-login.php");
    exit;
}
include 'includes/db.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Check current verification status
$user_q = $conn->query("SELECT verification_level, phone_verified, profile_photo, farm_name, farm_location, bio, phone, whatsapp, full_name FROM users WHERE id = $user_id");
if (!$user_q) {
    die("<div style='padding:40px; text-align:center; font-family:sans-serif;'>
            <h2>⚠️ Database Update Required</h2>
            <p>Your verification system needs a quick update before you can use it.</p>
            <a href='http://localhost/dajotpoultrysupply/migrate_everything.php' style='background:#2e7d32; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Click Here to Fix This Instantly</a>
         </div>");
}
$user = $user_q->fetch_assoc();
$status = $user['verification_level'] ?? 'basic';

// Check for pending request
$req_q = $conn->query("SELECT status FROM verification_requests WHERE user_id = $user_id");
$req_res = $req_q ? $req_q->fetch_assoc() : null;
$req_status = $req_res['status'] ?? '';
$is_pending = ($req_status === 'pending');

$message = "";
$msg_type = "success";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh and try again.";
        $msg_type = "error";
    } else {
        if ($is_pending) {
        $message = "Your verification is currently under review and cannot be modified.";
        $msg_type = "error";
    } else {
        // Basic verification update (Profile info)
        $farm_name = $conn->real_escape_string($_POST['farm_name']);
        $farm_location = $conn->real_escape_string($_POST['farm_location']);
        $bio = $conn->real_escape_string($_POST['bio']);
        $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
        $full_name = $conn->real_escape_string($_POST['full_name'] ?? '');
        
        {
            // Handle Profile Photo
            $profile_photo = $user['profile_photo']; 
            if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
                $target_dir = "assets/images/profiles/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $target_file = $target_dir . "profile_" . $user_id . "_" . uniqid() . ".jpg";
                if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                    $profile_photo = $target_file;
                }
            }
            
            
            $update_sql = "UPDATE users SET 
                           farm_name='$farm_name', 
                           farm_location='$farm_location', 
                           bio='$bio', 
                           whatsapp='$whatsapp',
                           profile_photo='$profile_photo',
                           full_name='$full_name'
                           WHERE id=$user_id";
            
            if ($conn->query($update_sql)) {
                if (isset($_POST['submit_verification'])) {
                    $conn->query("INSERT INTO verification_requests (user_id, status) VALUES ($user_id, 'pending') 
                                 ON DUPLICATE KEY UPDATE status='pending', requested_at=NOW()");
                    $message = "Your verification request has been submitted for review!";
                    $msg_type = "success";
                    $is_pending = true;
                } else {
                    $message = "Profile updated successfully!";
                    $msg_type = "success";
                }
                // Refresh data
                $user_q = $conn->query("SELECT * FROM users WHERE id = $user_id");
                $user = $user_q->fetch_assoc();
            } else {
                $message = "Error updating profile: " . (isset($conn) ? $conn->error : "Database unavailable");
                $msg_type = "error";
            }
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Get Verified - Dajot Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .verify-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .badge-status { padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 0.9em; }
        .status-basic { background: #E2E8F0; color: #4A5568; }
        .status-verified { background: #C6F6D5; color: #22543D; }
        .status-trusted { background: #2E7D32; color: white; }
    </style>
</head>
<body style="background: #f7fafc;">

<?php include 'includes/header.php'; ?>

<div class="verify-container">
    <div style="margin-bottom: 20px;">
        <a href="vendor-dashboard.php" style="color: #2e7d32; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 5px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <h1 style="margin-bottom: 20px;">Seller Verification</h1>
    
    <div class="card" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h3 style="margin: 0 0 5px;">Current Status</h3>
            <p style="margin: 0; color: #666;">Unlock trust badges and better placement</p>
        </div>
        <div>
            <?php if ($is_pending): ?>
                <span class="badge-status" style="background: #FEFCBF; color: #744210;">UNDER REVIEW</span>
            <?php endif; ?>
            <span class="badge-status status-<?php echo $status; ?>">
                <?php echo strtoupper($status); ?>
            </span>
        </div>
    </div>

    <?php if ($is_pending): ?>
        <div style="background: #fffaf0; border: 1px solid #feebc8; color: #7b341e; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-clock fa-2x"></i>
            <div>
                <strong style="display:block; margin-bottom: 3px;">Verification in Progress</strong>
                <p style="margin:0; font-size: 0.9em; opacity: 0.9;">Your application is being reviewed by our team. To maintain security, your identity information is locked during this time.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; 
            background: <?php echo $msg_type == 'success' ? '#c6f6d5' : '#fed7d7'; ?>; 
            color: <?php echo $msg_type == 'success' ? '#22543d' : '#822727'; ?>;">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3><i class="fas fa-edit"></i> Verification Details</h3>
        <p style="color: #666; margin-bottom: 20px;">Ensure all information matches your official document exactly.</p>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <?php csrf_input(); ?>
            <div class="form-group">
                <label>Official Full Name (As it appears on your ID)</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required placeholder="Enter name on ID" <?php echo $is_pending ? 'disabled' : ''; ?>>
            </div>

            <div class="form-group">
                <label>Farm/Business Name (Publicly visible)</label>
                <input type="text" name="farm_name" class="form-control" value="<?php echo htmlspecialchars($user['farm_name'] ?? ''); ?>" required <?php echo $is_pending ? 'disabled' : ''; ?>>
            </div>
            
            <div class="form-group">
                <label>Farm Location (City, State)</label>
                <input type="text" name="farm_location" class="form-control" value="<?php echo htmlspecialchars($user['farm_location'] ?? ''); ?>" required <?php echo $is_pending ? 'disabled' : ''; ?>>
            </div>

            <div class="form-group">
                <label>WhatsApp Number</label>
                <input type="text" name="whatsapp" class="form-control" value="<?php echo htmlspecialchars($user['whatsapp'] ?? $user['phone']); ?>" required <?php echo $is_pending ? 'disabled' : ''; ?>>
            </div>
            
            <div class="form-group">
                <label>Short Bio (Tell buyers about your farm)</label>
                <textarea name="bio" class="form-control" rows="3" <?php echo $is_pending ? 'disabled' : ''; ?>><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Profile Photo</label>
                <?php if(!empty($user['profile_photo'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo $user['profile_photo']; ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                    </div>
                <?php endif; ?>
                <input type="file" name="profile_photo" class="form-control" accept="image/*" <?php echo $is_pending ? 'disabled' : ''; ?>>
            </div>


            <?php if (!$is_pending): ?>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; display: flex; gap: 15px;">
                <button type="submit" name="save_profile" class="btn btn-outline" style="padding: 12px 25px;">Save Progress Only</button>
                <button type="submit" name="submit_verification" class="btn btn-primary" style="padding: 12px 25px; flex: 1; background: #2e7d32;">
                    <i class="fas fa-check-circle"></i> Submit for Official Review
                </button>
            </div>
            <p style="font-size: 0.85em; color: #718096; margin-top: 15px;">
                * Once submitted, your profile information will be locked until our team completes the review.
            </p>
            <?php else: ?>
            <div style="margin-top: 30px; padding: 20px; background: #f7fafc; border-radius: 8px; text-align: center;">
                <p style="color: #4a5568; margin:0;">Identity fields are locked during active review.</p>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
