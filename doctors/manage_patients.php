<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$doctor_query = "SELECT doctor_id FROM users WHERE id = ?";
$stmt = $conn->prepare($doctor_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor_result = $stmt->get_result();
$doctor_row = $doctor_result->fetch_assoc();
$stmt->close();
$doctor_id = $doctor_row['doctor_id'];


$patients_query = "SELECT DISTINCT p.*, COUNT(a.id) as appointment_count FROM patients p
                   JOIN appointments a ON p.id = a.patient_id
                   WHERE a.doctor_id = ?
                   GROUP BY p.id
                   ORDER BY p.first_name";
$stmt = $conn->prepare($patients_query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$patients_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <h2>My Patients</h2>
            
            <?php if($patients_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Appointments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($patient = $patients_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                <td><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo $patient['appointment_count']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No patients found.</div>
            <?php endif; ?>

            <div style="margin-top: 2rem;">
                <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
