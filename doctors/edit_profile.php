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

$error = '';
$success = '';

// Show upload handler messages
if(isset($_GET['upload_success'])){
    $success = 'Photo uploaded successfully.';
}
if(isset($_GET['upload_error'])){
    $error = htmlspecialchars(urldecode($_GET['upload_error']));
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $specialization = trim($_POST['specialization']);
    $phone = trim($_POST['phone']);
    $license_number = trim($_POST['license_number']);
    $address = trim($_POST['address']);
    $availability = trim($_POST['availability']);

    $stmt = $conn->prepare("UPDATE doctors SET specialization=?, phone=?, license_number=?, address=?, availability=? WHERE id=?");
    $stmt->bind_param("sssssi", $specialization, $phone, $license_number, $address, $availability, $doctor['id']);
    
    if($stmt->execute()) {
        $success = "Profile updated successfully!";
        $doctor = array_merge($doctor, [
            'specialization' => $specialization,
            'phone' => $phone,
            'license_number' => $license_number,
            'address' => $address,
            'availability' => $availability
        ]);
    } else {
        $error = "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="dashboard-container profile-page centered-page">
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

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

                <div class="profile-avatar" style="margin-top:20px;">
                    <form method="POST" enctype="multipart/form-data" action="../upload_avatar.php">
                        <label class="profile-avatar-label">Your Photo</label>
                        <input type="file" name="avatar" accept="image/*" style="margin-bottom:14px; display:block; width:100%;">
                        <div class="profile-avatar-actions" style="margin-top:14px; display:flex; gap:10px;">
                            <button type="submit" class="btn btn-primary">Upload</button>
                            <a href="view_profile.php" class="btn btn-outline">Preview</a>
                        </div>
                    </form>
                </div>
            </aside>

            <main class="profile-form profile-form-card">
                <h2 class="profile-form-title">Edit Profile</h2>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?>" placeholder="e.g., Cardiology">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="license_number">License Number</label>
                            <input type="text" id="license_number" name="license_number" value="<?php echo htmlspecialchars($doctor['license_number'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($doctor['date_of_birth'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($doctor['address'] ?? ''); ?>" placeholder="Enter your address">
                    </div>

                    <div class="form-group">
                        <label for="availability">Availability</label>
                        <textarea id="availability" name="availability" rows="4" placeholder="Enter your availability schedule"><?php echo htmlspecialchars($doctor['availability'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="view_profile.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </main>
        </div>

        <footer class="footer" style="margin-top:40px; text-align:center; padding-top:20px; border-top:1px solid rgba(255,255,255,0.04);">
            <p style="color:var(--color-text-secondary); font-size:0.9rem;">&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
