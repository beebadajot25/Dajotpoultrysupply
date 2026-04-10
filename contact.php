<?php 
require_once 'includes/security.php';
include 'includes/header.php'; 
?>

<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Expert Support for Farmers and Buyers</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            
            <!-- Contact Information -->
            <div class="contact-info-box">
                <h2 style="margin-bottom: 30px; color: var(--primary-green);">Get in Touch</h2>
                
                <div class="contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h4>Call or WhatsApp</h4>
                        <p>09034670525</p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email Address</h4>
                        <p>dajotsuply@gmail.com</p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fab fa-instagram"></i>
                    <div>
                        <h4>Social Media</h4>
                        <p>@dajotpoultrysupply</p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Location</h4>
                        <p>Nigeria</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-info-box">
                <h2 style="margin-bottom: 30px; color: var(--text-dark);">Send a Message</h2>
                
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    include 'includes/db.php';
                    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                        echo "<div class='alert alert-danger' style='color: red; margin-bottom: 10px;'>Security validation failed. Please refresh and try again.</div>";
                    } else {
                    
                    // Lazy Table Creation
                    $conn->query("CREATE TABLE IF NOT EXISTS messages (
                        id INT(11) AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        message TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )");

                    $name = $conn->real_escape_string($_POST['name']);
                    $email = $conn->real_escape_string($_POST['email']);
                    $message = $conn->real_escape_string($_POST['message']);

                    $sql = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";

                    if ($conn->query($sql) === TRUE) {
                        echo "<div class='alert alert-success' style='color: green; margin-bottom: 10px;'>Message sent successfully! We will contact you soon.</div>";
                    } else {
                        echo "<div class='alert alert-danger' style='color: red; margin-bottom: 10px;'>Error: " . $conn->error . "</div>";
                    }
                }
            }
                ?>

                <form action="contact.php" method="POST">
                    <?php csrf_input(); ?>
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" rows="5" class="form-control" placeholder="How can we help you?" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
