<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

$stats = array();
$appointments_data = null;
$records_data = null;
$billings_data = null;

if($role == 'admin') {
    // Fetch appointments with patient & doctor info
    $appointments_query = "SELECT a.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
                          FROM appointments a
                          LEFT JOIN patients p ON a.patient_id = p.id
                          LEFT JOIN doctors d ON a.doctor_id = d.id
                          ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $appointments_data = $conn->query($appointments_query);
    
    // Fetch medical records with patient & doctor info
    $records_query = "SELECT m.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
                      FROM medical_records m
                      LEFT JOIN patients p ON m.patient_id = p.id
                      LEFT JOIN doctors d ON m.doctor_id = d.id
                      ORDER BY m.created_at DESC";
    $records_data = $conn->query($records_query);
    
    // Fetch billing records with patient & doctor info
    $billing_query = "SELECT b.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last, a.id AS appointment_id
                      FROM billing b
                      LEFT JOIN patients p ON b.patient_id = p.id
                      LEFT JOIN appointments a ON b.appointment_id = a.id
                      LEFT JOIN doctors d ON a.doctor_id = d.id
                      ORDER BY b.created_at DESC";
    $billings_data = $conn->query($billing_query);
    
    // Get admin stats
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['appointments'] = $result->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT patient_id) as count FROM appointments");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['patients'] = $result->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM medical_records");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['records'] = $result->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM billing");
    $stmt->execute();
    $result = $stmt->get_result();
    $bill_data = $result->fetch_assoc();
    $stats['total_revenue'] = $bill_data['total'] ?? 0;
    $stmt->close();
} else if($role == 'patient') {
    $patient_id = $user['patient_id'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $appointments = $stmt->get_result();
    $stats['appointments'] = $appointments->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM medical_records WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $records = $stmt->get_result();
    $stats['records'] = $records->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM billing WHERE patient_id = ? AND payment_status = 'pending'");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $pending_bills = $stmt->get_result();
    $bill_data = $pending_bills->fetch_assoc();
    $stats['pending_bills'] = $bill_data['total'] ?? 0;
    $stmt->close();
} else {
    $doctor_id = $user['doctor_id'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $appointments = $stmt->get_result();
    $stats['appointments'] = $appointments->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT patient_id) as count FROM appointments WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $patients = $stmt->get_result();
    $stats['patients'] = $patients->fetch_assoc()['count'];
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM medical_records WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $records = $stmt->get_result();
    $stats['records'] = $records->fetch_assoc()['count'];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clinic Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            .navbar, .print-btn, .footer, .quick-actions, .stat-link {
                display: none !important;
            }
            .container {
                padding: 20px !important;
                max-width: 100%;
            }
            .dashboard-container {
                max-width: 100%;
            }
            .dashboard-header {
                page-break-after: avoid;
                margin-bottom: 20px;
                border-bottom: 3px solid #667eea;
                padding-bottom: 15px;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                page-break-after: avoid;
                margin-bottom: 30px;
            }
            .stat-card {
                border: 1px solid #ddd;
                padding: 15px;
                page-break-inside: avoid;
                background: #f9f9f9;
            }
            section {
                page-break-inside: avoid;
                margin-bottom: 30px;
                page-break-after: avoid;
            }
            table {
                width: 100% !important;
                border-collapse: collapse;
                font-size: 12px;
            }
            table th {
                background: #667eea !important;
                color: white !important;
                padding: 8px !important;
                text-align: left;
            }
            table td {
                padding: 8px !important;
                border: 1px solid #ddd;
            }
            table tr:nth-child(even) {
                background: #f9f9f9;
            }
            h2, h3 {
                margin-top: 0;
            }
        }
        
        .print-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        
        .print-btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <?php if($role == 'admin'): ?>
                <!-- Admin Dashboard -->
                <div class="dashboard-header">
                    <h2>Admin Dashboard</h2>
                    <p>Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong>! Overview of all clinic operations.</p>
                    <button class="print-btn" onclick="window.print()">Print Dashboard</button>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Appointments</h3>
                        <p class="stat-number"><?php echo $stats['appointments']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Unique Patients</h3>
                        <p class="stat-number"><?php echo $stats['patients']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Medical Records</h3>
                        <p class="stat-number"><?php echo $stats['records']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Revenue</h3>
                        <p class="stat-number">$<?php echo number_format($stats['total_revenue'], 2); ?></p>
                    </div>
                </div>

                <!-- Appointments Section -->
                <section>
                    <h3>All Appointments</h3>
                    <?php if($appointments_data && $appointments_data->num_rows > 0): ?>
                        <table class="admin-table">
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
                            <?php while($a = $appointments_data->fetch_assoc()): ?>
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

                <!-- Medical Records Section -->
                <section>
                    <h3>All Medical Records</h3>
                    <?php if($records_data && $records_data->num_rows > 0): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Diagnosis</th>
                                    <th>Prescription</th>
                                    <th>Notes</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($r = $records_data->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['id']); ?></td>
                                    <td><?php echo htmlspecialchars(($r['p_first'] ?? '') . ' ' . ($r['p_last'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(($r['d_first'] ?? '') . ' ' . ($r['d_last'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(substr($r['diagnosis'] ?? '', 0, 50)); ?></td>
                                    <td><?php echo htmlspecialchars(substr($r['prescription'] ?? '', 0, 50)); ?></td>
                                    <td><?php echo htmlspecialchars(substr($r['notes'] ?? '', 0, 50)); ?></td>
                                    <td><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No medical records found.</div>
                    <?php endif; ?>
                </section>

                <!-- Billing Section -->
                <section>
                    <h3>All Billing / Transactions</h3>
                    <?php if($billings_data && $billings_data->num_rows > 0): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment Method</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($b = $billings_data->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($b['id']); ?></td>
                                    <td><?php echo htmlspecialchars(($b['p_first'] ?? '') . ' ' . ($b['p_last'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(($b['d_first'] ?? '') . ' ' . ($b['d_last'] ?? '')); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format((float)($b['amount'] ?? 0), 2)); ?></td>
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
            <?php else: ?>
                <!-- Patient / Doctor Dashboard -->
                <div class="dashboard-header">
                    <div>
                        <h2 style="margin: 0; font-size: 2rem; color: var(--color-text-primary);">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                        <p style="margin: 0.5rem 0 0 0; color: var(--color-text-secondary); font-size: 0.95rem;">Role: <strong style="color: var(--color-accent-lime);"><?php echo ucfirst($role); ?></strong></p>
                    </div>
                </div>

                <!-- Enhanced Stats Grid -->
                <div class="stats-grid">
                    <?php if($role == 'patient'): ?>
                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(174, 188, 36, 0.05)); border: 1px solid rgba(102, 126, 234, 0.2); border-radius: 12px; padding: 28px; transition: all 0.3s ease;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 16px 0; color: var(--color-text-primary); font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">Appointments</h3>
                                <p class="stat-number" style="margin: 0 0 8px 0; font-size: 3rem; font-weight: 700; color: var(--color-accent-lime);"><?php echo $stats['appointments']; ?></p>
                                <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.9rem;">Total appointments booked</p>
                            </div>
                            <a href="appointments/view.php" class="stat-cta view">View All →</a>
                        </div>

                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(174, 188, 36, 0.05)); border: 1px solid rgba(76, 175, 80, 0.2); border-radius: 12px; padding: 28px; transition: all 0.3s ease;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 16px 0; color: var(--color-text-primary); font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">Medical Records</h3>
                                <p class="stat-number" style="margin: 0 0 8px 0; font-size: 3rem; font-weight: 700; color: var(--color-accent-lime);"><?php echo $stats['records']; ?></p>
                                <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.9rem;">Medical records on file</p>
                            </div>
                            <a href="medical_records/view.php" class="stat-cta green">View All →</a>
                        </div>

                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(174, 188, 36, 0.05)); border: 1px solid rgba(255, 152, 0, 0.2); border-radius: 12px; padding: 28px; transition: all 0.3s ease;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 16px 0; color: var(--color-text-primary); font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">Pending Bills</h3>
                                <p class="stat-number" style="margin: 0 0 8px 0; font-size: 3rem; font-weight: 700; color: #FF6B35;">$<?php echo number_format($stats['pending_bills'], 2); ?></p>
                                <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.9rem;">Amount pending payment</p>
                            </div>
                            <a href="billing/view.php" class="stat-cta orange">Pay Now →</a>
                        </div>
                    <?php else: ?>
                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(174, 188, 36, 0.05)); border: 1px solid rgba(102, 126, 234, 0.2); border-radius: 12px; padding: 28px; transition: all 0.3s ease;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 16px 0; color: var(--color-text-primary); font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">Appointments</h3>
                                <p class="stat-number" style="margin: 0 0 8px 0; font-size: 3rem; font-weight: 700; color: var(--color-accent-lime);"><?php echo $stats['appointments']; ?></p>
                                <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.9rem;">Scheduled appointments</p>
                            </div>
                            <a href="appointments/manage.php" class="stat-cta view">View All →</a>
                        </div>

                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(233, 30, 99, 0.1), rgba(174, 188, 36, 0.05)); border: 1px solid rgba(233, 30, 99, 0.2); border-radius: 12px; padding: 28px; transition: all 0.3s ease;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 16px 0; color: var(--color-text-primary); font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">Patients</h3>
                                <p class="stat-number" style="margin: 0 0 8px 0; font-size: 3rem; font-weight: 700; color: var(--color-accent-lime);"><?php echo $stats['patients']; ?></p>
                                <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.9rem;">Active patients under care</p>
                            </div>
                            <a href="doctors/manage_patients.php" class="stat-cta view" style="background: linear-gradient(135deg, #E91E63, #C2185B);">View All →</a>
                        </div>

                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(174, 188, 36, 0.05)); border: 1px solid rgba(76, 175, 80, 0.2); border-radius: 12px; padding: 28px; transition: all 0.3s ease;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 16px 0; color: var(--color-text-primary); font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">Medical Records</h3>
                                <p class="stat-number" style="margin: 0 0 8px 0; font-size: 3rem; font-weight: 700; color: var(--color-accent-lime);"><?php echo $stats['records']; ?></p>
                                <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.9rem;">Records created and managed</p>
                            </div>
                            <a href="medical_records/manage.php" class="stat-cta green">View All →</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions Section -->
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="quick-actions-grid">
                        <?php if($role == 'patient'): ?>
                            <a href="appointments/book.php" class="tile lime"><span>Book an Appointment</span></a>
                            <a href="medical_records/view.php" class="tile green"><span>View Medical Records</span></a>
                            <a href="billing/view.php" class="tile orange"><span>Manage Billing</span></a>
                        <?php else: ?>
                            <a href="appointments/manage.php" class="tile lime"><span>Manage Appointments</span></a>
                            <a href="medical_records/manage.php" class="tile green"><span>Manage Medical Records</span></a>
                            <a href="doctors/view_profile.php" class="tile view"><span>View My Profile</span></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Card -->
                <div style="background: linear-gradient(135deg, rgba(174,188,36,0.1), rgba(0,0,0,0.05)); border: 1px solid rgba(174, 188, 36, 0.2); border-radius: 12px; padding: 28px; margin-bottom: 20px;">
                    <h3 style="margin: 0 0 12px 0; color: var(--color-accent-lime); font-weight: 700;">Need Help?</h3>
                    <p style="margin: 0; color: var(--color-text-secondary); line-height: 1.6;">
                        <?php 
                        if($role == 'patient') {
                            echo 'Book appointments with our qualified doctors, manage your medical records, and keep track of your health in one place. Use the quick actions above to get started.';
                        } else {
                            echo 'Manage your patient appointments, maintain comprehensive medical records, and track patient health progress efficiently.';
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
