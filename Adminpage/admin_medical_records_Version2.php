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

// Build the query dynamically
$records_query = "SELECT m.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
                  FROM medical_records m
                  LEFT JOIN patients p ON m.patient_id = p.id
                  LEFT JOIN doctors d ON m.doctor_id = d.id
                  WHERE 1=1";
$params = [];
$types = '';

if($search) {
    $records_query .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR d.first_name LIKE ? OR d.last_name LIKE ? OR m.diagnosis LIKE ? OR m.prescription LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
    $types .= 'ssssss';
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

if(count($params) > 0) {
    $stmt = $conn->prepare($records_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $records = $stmt->get_result();
} else {
    $records = $conn->query($records_query);
}
$record_count = $records->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <div class="admin-header">
                <div>
                    <h2 style="margin: 0; color: var(--text-primary);">📋 Medical Records Management</h2>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View and manage all medical records in the system</p>
                </div>
                <a href="../admin.php" class="btn btn-secondary">← Back to Admin Panel</a>
            </div>

            <!-- Filter Section -->
            <form method="GET" class="filter-section">
                <div class="filter-group">
                    <div class="filter-item">
                        <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">🔍 Search</label>
                        <input type="text" id="search" name="search" placeholder="Patient, doctor, diagnosis, prescription..." 
                            class="search-input-group" value="<?php echo htmlspecialchars($search); ?>">
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
                    <a href="admin_medical_records_Version2.php" class="btn-filter">↺ Clear Filters</a>
                </div>
            </form>

            <!-- Results Counter -->
            <?php if($search || $date_from || $date_to): ?>
                <div style="background: rgba(174, 188, 36, 0.1); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; color: var(--color-accent-lime); font-weight: 500;">
                    ✓ Found <strong><?php echo $record_count; ?></strong> record<?php echo $record_count !== 1 ? 's' : ''; ?> matching your criteria
                </div>
            <?php endif; ?>

            <!-- Medical Records Table -->
            <?php if($record_count > 0): ?>
                <div class="records-container">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PATIENT</th>
                                <th>DOCTOR</th>
                                <th>DIAGNOSIS</th>
                                <th>PRESCRIPTION</th>
                                <th>NOTES</th>
                                <th>CREATED</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($r = $records->fetch_assoc()): ?>
                            <tr class="clickable-row" style="cursor: pointer;"
                                onclick="openRecordModal(<?php echo htmlspecialchars(json_encode($r)); ?>)">
                                <td><?php echo htmlspecialchars($r['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars(($r['p_first'] ?? 'N/A') . ' ' . ($r['p_last'] ?? '')); ?></strong></td>
                                <td><?php echo htmlspecialchars(($r['d_first'] ?? 'N/A') . ' ' . ($r['d_last'] ?? '')); ?></td>
                                <td title="<?php echo htmlspecialchars($r['diagnosis'] ?? ''); ?>">
                                    <?php 
                                        $diag = $r['diagnosis'] ?? '';
                                        echo htmlspecialchars(substr($diag, 0, 40)) . (strlen($diag) > 40 ? '...' : '');
                                    ?>
                                </td>
                                <td title="<?php echo htmlspecialchars($r['prescription'] ?? ''); ?>">
                                    <?php 
                                        $presc = $r['prescription'] ?? '';
                                        echo htmlspecialchars(substr($presc, 0, 40)) . (strlen($presc) > 40 ? '...' : '');
                                    ?>
                                </td>
                                <td title="<?php echo htmlspecialchars($r['notes'] ?? ''); ?>">
                                    <?php 
                                        $notes = $r['notes'] ?? '';
                                        echo htmlspecialchars(substr($notes, 0, 30)) . (strlen($notes) > 30 ? '...' : '');
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($r['created_at'] ?? '')); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">📭</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">No Records Found</h3>
                    <p style="margin: 0; color: var(--text-secondary);">
                        <?php echo ($search || $date_from || $date_to) ? 'No medical records match your search criteria.' : 'No medical records found.'; ?>
                    </p>
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
            nav, footer, .btn, a.btn, button, .navbar, .filter-section, .filter-actions, .modal-overlay, .modal-close, .modal-footer, .modal-header {
                display: none !important;
                visibility: hidden !important;
            }

            .container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .dashboard-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
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
                padding: 0.3in !important;
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
            const patientName = (record.p_first || 'Unknown') + ' ' + (record.p_last || '');
            const doctorName = (record.d_first || 'Unknown') + ' ' + (record.d_last || '');
            modalTitle.textContent = patientName + ' - Medical Record (Dr. ' + doctorName + ')';

            // Build detailed content
            const detailsHTML = `
                <div>
                    <h4>Patient Information</h4>
                    <p><strong>${patientName}</strong> (ID: ${record.patient_id})</p>
                </div>

                <div>
                    <h4>Doctor Information</h4>
                    <p><strong>Dr. ${doctorName}</strong></p>
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
                    <p>${record.diagnosis || 'N/A'}</p>
                </div>

                <div>
                    <h4>Prescription</h4>
                    <p>${record.prescription || 'N/A'}</p>
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
    <script src="../js/script.js"></script>
</body>
</html>