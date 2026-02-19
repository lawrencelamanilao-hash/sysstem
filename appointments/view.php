<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$patient_query = "SELECT patient_id FROM users WHERE id = ?";
$stmt = $conn->prepare($patient_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patient_result = $stmt->get_result();
$patient_row = $patient_result->fetch_assoc();
$stmt->close();
$patient_id = $patient_row['patient_id'];

$appointments_query = "SELECT a.*, d.first_name, d.last_name, d.specialization FROM appointments a 
                       JOIN doctors d ON a.doctor_id = d.id 
                       WHERE a.patient_id = ? 
                       ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($appointments_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <h2>My Appointments</h2>
            
            <?php if($appointments_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($appointment = $appointments_result->fetch_assoc()): ?>
                            <tr>
                                <td>Dr. <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['reason']); ?></td>
                                <td>
                                    <span class="<?php echo ($appointment['status'] == 'scheduled') ? 'status-scheduled' : 'status-' . $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No appointments found. <a href="book.php">Book one now</a></div>
            <?php endif; ?>

            <div style="margin-top: 2rem;">
                <a href="book.php" class="btn btn-primary">Book New Appointment</a>
                <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
    
    <style>
        .status-scheduled {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
</body>
</html>
