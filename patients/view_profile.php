<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$patient_query = "SELECT p.*, u.username FROM patients p 
                  JOIN users u ON p.id = (SELECT patient_id FROM users WHERE id = ?) 
                  WHERE p.id = (SELECT patient_id FROM users WHERE id = ?)";
$stmt = $conn->prepare($patient_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$patient_result = $stmt->get_result();
$patient = $patient_result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="dashboard-container profile-page centered-page">
        <div class="profile-grid">
            <aside class="profile-card">
                <div class="profile-card-hero"></div>
                <div class="profile-card-body">
                    <img src="../uploads/avatars/<?php echo htmlspecialchars($patient['avatar'] ?? 'default.png'); ?>" alt="avatar" class="profile-avatar-img">
                    <div>
                        <div class="profile-name"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></div>
                        <div class="profile-sub">Patient</div>
                    </div>
                </div>
                <div style="margin-top:20px;">
                    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
            </aside>

            <main class="profile-form profile-form-card">
                <h2 class="profile-form-title">Profile Details</h2>
                <div class="profile-details">
                    <div class="detail-section">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($patient['email']); ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo $patient['phone'] ? htmlspecialchars($patient['phone']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Date of Birth</div>
                        <div class="detail-value"><?php echo $patient['date_of_birth'] ? htmlspecialchars($patient['date_of_birth']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Gender</div>
                        <div class="detail-value"><?php echo $patient['gender'] ? htmlspecialchars($patient['gender']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">City</div>
                        <div class="detail-value"><?php echo $patient['city'] ? htmlspecialchars($patient['city']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">State</div>
                        <div class="detail-value"><?php echo $patient['state'] ? htmlspecialchars($patient['state']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Address</div>
                        <div class="detail-value"><?php echo $patient['address'] ? htmlspecialchars($patient['address']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Zipcode</div>
                        <div class="detail-value"><?php echo $patient['zipcode'] ? htmlspecialchars($patient['zipcode']) : 'Not provided'; ?></div>
                    </div>
                </div>
            </main>
        </div>

        <footer class="footer" style="margin-top:40px; text-align:center; padding-top:20px; border-top:1px solid rgba(255,255,255,0.04);">
            <p style="color:var(--color-text-secondary); font-size:0.9rem;">&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
