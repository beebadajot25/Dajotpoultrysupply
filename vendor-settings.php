<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: vendor-login.php");
    exit;
}

require_once 'includes/db.php';
$user_id = $_SESSION['user_id'];

$msg = "";
$msg_type = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $msg = "Security validation failed. Please refresh and try again.";
        $msg_type = "error";
    } else {
        $farm_name = $conn->real_escape_string($_POST['farm_name']);
        $farm_location = $conn->real_escape_string($_POST['farm_location']);
        $bio = $conn->real_escape_string($_POST['bio']);
        $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
        $lat = !empty($_POST['lat']) ? floatval($_POST['lat']) : 'NULL';
        $lng = !empty($_POST['lng']) ? floatval($_POST['lng']) : 'NULL';
        
        // Handle Profile Photo
        $photo_sql = "";
        if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
            $target_dir = "assets/images/profiles/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . "profile_" . $user_id . "_" . uniqid() . ".jpg";
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $photo_sql = ", profile_photo='$target_file'";
            }
        }

        $sql = "UPDATE users SET 
                farm_name='$farm_name', 
                farm_location='$farm_location', 
                bio='$bio', 
                whatsapp='$whatsapp',
                lat=$lat,
                lng=$lng
                $photo_sql
                WHERE id=$user_id";
        
        if ($conn->query($sql)) {
            $msg = "Profile updated successfully!";
            $msg_type = "success";
        } else {
            $msg = "Error updating profile: " . (isset($conn) ? $conn->error : "Database unavailable");
            if (isset($conn) && strpos($conn->error, 'Unknown column') !== false) {
                $msg .= "<br><br><a href='migrate_everything.php' class='btn btn-sm' style='background:#e53e3e; color:white; text-decoration:none; padding:5px 15px; border-radius:4px;'>Click here to Fix Database</a>";
            }
            $msg_type = "error";
        }
    }
}

// Fetch Current Data
$user_res = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_res ? $user_res->fetch_assoc() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Settings - Dajot Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        .settings-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        #map { height: 300px; width: 100%; border-radius: 8px; margin-top: 10px; z-index: 1; }
    </style>
</head>
<body style="background: #f7fafc;">

<?php include 'includes/header.php'; ?>

<div class="settings-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="margin:0;">Farm Settings</h1>
        <a href="vendor-dashboard.php" class="btn btn-outline btn-sm">Back to Dashboard</a>
    </div>

    <?php if ($msg): ?>
        <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; 
            background: <?php echo $msg_type == 'success' ? '#c6f6d5' : '#fed7d7'; ?>; 
            color: <?php echo $msg_type == 'success' ? '#22543d' : '#822727'; ?>;">
            <?php echo e($msg); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="" method="POST" enctype="multipart/form-data">
            <?php csrf_input(); ?>
            
            <div class="form-group">
                <label>Farm Name</label>
                <input type="text" name="farm_name" class="form-control" value="<?php echo htmlspecialchars($user['farm_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Generic Location (City, State)</label>
                <input type="text" name="farm_location" class="form-control" value="<?php echo htmlspecialchars($user['farm_location'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Pin Exact Farm Location (Click on map)</label>
                <div id="map"></div>
                <!-- Hidden inputs for Lat/Lng -->
                <input type="hidden" name="lat" id="lat" value="<?php echo htmlspecialchars($user['lat'] ?? ''); ?>">
                <input type="hidden" name="lng" id="lng" value="<?php echo htmlspecialchars($user['lng'] ?? ''); ?>">
                <p style="font-size:0.85em; color:#666; margin-top:5px;">This helps buyers find you easier based on distance.</p>
            </div>

            <div class="form-group">
                <label>WhatsApp Number</label>
                <input type="text" name="whatsapp" class="form-control" value="<?php echo htmlspecialchars($user['whatsapp'] ?? $user['phone']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Short Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Profile Photo</label>
                <?php if(!empty($user['profile_photo'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo $user['profile_photo']; ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                    </div>
                <?php endif; ?>
                <input type="file" name="profile_photo" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    // Default to Nigeria Center or User location
    var defaultLat = <?php echo !empty($user['lat']) ? $user['lat'] : '9.0820'; ?>;
    var defaultLng = <?php echo !empty($user['lng']) ? $user['lng'] : '8.6753'; ?>;
    var zoomLevel = <?php echo !empty($user['lat']) ? '13' : '6'; ?>;

    var map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker;

    // If user has saved location, show it
    <?php if(!empty($user['lat'])): ?>
        marker = L.marker([defaultLat, defaultLng]).addTo(map);
    <?php endif; ?>

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
    });
</script>
<?php // Removed redundant footer include ?>
