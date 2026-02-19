<?php
session_start();
include '../config.php';

// Only allow admin users
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get various statistics
$stats = array();

// Total revenue
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM billing WHERE payment_status = 'paid'");
$stmt->execute();
$stats['total_revenue'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Pending revenue
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM billing WHERE payment_status = 'pending'");
$stmt->execute();
$stats['pending_revenue'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Appointments by status
$status_query = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
$status_result = $conn->query($status_query);
$stats['appointments_by_status'] = [];
while($row = $status_result->fetch_assoc()) {
    $stats['appointments_by_status'][$row['status']] = $row['count'];
}

// Payment methods distribution
$method_query = "SELECT method, COUNT(*) as count FROM billing GROUP BY method";
$method_result = $conn->query($method_query);
$stats['payment_methods'] = [];
while($row = $method_result->fetch_assoc()) {
    $stats['payment_methods'][$row['method']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <div class="admin-header">
                <h2>System Reports</h2>
                <a href="../admin.php" class="btn btn-secondary">← Back to Admin Panel</a>
            </div>

            <!-- Revenue Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>💰 Total Revenue</h3>
                    <p class="stat-number">$<?php echo number_format($stats['total_revenue'], 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>⏳ Pending Revenue</h3>
                    <p class="stat-number">$<?php echo number_format($stats['pending_revenue'], 2); ?></p>
                </div>
            </div>

            <!-- Appointments by Status -->
            <div class="report-section">
                <h3>Appointments by Status</h3>
                <div class="report-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stats['appointments_by_status'] as $status => $count): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(ucfirst($status)); ?></td>
                                <td><?php echo $count; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="report-section">
                <h3>Payment Methods Distribution</h3>
                <div class="report-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stats['payment_methods'] as $method => $count): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($method ?? 'Unknown'); ?></td>
                                <td><?php echo $count; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>