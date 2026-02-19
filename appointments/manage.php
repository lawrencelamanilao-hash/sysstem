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


$appointments_query = "SELECT a.*, p.first_name, p.last_name, p.email FROM appointments a 
                       JOIN patients p ON a.patient_id = p.id 
                       WHERE a.doctor_id = ? 
                       ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($appointments_query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>My Appointments</h2>
            </div>
            
            <?php if($appointments_result->num_rows > 0): ?>
                <!-- Search Box -->
                <div style="margin-bottom: 20px;">
                    <input type="text" id="searchAppointments" placeholder="🔍 Search by patient name, email, or reason..." 
                           style="width: 100%; padding: 10px 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.3s;"
                           onkeyup="filterAppointments()" 
                           onfocus="this.style.borderColor='#667eea'" 
                           onblur="this.style.borderColor='#ddd'">
                </div>
                
                <table id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($appointment = $appointments_result->fetch_assoc()): ?>
                            <tr class="appointment-row" data-search="<?php echo strtolower(htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name'] . ' ' . $appointment['email'] . ' ' . $appointment['reason'])); ?>">
                                <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['reason']); ?></td>
                                <td>
                                    <span class="<?php echo 'status-' . $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="add_record.php?appointment_id=<?php echo $appointment['id']; ?>">Add Record</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- No Results Message -->
                <div id="noResults" style="display: none; text-align: center; padding: 40px; color: #999;">
                    <p style="font-size: 16px;">No appointments found matching your search.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No appointments found.</div>
            <?php endif; ?>

            <div style="margin-top: 2rem;">
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
    
    <script>
        function filterAppointments() {
            let searchInput = document.getElementById('searchAppointments').value.toLowerCase();
            let rows = document.querySelectorAll('.appointment-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                let searchData = row.getAttribute('data-search');
                if (searchData.includes(searchInput)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            let noResults = document.getElementById('noResults');
            if (visibleCount === 0 && searchInput !== '') {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
    </script>
</body>
</html>
