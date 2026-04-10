<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit; }
require_once '../includes/db.php';

$user_id = intval($_GET['id'] ?? 0);
if (!$user_id) { header("Location: dashboard.php?view=verifications"); exit; }

// Fetch Request & User Info
$sql = "SELECT vr.*, u.username, u.full_name, u.email, u.phone, u.profile_photo, u.verification_level 
        FROM verification_requests vr 
        JOIN users u ON vr.user_id = u.id 
        WHERE vr.user_id = $user_id";
$res = $conn->query($sql);
$data = $res ? $res->fetch_assoc() : null;

if (!$data) { die("Request not found."); }

$message = "";
$msg_type = "";

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh and try again.";
        $msg_type = "error";
    } else {
        $action = $_POST['action'];
        $reason = $conn->real_escape_string($_POST['reason'] ?? '');

    if ($action == 'approve') {
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE users SET verification_level = 'verified' WHERE id = $user_id");
            $conn->query("UPDATE verification_requests SET status = 'approved', updated_at = NOW() WHERE user_id = $user_id");
            $conn->commit();
            $message = "User verified successfully!";
            $msg_type = "success";
            // Refresh data
            header("Location: dashboard.php?view=verifications&msg=verified");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $msg_type = "error";
        }
    } elseif ($action == 'reject') {
        $conn->query("UPDATE verification_requests SET status = 'rejected', updated_at = NOW() WHERE user_id = $user_id");
        $message = "Verification rejected. User can now resubmit.";
        $msg_type = "info";
        header("Location: dashboard.php?view=verifications&msg=rejected");
        exit;
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Verification - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .review-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .doc-preview { width: 100%; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 15px; }
        .label { color: #718096; font-size: 0.85em; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 1.1em; color: #2d3748; margin-bottom: 15px; display: block; }
    </style>
</head>
<body style="background: #f7fafc;">

    <div class="review-container">
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php?view=verifications" style="color: #2e7d32; text-decoration: none; font-weight: bold;">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <h1 style="margin-bottom: 30px;">Review Identity: <?php echo htmlspecialchars($data['username']); ?></h1>

        <div class="grid">
            <!-- Left: Information Comparison -->
            <div class="card">
                <h3>Verification Info</h3>
                <hr style="margin: 20px 0; opacity: 0.1;">
                
                <span class="label">Provided Full Name</span>
                <span class="value"><?php echo htmlspecialchars($data['full_name'] ?: 'Not Provided'); ?></span>

                <span class="label">Phone Number</span>
                <span class="value"><?php echo htmlspecialchars($data['phone']); ?></span>

                <span class="label">Requested On</span>
                <span class="value"><?php echo date('F d, Y H:i', strtotime($data['requested_at'])); ?></span>

                <div style="margin-top: 30px; padding: 20px; background: #fff5f5; border-radius: 8px; border-left: 4px solid #f56565;">
                    <p style="margin:0; font-size: 0.9em; color: #c53030;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Please ensure the <strong>Full Name</strong> matches the provided information before approving.
                    </p>
                </div>
            </div>

            <!-- Right: Document Preview -->
            <div class="card">
                <h3>Identity Verification</h3>
                <hr style="margin: 20px 0; opacity: 0.1;">
                <p>NIN requirements have been removed. Verify user based on provided profile information.</p>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="card" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 15px; flex: 1; align-items: center;">
                <form action="" method="POST" style="display: flex; gap: 15px; width: 100%;">
                    <?php csrf_input(); ?>
                    <button type="submit" name="action" value="reject" class="btn" style="background: #e53e3e; color: white;" onclick="return confirm('Are you sure you want to REJECT this verification?')">
                        <i class="fas fa-times"></i> Reject Request
                    </button>
                    <button type="submit" name="action" value="approve" class="btn" style="background: #2e7d32; color: white; padding: 12px 40px;" onclick="return confirm('Confirm that details match the document. Approve now?')">
                        <i class="fas fa-check-circle"></i> Approve & Verify User
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
