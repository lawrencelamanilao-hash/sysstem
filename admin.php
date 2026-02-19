<?php
session_start();
include 'config.php';

// Only allow admin users
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch appointments with patient & doctor info
$appointments_query = "SELECT a.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
                       FROM appointments a
                       LEFT JOIN patients p ON a.patient_id = p.id
                       LEFT JOIN doctors d ON a.doctor_id = d.id
                       ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$appointments = $conn->query($appointments_query);

// Fetch medical records with patient & doctor info
$records_query = "SELECT m.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
                  FROM medical_records m
                  LEFT JOIN patients p ON m.patient_id = p.id
                  LEFT JOIN doctors d ON m.doctor_id = d.id
                  ORDER BY m.created_at DESC";
$records = $conn->query($records_query);

// Fetch billing records with patient & doctor info (via appointment)
$billing_query = "SELECT b.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last, a.id AS appointment_id
                  FROM billing b
                  LEFT JOIN patients p ON b.patient_id = p.id
                  LEFT JOIN appointments a ON b.appointment_id = a.id
                  LEFT JOIN doctors d ON a.doctor_id = d.id
                  ORDER BY b.created_at DESC";
$billings = $conn->query($billing_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Clinic Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <h2>Admin Dashboard</h2>
            <p>Overview of appointments, medical records, and billing.</p>

            <section style="margin-top:1.5rem;">
                <h3>Appointments</h3>
                <?php if($appointments && $appointments->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($a = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a['id']); ?></td>
                                <td><?php echo htmlspecialchars(($a['p_first'] ?? '') . ' ' . ($a['p_last'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars(($a['d_first'] ?? '') . ' ' . ($a['d_last'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($a['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($a['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($a['reason']); ?></td>
                                <td><?php echo htmlspecialchars($a['status']); ?></td>
                                <td><?php echo htmlspecialchars($a['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">No appointments found.</div>
                <?php endif; ?>
            </section>

            <section style="margin-top:1.5rem;">
                <h3>Medical Records</h3>
                <?php if($records && $records->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Diagnosis</th>
                                <th>Prescription</th>
                                <th>Notes</th>
                                <th>Appointment</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($r = $records->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['id']); ?></td>
                                <td><?php echo htmlspecialchars(($r['p_first'] ?? '') . ' ' . ($r['p_last'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars(($r['d_first'] ?? '') . ' ' . ($r['d_last'] ?? '')); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($r['diagnosis'] ?? '')); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($r['prescription'] ?? '')); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($r['notes'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($r['appointment_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">No medical records found.</div>
                <?php endif; ?>
            </section>

            <section style="margin-top:1.5rem; margin-bottom:2rem;">
                <h3>Billing / Transactions</h3>
                <?php if($billings && $billings->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Appointment</th>
                                <th>Doctor</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Description</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($b = $billings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['id']); ?></td>
                                <td><?php echo htmlspecialchars(($b['p_first'] ?? '') . ' ' . ($b['p_last'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($b['appointment_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(($b['d_first'] ?? '') . ' ' . ($b['d_last'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars(number_format((float)($b['amount'] ?? 0), 2)); ?></td>
                                <td><?php echo htmlspecialchars($b['payment_status'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($b['method'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($b['service_description'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($b['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">No billing records found.</div>
                <?php endif; ?>
            </section>
        </div>
        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
