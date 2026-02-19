<?php
session_start();
include 'config.php';

// Check if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clinic Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="auth-split">
        <div class="auth-left">
            <div class="auth-visual">
                <div class="brand">
                    <div class="logo-big">Clinic</div>
                    <p>Your care. Streamlined.</p>
                </div>
            </div>
        </div>

        <div class="auth-right">
            <div class="auth-card">
                <h2>Sign in to your account</h2>
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="register.php" class="btn btn-ghost">Create account</a>
                    </div>
                </form>
                <div class="help">Forgot password? <a href="reset_admin_password.php">Reset here</a></div>
            </div>
        </div>
    </div>
</body>
</html>
