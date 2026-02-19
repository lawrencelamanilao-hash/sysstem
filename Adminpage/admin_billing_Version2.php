<?php
session_start();
include '../config.php';

// Only allow admin users
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$payment_status = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';

// Build the query dynamically
$billing_query = "SELECT b.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last, a.id AS appointment_id
                  FROM billing b
                  LEFT JOIN patients p ON b.patient_id = p.id
                  LEFT JOIN appointments a ON b.appointment_id = a.id
                  LEFT JOIN doctors d ON a.doctor_id = d.id
                  WHERE 1=1";
$params = [];
$types = '';

if($search) {
    $billing_query .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR d.first_name LIKE ? OR d.last_name LIKE ? OR b.service_description LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
    $types .= 'sssss';
}

if($date_from) {
    $billing_query .= " AND DATE(b.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if($date_to) {
    $billing_query .= " AND DATE(b.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if($payment_status) {
    $billing_query .= " AND b.payment_status = ?";
    $params[] = $payment_status;
    $types .= 's';
}

$billing_query .= " ORDER BY b.created_at DESC";

if(count($params) > 0) {
    $stmt = $conn->prepare($billing_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $billings = $stmt->get_result();
} else {
    $billings = $conn->query($billing_query);
}
$billing_count = $billings->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Management - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <div class="admin-header">
                <div>
                    <h2 style="margin: 0; color: var(--text-primary);">💰 Billing & Transactions Management</h2>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View and manage all billing records in the system</p>
                </div>
                <a href="../admin.php" class="btn btn-secondary">← Back to Admin Panel</a>
            </div>

            <!-- Filter Section -->
            <form method="GET" class="filter-section">
                <div class="filter-group">
                    <div class="filter-item">
                        <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">🔍 Search</label>
                        <input type="text" id="search" name="search" placeholder="Patient, doctor, service..." 
                            class="search-input-group" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-item">
                        <label for="payment_status" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">📊 Payment Status</label>
                        <select id="payment_status" name="payment_status" 
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-surface); color: var(--text-primary); font-size: 0.95rem;">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $payment_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $payment_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $payment_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label for="date_from" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">📅 From Date</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" 
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-surface); color: var(--text-primary); font-size: 0.95rem;">
                    </div>
                    
                    <div class="filter-item">
                        <label for="date_to" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">📅 To Date</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" 
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-surface); color: var(--text-primary); font-size: 0.95rem;">
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn-search">🔎 Search</button>
                    <a href="admin_billing_Version2.php" class="btn-filter">↺ Clear Filters</a>
                </div>
            </form>

            <!-- Results Counter -->
            <?php if($search || $date_from || $date_to || $payment_status): ?>
                <div style="background: rgba(174, 188, 36, 0.1); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; color: var(--color-accent-lime); font-weight: 500;">
                    ✓ Found <strong><?php echo $billing_count; ?></strong> record<?php echo $billing_count !== 1 ? 's' : ''; ?> matching your criteria
                </div>
            <?php endif; ?>

            <!-- Billing Records Table -->
            <?php if($billing_count > 0): ?>
                <div class="records-container">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PATIENT</th>
                                <th>DOCTOR</th>
                                <th>AMOUNT</th>
                                <th>STATUS</th>
                                <th>METHOD</th>
                                <th>DESCRIPTION</th>
                                <th>CREATED</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($b = $billings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars(($b['p_first'] ?? 'N/A') . ' ' . ($b['p_last'] ?? '')); ?></strong></td>
                                <td><?php echo htmlspecialchars(($b['d_first'] ?? 'N/A') . ' ' . ($b['d_last'] ?? '')); ?></td>
                                <td style="color: #AEBC24; font-weight: 600;">$<?php echo htmlspecialchars(number_format((float)($b['amount'] ?? 0), 2)); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($b['payment_status']); ?>">
                                        <?php echo htmlspecialchars($b['payment_status'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($b['method'] ?? 'N/A'); ?></td>
                                <td title="<?php echo htmlspecialchars($b['service_description'] ?? ''); ?>">
                                    <?php 
                                        $desc = $b['service_description'] ?? '';
                                        echo htmlspecialchars(substr($desc, 0, 30)) . (strlen($desc) > 30 ? '...' : '');
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($b['created_at'] ?? '')); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">💳</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">No Records Found</h3>
                    <p style="margin: 0; color: var(--text-secondary);">
                        <?php echo ($search || $date_from || $date_to || $payment_status) ? 'No billing records match your search criteria.' : 'No billing records found.'; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>