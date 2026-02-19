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

// Search and filter logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$records_query = "SELECT m.*, d.first_name, d.last_name FROM medical_records m 
                  JOIN doctors d ON m.doctor_id = d.id 
                  WHERE m.patient_id = ?";

$params = [$patient_id];
$types = 'i';

if($search) {
    $records_query .= " AND (d.first_name LIKE ? OR d.last_name LIKE ? OR m.diagnosis LIKE ? OR m.prescription LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

if($date_from) {
    $records_query .= " AND DATE(m.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if($date_to) {
    $records_query .= " AND DATE(m.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$records_query .= " ORDER BY m.created_at DESC";

$stmt = $conn->prepare($records_query);
if($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$records_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Clinic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <div style="margin-bottom: 28px;">
                <h2 style="margin: 0 0 4px 0; font-size: 1.8rem; font-weight: 700;">📋 Medical Records</h2>
                <p style="margin: 0; color: var(--color-text-secondary); font-size: 0.95rem;">View and manage your medical history</p>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <h3>🔍 Search & Filter</h3>
                <form method="GET" action="" style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="search-container">
                        <div class="search-input-group">
                            <span class="search-icon">🔍</span>
                            <input type="text" name="search" placeholder="Search by doctor name, diagnosis, or prescription..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <div class="filter-item">
                            <label for="date_from">Date From</label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="filter-item">
                            <label for="date_to">Date To</label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">🔎 Search</button>
                        <a href="view.php" class="btn-filter" style="background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05)); text-decoration: none; display: inline-flex;">↻ Clear Filter</a>
                    </div>
                </form>
                <?php if($search || $date_from || $date_to): ?>
                    <div class="records-results">
                        Found <strong><?php echo $records_result->num_rows; ?></strong> record(s) matching your criteria
                    </div>
                <?php endif; ?>
            </div>

            <!-- Records Display -->
            <?php if($records_result->num_rows > 0): ?>
                <div class="records-container">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Diagnosis</th>
                                <th>Prescription</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($record = $records_result->fetch_assoc()): ?>
                                <tr class="clickable-row" style="cursor: pointer;" 
                                    onclick="openRecordModal(<?php echo htmlspecialchars(json_encode($record)); ?>)">
                                    <td><strong>Dr. <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></strong></td>
                                    <td><?php echo date('M d, Y', strtotime($record['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars(substr($record['diagnosis'], 0, 50)); ?><?php echo strlen($record['diagnosis']) > 50 ? '...' : ''; ?></td>
                                    <td><?php echo htmlspecialchars(substr($record['prescription'], 0, 50)); ?><?php echo strlen($record['prescription']) > 50 ? '...' : ''; ?></td>
                                    <td><?php echo htmlspecialchars(substr($record['notes'] ?? '', 0, 40)); ?><?php echo strlen($record['notes'] ?? '') > 40 ? '...' : ''; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="records-container">
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>No medical records found<?php echo ($search || $date_from || $date_to) ? ' matching your criteria' : ''; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Record Detail Modal -->
            <div id="recordModal" class="modal" style="display: none;">
                <div class="modal-overlay" onclick="closeRecordModal()"></div>
                <div class="modal-dialog" style="max-width: 800px;">
                    <div class="modal-header">
                        <h3 class="modal-title" id="modalRecordTitle">Medical Record Details</h3>
                        <button class="modal-close" onclick="closeRecordModal()">✕</button>
                    </div>
                    <div class="modal-body" id="modalRecordBody">
                        <!-- Record details will be populated here -->
                    </div>
                    <div class="modal-footer">
                        <button class="btn-filter" onclick="closeRecordModal()">Close</button>
                        <button class="btn-search" onclick="printRecord()">🖨️ Print This Record</button>
                    </div>
                </div>
            </div>

            <div style="margin-top: 28px;">
                <a href="../dashboard.php" class="btn-filter">← Back to Dashboard</a>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>

    <style>
        .clickable-row {
            transition: background 0.2s ease;
        }
        
        .clickable-row:hover {
            background: rgba(174, 188, 36, 0.1) !important;
        }

        /* Modal Styling */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            cursor: pointer;
        }

        .modal-dialog {
            position: relative;
            background: var(--color-surface-charcoal);
            border: 1px solid rgba(174, 188, 36, 0.2);
            border-radius: 12px;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            background: rgba(255, 255, 255, 0.01);
        }

        .modal-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--color-text-primary);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--color-text-secondary);
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .modal-close:hover {
            color: var(--color-text-primary);
        }

        .modal-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .modal-body h4 {
            margin: 0 0 8px 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--color-accent-lime);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal-body p {
            margin: 0;
            color: var(--color-text-primary);
            line-height: 1.6;
            word-break: break-word;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            background: rgba(255, 255, 255, 0.01);
        }

        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 95%;
                max-height: 90vh;
            }

            .modal-body {
                padding: 16px;
            }

            .modal-footer {
                flex-direction: column;
            }

            .modal-footer button {
                width: 100%;
            }
        }

        @media print {
            .modal-overlay, .modal-close, .modal-footer, .modal-header {
                display: none !important;
            }

            .modal-dialog {
                position: static !important;
                max-width: 100% !important;
                height: auto !important;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
            }

            .modal-body {
                background: white !important;
                color: black !important;
            }

            .modal-body h4 {
                color: #333 !important;
            }

            .modal-body p {
                color: black !important;
            }
        }
    </style>

    <script>
        let currentRecord = {};

        function openRecordModal(record) {
            currentRecord = record;
            const modal = document.getElementById('recordModal');
            const modalBody = document.getElementById('modalRecordBody');
            const modalTitle = document.getElementById('modalRecordTitle');

            // Set title
            modalTitle.textContent = 'Dr. ' + record.first_name + ' ' + record.last_name + ' - Medical Record';

            // Build detailed content
            const detailsHTML = `
                <div>
                    <h4>Doctor Information</h4>
                    <p><strong>Dr. ${record.first_name} ${record.last_name}</strong></p>
                </div>

                <div>
                    <h4>Visit Date</h4>
                    <p>${new Date(record.created_at).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</p>
                </div>

                <div>
                    <h4>Diagnosis</h4>
                    <p>${record.diagnosis}</p>
                </div>

                <div>
                    <h4>Prescription</h4>
                    <p>${record.prescription}</p>
                </div>

                ${record.notes ? `
                <div>
                    <h4>Notes</h4>
                    <p>${record.notes}</p>
                </div>
                ` : ''}

                <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 16px; margin-top: 16px;">
                    <p style="font-size: 0.85rem; color: var(--color-text-secondary);">
                        Record ID: ${record.id}
                    </p>
                </div>
            `;

            modalBody.innerHTML = detailsHTML;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeRecordModal() {
            const modal = document.getElementById('recordModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function printRecord() {
            window.print();
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('recordModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal.querySelector('.modal-overlay')) {
                        closeRecordModal();
                    }
                });
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRecordModal();
            }
        });
    </script>
</body>
</html>
