<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please refresh and try again.';
    } else {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];

    // Allow login by email or username
    $sql = "SELECT * FROM users WHERE (email = '$email' OR username = '$email') AND role = 'farmer'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'farmer';
            $_SESSION['username'] = $user['username'];
            
            // Update Last Active
            $now = date('Y-m-d H:i:s');
            $conn->query("UPDATE users SET last_active = '$now' WHERE id = " . $user['id']);
            
            header("Location: vendor-dashboard.php");
            exit;
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'Invalid email/username or account does not exist';
    }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="section" style="padding: 40px 0; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="container">
        <div class="login-box" style="margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
            <h2>Vendor Login</h2>
            <?php if ($error): ?>
                <p class="error-msg"><?php echo e($error); ?></p>
            <?php endif; ?>
    <form action="vendor-login.php" method="POST">
        <?php csrf_input(); ?>
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Email or Username</label>
            <input type="text" name="email" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label>Password</label>
            <input type="password" name="password" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
        <p style="margin-top:15px; text-align:center; font-size:0.9em;">New Vendor? <a href="vendor-register.php">Register here</a></p>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
