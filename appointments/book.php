<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$patient_query = "SELECT patient_id FROM users WHERE id = $user_id";
$patient_result = $conn->query($patient_query);
$patient_row = $patient_result->fetch_assoc();
$patient_id = $patient_row['patient_id'];

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = trim($_POST['reason']);

   
    $datetime = strtotime($appointment_date . ' ' . $appointment_time);
    if($datetime <= time()) {
        $error = "Please select a future date and time";
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, 'scheduled')");
        $stmt->bind_param("iisss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $reason);
        
        if($stmt->execute()) {
            $success = "Appointment booked successfully!";
        } else {
            $error = "Error booking appointment: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get all available doctors
$doctors_query = "SELECT id, first_name, last_name, specialization FROM doctors ORDER BY first_name";
$doctors_result = $conn->query($doctors_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    
    <div class="book-appointment-split">
        <!-- Left Panel: Information -->
        <div class="book-left">
            <h2>Schedule Your Appointment</h2>
            <p>Choose an available doctor, pick a date and time, and provide a brief reason for your visit.</p>

            <div class="book-features">
                <div class="book-feature-item">Experienced doctors</div>
                <div class="book-feature-item">Flexible scheduling</div>
                <div class="book-feature-item">Quick confirmation</div>
            </div>
        </div>

        <!-- Right Panel: Form -->
        <div class="book-right">
            <h2>Book Consultation</h2>

            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="doctor_id">Select Doctor</label>
                    <select id="doctor_id" name="doctor_id" required>
                        <option value="">Choose a doctor...</option>
                        <?php while($doctor = $doctors_result->fetch_assoc()): ?>
                            <option value="<?php echo $doctor['id']; ?>">
                                Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?> 
                                (<?php echo htmlspecialchars($doctor['specialization']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment_date">Date</label>
                        <input type="date" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="appointment_time">Time</label>
                        <input type="time" id="appointment_time" name="appointment_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Reason for Visit</label>
                    <textarea id="reason" name="reason" placeholder="Describe your symptoms or reason for visit..."></textarea>
                </div>

                <button type="submit" class="btn-book">Book Appointment</button>
            </form>

            <div style="margin-top: 20px; text-align: center; color: var(--color-text-secondary); font-size: 0.9rem;">
                <a href="view.php" style="color: var(--color-accent-lime); text-decoration: none; font-weight: 600;">View My Appointments →</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
    </footer>
</body>
</html>
