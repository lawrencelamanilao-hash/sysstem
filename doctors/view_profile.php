<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$doctor_query = "SELECT d.* FROM doctors d 
                 JOIN users u ON d.id = (SELECT doctor_id FROM users WHERE id = ?) 
                 WHERE d.id = (SELECT doctor_id FROM users WHERE id = ?)";
$stmt = $conn->prepare($doctor_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$doctor_result = $stmt->get_result();
$doctor = $doctor_result->fetch_assoc();
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
                    <img src="../uploads/avatars/<?php echo htmlspecialchars($doctor['avatar'] ?? 'default.png'); ?>" alt="avatar" class="profile-avatar-img">
                    <div>
                        <div class="profile-name"><?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></div>
                        <div class="profile-sub"><?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?></div>
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
                        <div class="detail-value"><?php echo htmlspecialchars($doctor['email']); ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo $doctor['phone'] ? htmlspecialchars($doctor['phone']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Specialization</div>
                        <div class="detail-value"><?php echo $doctor['specialization'] ? htmlspecialchars($doctor['specialization']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">License Number</div>
                        <div class="detail-value"><?php echo $doctor['license_number'] ? htmlspecialchars($doctor['license_number']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Address</div>
                        <div class="detail-value"><?php echo $doctor['address'] ? htmlspecialchars($doctor['address']) : 'Not provided'; ?></div>
                    </div>
                    <div class="detail-section">
                        <div class="detail-label">Availability</div>
                        <div class="detail-value"><?php echo $doctor['availability'] ? nl2br(htmlspecialchars($doctor['availability'])) : 'Not provided'; ?></div>
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
