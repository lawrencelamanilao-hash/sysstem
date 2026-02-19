<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$patient_query = "SELECT p.* FROM patients p 
                  JOIN users u ON p.id = (SELECT patient_id FROM users WHERE id = ?) 
                  WHERE p.id = (SELECT patient_id FROM users WHERE id = ?)";
$stmt = $conn->prepare($patient_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$patient_result = $stmt->get_result();
$patient = $patient_result->fetch_assoc();
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
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zipcode = trim($_POST['zipcode']);

    $stmt = $conn->prepare("UPDATE patients SET first_name=?, last_name=?, phone=?, date_of_birth=?, gender=?, address=?, city=?, state=?, zipcode=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $first_name, $last_name, $phone, $date_of_birth, $gender, $address, $city, $state, $zipcode, $patient['id']);
    
    if($stmt->execute()) {
        $success = "Profile updated successfully!";
        $patient = array_merge($patient, [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zipcode' => $zipcode
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
                    <img src="../uploads/avatars/<?php echo htmlspecialchars($patient['avatar'] ?? 'default.png'); ?>" alt="avatar" class="profile-avatar-img">
                    <div>
                        <div class="profile-name"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></div>
                        <div class="profile-sub">Patient</div>
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
                            <label for="first_name">First name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $patient['date_of_birth'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select...</option>
                                <option value="Male" <?php echo ($patient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($patient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($patient['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($patient['city'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($patient['address'] ?? ''); ?>" placeholder="Enter your address">
                    </div>

                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($patient['state'] ?? ''); ?>" placeholder="Enter your state">
                    </div>

                    <div class="form-group">
                        <label for="zipcode">Zipcode</label>
                        <input type="text" id="zipcode" name="zipcode" value="<?php echo htmlspecialchars($patient['zipcode'] ?? ''); ?>" placeholder="Enter your zipcode">
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
