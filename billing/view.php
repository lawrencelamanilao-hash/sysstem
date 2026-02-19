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

// Get patient's billing records
$billing_query = "SELECT b.*, d.first_name, d.last_name FROM billing b 
                  LEFT JOIN appointments a ON b.appointment_id = a.id
                  LEFT JOIN doctors d ON a.doctor_id = d.id
                  WHERE b.patient_id = ? 
                  ORDER BY b.created_at DESC";
$stmt = $conn->prepare($billing_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$billing_result = $stmt->get_result();
$stmt->close();

// Calculate totals
$totals_query = "SELECT SUM(CASE WHEN payment_status='pending' THEN amount ELSE 0 END) as pending,
                        SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) as paid
                 FROM billing WHERE patient_id = ?";
$stmt = $conn->prepare($totals_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$totals_result = $stmt->get_result();
$totals = $totals_result->fetch_assoc();
$stmt->close();
// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay']) && isset($_POST['billing_id'])) {
    $billing_id = intval($_POST['billing_id']);
    $method = (isset($_POST['method']) && $_POST['method'] === 'online') ? 'online' : 'cash';
    // verify billing belongs to this patient
    $check = $conn->prepare("SELECT id, amount FROM billing WHERE id = ? AND patient_id = ?");
    $check->bind_param("ii", $billing_id, $patient_id);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows === 1) {
        // mark as paid (simple flow)
        $update = $conn->prepare("UPDATE billing SET payment_status = 'paid' WHERE id = ? AND patient_id = ?");
        $update->bind_param("ii", $billing_id, $patient_id);
        $update->execute();
        $update->close();
        $message = "Payment recorded successfully.";
    } else {
        $error = "Invalid billing record.";
    }
    $check->close();
}

// Get pending bills for payment dropdown
$pending_stmt = $conn->prepare("SELECT id, amount, created_at, service_description FROM billing WHERE patient_id = ? AND payment_status = 'pending'");
$pending_stmt->bind_param("i", $patient_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">>
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <h2>Billing Information</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Pending Balance</h3>
                    <p class="stat-number">$<?php echo number_format($totals['pending'] ?? 0, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Paid Amount</h3>
                    <p class="stat-number">$<?php echo number_format($totals['paid'] ?? 0, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Charges</h3>
                    <p class="stat-number">$<?php echo number_format(($totals['pending'] ?? 0) + ($totals['paid'] ?? 0), 2); ?></p>
                </div>
            </div>

            <?php if($billing_result->num_rows > 0): ?>
                <h3 style="margin-top: 2rem; color: #2c3e50;">Billing History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Service</th>
                            <th>Doctor</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($bill = $billing_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($bill['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($bill['service_description']); ?></td>
                                <td><?php echo ($bill['first_name'] && $bill['last_name']) ? 'Dr. ' . htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) : 'N/A'; ?></td>
                                <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                                <td>
                                    <span style="background-color: <?php echo ($bill['payment_status'] == 'paid') ? '#e8f5e9' : '#fff3e0'; ?>; 
                                                 color: <?php echo ($bill['payment_status'] == 'paid') ? '#388e3c' : '#e65100'; ?>; 
                                                 padding: 0.25rem 0.75rem; 
                                                 border-radius: 20px; 
                                                 font-size: 0.85rem;
                                                 font-weight: 500;">
                                        <?php echo ucfirst($bill['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info" style="margin-top: 2rem;">No billing records found.</div>
            <?php endif; ?>

            <!-- Payment section -->
            <div style="margin-top: 2rem;">
                <h3>Make a Payment</h3>
                <?php if(isset($message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if($pending_result && $pending_result->num_rows > 0): ?>
                    <form method="post" id="paymentForm">
                        <label for="billingSelect">Select pending bill:</label>
                        <select name="billing_id" id="billingSelect" style="display:block; margin:0.5rem 0; padding:0.4rem;">
                            <?php while($pb = $pending_result->fetch_assoc()): ?>
                                <option value="<?php echo $pb['id']; ?>" data-amount="<?php echo $pb['amount']; ?>" data-service="<?php echo htmlspecialchars($pb['service_description']); ?>">
                                    <?php echo date('M d, Y', strtotime($pb['created_at'])); ?> — <?php echo htmlspecialchars($pb['service_description']); ?> — $<?php echo number_format($pb['amount'],2); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <div style="margin:0.5rem 0;">
                            <label style="margin-right:1rem;"><input type="radio" name="method" value="cash" checked> Pay via Cash</label>
                            <label><input type="radio" name="method" value="online"> Pay Online (QR)</label>
                        </div>

                        <div id="qrContainer" style="display:none; margin:0.5rem 0;">
                            <p>Scan this QR to pay:</p>
                            <img id="qrImage" src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" alt="QR Code" style="width:200px;height:200px;border:1px solid #ddd;" />
                        </div>

                        <button type="submit" name="pay" class="btn btn-primary">Confirm Payment</button>
                    </form>
                    <script>
                        (function(){
                            var billingSelect = document.getElementById('billingSelect');
                            var qrContainer = document.getElementById('qrContainer');
                            var qrImage = document.getElementById('qrImage');
                            var radios = document.querySelectorAll('input[name="method"]');

                            function updateQR(){
                                var opt = billingSelect.options[billingSelect.selectedIndex];
                                var billId = opt.value;
                                var amount = opt.getAttribute('data-amount');
                                var service = encodeURIComponent(opt.getAttribute('data-service'));
                                var payload = 'clinic-pay://bill?id=' + billId + '&amount=' + amount + '&service=' + service;
                                qrImage.src = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' + encodeURIComponent(payload);
                            }

                            radios.forEach(function(r){
                                r.addEventListener('change', function(){
                                    if(this.value === 'online'){
                                        qrContainer.style.display = 'block';
                                        updateQR();
                                    } else {
                                        qrContainer.style.display = 'none';
                                    }
                                });
                            });

                            billingSelect.addEventListener('change', function(){
                                // refresh QR if visible
                                if(document.querySelector('input[name="method"]:checked').value === 'online'){
                                    updateQR();
                                }
                            });
                        })();
                    </script>
                <?php else: ?>
                    <div class="alert alert-info">No pending bills to pay.</div>
                <?php endif; ?>

                <div style="margin-top: 1rem;">
                    <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
