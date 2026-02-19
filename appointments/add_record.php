<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['appointment_id'])) {
    header("Location: manage.php");
    exit();
}

$appointment_id = intval($_GET['appointment_id']);
$user_id = $_SESSION['user_id'];

$appointment_query = "SELECT a.*, p.first_name as p_fname, p.last_name as p_lname FROM appointments a
                      JOIN patients p ON a.patient_id = p.id
                      WHERE a.id = ?";
$stmt = $conn->prepare($appointment_query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment_result = $stmt->get_result();
$stmt->close();

if($appointment_result->num_rows == 0) {
    header("Location: manage.php");
    exit();
}

$appointment = $appointment_result->fetch_assoc();
$patient_id = $appointment['patient_id'];

// Get doctor ID
$doctor_query = "SELECT doctor_id FROM users WHERE id = ?";
$stmt = $conn->prepare($doctor_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor_result = $stmt->get_result();
$doctor_row = $doctor_result->fetch_assoc();
$stmt->close();
$doctor_id = $doctor_row['doctor_id'];

// Get existing record if any
$record_query = "SELECT * FROM medical_records WHERE appointment_id = ?";
$stmt = $conn->prepare($record_query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$record_result = $stmt->get_result();
$stmt->close();
$record = null;
if($record_result->num_rows > 0) {
    $record = $record_result->fetch_assoc();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $diagnosis = trim($_POST['diagnosis']);
    $prescription = trim($_POST['prescription']);
    $notes = trim($_POST['notes']);

    if(empty($diagnosis)) {
        $error = "Please enter diagnosis";
    } else {
        if($record) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE medical_records SET diagnosis=?, prescription=?, notes=? WHERE id=?");
            $stmt->bind_param("sssi", $diagnosis, $prescription, $notes, $record['id']);
        } else {
            // Create new record
            $stmt = $conn->prepare("INSERT INTO medical_records (patient_id, doctor_id, appointment_id, diagnosis, prescription, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiisss", $patient_id, $doctor_id, $appointment_id, $diagnosis, $prescription, $notes);
        }

        if($stmt->execute()) {
            $success = "Medical record saved successfully!";
            if(!$record) {
                $record = ['diagnosis' => $diagnosis, 'prescription' => $prescription, 'notes' => $notes];
            }
        } else {
            $error = "Error saving record: " . $stmt->error;
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
    <title>Add Medical Record - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="dashboard-container" style="max-width:920px; margin:28px auto;">
        <div style="display:flex; gap:20px; flex-direction:column;">
            <div class="record-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="margin:0; color:var(--color-text-primary);">Medical Record</h2>
                    <div style="color:var(--color-text-secondary); font-size:0.95rem;">Patient: <strong><?php echo htmlspecialchars($appointment['p_fname'] . ' ' . $appointment['p_lname']); ?></strong></div>
                    <div style="color:var(--color-text-secondary); font-size:0.9rem;">Date: <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?> at <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></div>
                </div>
                <div>
                    <a href="manage.php" class="btn" style="background:transparent; border:1px solid rgba(255,255,255,0.04);">Back</a>
                </div>
            </div>

            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="record-stack">
                    <section class="record-card">
                        <h4 class="card-title">Diagnosis</h4>
                        <div class="card-body">
                            <textarea id="diagnosis" name="diagnosis" rows="4" required class="record-field"><?php echo $record ? htmlspecialchars($record['diagnosis']) : ''; ?></textarea>
                        </div>
                    </section>

                    <section class="record-card">
                        <h4 class="card-title">Prescription</h4>
                        <div class="card-body">
                            <textarea id="prescription" name="prescription" rows="4" class="record-field"><?php echo $record ? htmlspecialchars($record['prescription']) : ''; ?></textarea>
                        </div>
                    </section>

                    <section class="record-card">
                        <h4 class="card-title">Notes</h4>
                        <div class="card-body">
                            <textarea id="notes" name="notes" rows="4" class="record-field"><?php echo $record ? htmlspecialchars($record['notes']) : ''; ?></textarea>
                        </div>
                    </section>

                    <div class="record-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $record ? 'Update' : 'Save'; ?> Record</button>
                        <a href="manage.php" class="btn">Cancel</a>
                    </div>
                </div>
            </form>
        </div>

        <footer class="footer" style="margin-top:28px;">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
