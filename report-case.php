<?php 
require_once 'includes/security.php';
include 'includes/header.php'; 
?>

<div class="page-header text-center">
    <div class="container">
        <h1>Trust & Safety Center</h1>
        <p>Report an issue or share a success story</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                include 'includes/db.php';
                if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                    echo "<div class='alert alert-danger'>Security validation failed. Please refresh and try again.</div>";
                } else {

                // Ensure Table Exists (Lazy Init)
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

                $type = $conn->real_escape_string($_POST['type']);
                $name = $conn->real_escape_string($_POST['name']);
                $contact = $conn->real_escape_string($_POST['contact']);
                $farmer = $conn->real_escape_string($_POST['farmer']);
                $message = $conn->real_escape_string($_POST['message']);

                $sql = "INSERT INTO reports (type, reporter_name, reporter_contact, target_farmer, message) 
                        VALUES ('$type', '$name', '$contact', '$farmer', '$message')";

                if ($conn->query($sql) === TRUE) {
                    $color = ($type == 'testimony') ? 'green' : '#e53e3e';
                    $icon = ($type == 'testimony') ? 'fa-heart' : 'fa-shield-alt';
                    echo "<div style='text-align:center; padding: 20px; background: #f9f9f9; border-left: 4px solid $color; margin-bottom: 20px;'>
                            <i class='fas $icon' style='font-size: 30px; color: $color; margin-bottom: 10px;'></i><br>
                            <strong>Submission Received!</strong><br>
                            Thank you for helping us keep Dajot Marketplace safe and trusted.
                          </div>";
                } else {
                    echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
                }
                }
            }
            ?>

            <form action="report-case.php" method="POST">
                <?php csrf_input(); ?>
                <div class="form-group">
                    <label>What would you like to do?</label>
                    <select name="type" class="form-control" required style="height: 50px;">
                        <option value="testimony">❤️ Share a positive testimony</option>
                        <option value="report">⚠️ Report a fraud / complaint</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                </div>

                <div class="form-group">
                    <label>Your Contact (Phone or Email)</label>
                    <input type="text" name="contact" class="form-control" placeholder="How can we reach you?" required>
                </div>

                <div class="form-group">
                    <label>Name of Farmer / Vendor (Optional)</label>
                    <input type="text" name="farmer" class="form-control" placeholder="Who is this regarding?">
                </div>

                <div class="form-group">
                    <label>Details</label>
                    <textarea name="message" rows="5" class="form-control" placeholder="Please describe your experience in detail..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
