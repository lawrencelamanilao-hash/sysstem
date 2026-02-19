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
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// Build the query dynamically
$users_query = "SELECT u.*, 
                CASE 
                    WHEN u.role = 'patient' THEN CONCAT(p.first_name, ' ', p.last_name)
                    WHEN u.role = 'doctor' THEN CONCAT(d.first_name, ' ', d.last_name)
                    ELSE 'Admin'
                END as full_name,
                p.email as patient_email,
                d.email as doctor_email
                FROM users u
                LEFT JOIN patients p ON u.patient_id = p.id
                LEFT JOIN doctors d ON u.doctor_id = d.id
                WHERE 1=1";
$params = [];
$types = '';

if($search) {
    $users_query .= " AND (u.username LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR d.first_name LIKE ? OR d.last_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
    $types .= 'sssss';
}

if($role_filter) {
    $users_query .= " AND u.role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

$users_query .= " ORDER BY u.role, u.created_at DESC";

if(count($params) > 0) {
    $stmt = $conn->prepare($users_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query($users_query);
}
$user_count = $users->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container">
        <div class="dashboard-container">
            <div class="admin-header">
                <div>
                    <h2 style="margin: 0; color: var(--text-primary);">👥 User Management</h2>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View and manage all users in the system</p>
                </div>
                <a href="../admin.php" class="btn btn-secondary">← Back to Admin Panel</a>
            </div>

            <!-- Filter Section -->
            <form method="GET" class="filter-section">
                <div class="filter-group">
                    <div class="filter-item">
                        <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">🔍 Search</label>
                        <input type="text" id="search" name="search" placeholder="Username, name..." 
                            class="search-input-group" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-item">
                        <label for="role" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">📋 Role</label>
                        <select id="role" name="role" 
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-surface); color: var(--text-primary); font-size: 0.95rem;">
                            <option value="">All Roles</option>
                            <option value="patient" <?php echo $role_filter === 'patient' ? 'selected' : ''; ?>>Patient</option>
                            <option value="doctor" <?php echo $role_filter === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn-search">🔎 Search</button>
                    <a href="admin_users_Version2.php" class="btn-filter">↺ Clear Filters</a>
                </div>
            </form>

            <!-- Results Counter -->
            <?php if($search || $role_filter): ?>
                <div style="background: rgba(174, 188, 36, 0.1); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; color: var(--color-accent-lime); font-weight: 500;">
                    ✓ Found <strong><?php echo $user_count; ?></strong> user<?php echo $user_count !== 1 ? 's' : ''; ?> matching your criteria
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <?php if($user_count > 0): ?>
                <div class="records-container">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>USERNAME</th>
                                <th>FULL NAME</th>
                                <th>EMAIL</th>
                                <th>ROLE</th>
                                <th>CREATED</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($u['patient_email'] ?? $u['doctor_email'] ?? $u['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo strtolower($u['role']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($u['role'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'] ?? '')); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">👤</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">No Users Found</h3>
                    <p style="margin: 0; color: var(--text-secondary);">
                        <?php echo ($search || $role_filter) ? 'No users match your search criteria.' : 'No users found.'; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
    <script src="../js/script.js"></script>
</body>
</html>