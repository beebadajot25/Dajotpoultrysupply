<?php 
include 'includes/db.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Error: Security validation failed. Please refresh and try again.";
    } else {
        $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $phone = $conn->real_escape_string($_POST['phone']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $location = $conn->real_escape_string($_POST['location']);
    $terms = isset($_POST['terms_agreement']) ? true : false;

    if (!$terms) {
        $message = "Error: You must agree to the Terms and Conditions to register.";
    } else {
        // 1. Check if User Exists
    $check = $conn->query("SELECT id FROM users WHERE email='$email' OR username='$name'");
    if ($check->num_rows > 0) {
        $message = "Error: User with this email or name already exists. <a href='vendor-login.php'>Login here</a>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'farmer';
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, phone, whatsapp, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $phone, $whatsapp, $location);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Auto Login - must be before any output
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = 'farmer';
            $_SESSION['username'] = $name;
            
            // Redirect to Dashboard
            header("Location: vendor-dashboard.php");
            exit;
        } else {
             $message = "Error creating account: " . $conn->error;
        }
        $stmt->close();
        }
    }
}
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-header text-center">
    <div class="container">
        <h1>Join Marketplace</h1>
        <p>Create your vendor account to start selling</p>
    </div>
</div>

<section class="section" style="padding-top: 40px;">
    <div class="container">
        
        <?php if (!empty($message)) { ?>
            <div style="background: <?php echo strpos($message, 'Success') !== false ? '#e8f5e9' : '#ffebee'; ?>; 
                        color: <?php echo strpos($message, 'Success') !== false ? '#2e7d32' : '#c62828'; ?>; 
                        padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center; border: 1px solid #ddd;">
                <i class="fas <?php echo strpos($message, 'Success') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i> <?php echo $message; ?>
            </div>
        <?php } ?>

        <div style="max-width:700px; margin:0 auto;">
        <div style="max-width:700px; margin:0 auto;">
            <div style="background:#fff; padding:40px; border-radius:10px; border:1px solid #eee; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                <h2 class="section-title" style="text-align:left; margin-bottom:5px;">Vendor Registration</h2>
                <p style="color:#666; margin-bottom:30px;">Create an account to manage your farm and listings.</p>
                
                <form id="vendor-reg-form" action="vendor-register.php" method="POST">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="terms_agreement" value="1">
                    
                    <div class="form-group">
                        <label>Vendor / Farm Name (Username)</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number (For Buyers)</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                     <div class="form-group">
                        <label>WhatsApp Number</label>
                        <input type="text" name="whatsapp" class="form-control">
                    </div>
                     <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                    
                    <button type="button" id="show-terms-btn" class="btn btn-primary btn-block">Create Account</button>
                    <p style="margin-top:15px; text-align:center;">Already have an account? <a href="vendor-login.php">Login here</a></p>
                </form>
            </div>
        </div>

    </div>
</section>

<!-- Terms Modal -->
<div id="terms-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; display:none; justify-content:center; align-items:center; padding: 20px;">
    <div style="background:#fff; padding:30px; border-radius:12px; max-width:600px; width:100%; max-height:90vh; overflow-y:auto; position:relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h2 style="color:#2e7d32; margin-top:0;"><i class="fas fa-file-contract"></i> Marketplace Terms</h2>
        <p style="color:#666; font-size: 0.9em; margin-bottom:20px;">Please read and agree to our terms to finalize your registration.</p>
        
        <div style="height: 300px; overflow-y: scroll; border: 1px solid #ddd; padding: 20px; border-radius: 8px; font-size: 0.9em; color: #555; background: #fafafa; margin-bottom: 20px; line-height: 1.6;">
            <h4 style="color: #2e7d32; margin-top:0;">1. Vendor Code of Conduct</h4>
            <p>As a vendor on Dajot Marketplace, you commit to providing accurate information about your poultry products. Fraudulent listings, misrepresentation of livestock health, or unethical business practices are strictly prohibited.</p>
            
            <h4 style="color: #2e7d32; margin-top:20px;">2. Direct Transactions</h4>
            <p>Dajot provides the platform for connection. All transactions, payments, and deliveries are handled directly between you and the buyer. We are not liable for disputes arising from these transactions.</p>
            
            <h4 style="color: #2e7d32; margin-top:20px;">3. Product Quality</h4>
            <p>You are solely responsible for the quality and health of the poultry or equipment you list. Ensure your products meet the standards described in your listings.</p>
            
            <h4 style="color: #2e7d32; margin-top:20px;">4. Account Security</h4>
            <p>Maintain the confidentiality of your account credentials. You are responsible for all activities conducted through your vendor dashboard.</p>
            
            <p style="margin-top:20px; font-weight:bold;">For more details, visit our <a href="terms.php" target="_blank">Full Terms Page</a>.</p>
        </div>

        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 25px;">
            <input type="checkbox" id="accept-terms-check" style="margin-top: 5px; transform: scale(1.2);">
            <label for="accept-terms-check" style="font-size: 1em; color: #333; cursor: pointer;">
                I have read, understood, and <strong>agree to the Terms and Conditions</strong> of Dajot Poultry Supply.
            </label>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-outline" id="close-modal" style="flex:1;">Cancel</button>
            <button type="button" class="btn btn-primary" id="final-submit" style="flex:2;" disabled>Agree & Register</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const regForm = document.getElementById('vendor-reg-form');
    const showTermsBtn = document.getElementById('show-terms-btn');
    const termsModal = document.getElementById('terms-modal');
    const closeModal = document.getElementById('close-modal');
    const termsCheck = document.getElementById('accept-terms-check');
    const finalSubmit = document.getElementById('final-submit');

    // Show modal only if form is valid
    showTermsBtn.addEventListener('click', function() {
        if (regForm.checkValidity()) {
            termsModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        } else {
            regForm.reportValidity(); // Show native browser validation messages
        }
    });

    closeModal.addEventListener('click', function() {
        termsModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });

    termsCheck.addEventListener('change', function() {
        finalSubmit.disabled = !this.checked;
    });

    finalSubmit.addEventListener('click', function() {
        if (termsCheck.checked) {
            regForm.submit();
        }
    });

    // Prevent Enter key from submitting bypassing terms
    regForm.addEventListener('submit', function(e) {
        if (!termsCheck.checked) {
            e.preventDefault();
            showTermsBtn.click(); // Trigger terms modal
        }
    });

    // Close on overlay click
    termsModal.addEventListener('click', function(e) {
        if (e.target === termsModal) {
            termsModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
