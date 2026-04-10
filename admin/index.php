<?php
session_start();
$error = '';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please refresh and try again.';
    } else {
        $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND role = 'admin'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'Invalid username';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Dajot Poultry Supply</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; }
        .login-box h2 { margin-bottom: 20px; color: var(--primary-green); text-align: center; }
        .error-msg { color: #e53e3e; text-align: center; margin-bottom: 15px; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <p class="error-msg"><?php echo e($error); ?></p>
    <?php endif; ?>
    <form action="index.php" method="POST">
        <?php csrf_input(); ?>
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Username</label>
            <input type="text" name="username" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label>Password</label>
            <input type="password" name="password" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
</div>

</body>
</html>
