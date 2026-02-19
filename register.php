<?php
session_start();
include 'config.php';


if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    if(empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif($password != $confirm_password) {
        $error = "Passwords do not match";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if($result->num_rows > 0) {
            $error = "This username or email already exists";
        } else {
            // Hash password outside of transaction
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction for faster inserts
            $conn->begin_transaction();
            
            try {
                if($role == 'patient') {
                    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, email) VALUES (?, ?, ?)");
                    if(!$stmt) {
                        throw new Exception($conn->error);
                    }
                    $stmt->bind_param("sss", $first_name, $last_name, $email);
                    $stmt->execute();
                    $patient_id = $stmt->insert_id;
                    $stmt->close();

                    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, patient_id) VALUES (?, ?, ?, ?, ?)");
                    if(!$stmt) {
                        throw new Exception($conn->error);
                    }
                    $stmt->bind_param("ssssi", $username, $hashed_password, $email, $role, $patient_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $conn->prepare("INSERT INTO doctors (first_name, last_name, email) VALUES (?, ?, ?)");
                    if(!$stmt) {
                        throw new Exception($conn->error);
                    }
                    $stmt->bind_param("sss", $first_name, $last_name, $email);
                    $stmt->execute();
                    $doctor_id = $stmt->insert_id;
                    $stmt->close();

                    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, doctor_id) VALUES (?, ?, ?, ?, ?)");
                    if(!$stmt) {
                        throw new Exception($conn->error);
                    }
                    $stmt->bind_param("ssssi", $username, $hashed_password, $email, $role, $doctor_id);
                    $stmt->execute();
                    $stmt->close();
                }
                
                $conn->commit();
                $success = "Registration successful! Redirecting to login in 3 seconds...";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Clinic Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <?php if($success): ?>
        <script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000);
        </script>
    <?php endif; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="auth-split">
        <div class="auth-left">
            <div class="auth-visual">
                <div class="brand">
                    <div class="logo-big">Clinic</div>
                    <p>Secure, compliant, and built for care teams.</p>
                </div>
            </div>
        </div>

        <div class="auth-right">
            <div class="auth-card">
                <h2>Create an account</h2>
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="role">I am a</label>
                        <select id="role" name="role" required>
                            <option value="patient">Patient</option>
                            <option value="doctor">Doctor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a href="login.php" class="btn btn-ghost">Already have an account?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
