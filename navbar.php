<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine if in subdirectory
$is_subdir = (strpos($_SERVER['PHP_SELF'], '/patients/') !== false || 
              strpos($_SERVER['PHP_SELF'], '/doctors/') !== false ||
              strpos($_SERVER['PHP_SELF'], '/appointments/') !== false ||
              strpos($_SERVER['PHP_SELF'], '/medical_records/') !== false ||
              strpos($_SERVER['PHP_SELF'], '/billing/') !== false);

$base_path = $is_subdir ? '../' : '';

// User info
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? 'User';

// Check if page is active
function is_page_active($page, $dir = null) {
    global $current_page, $current_dir;
    if ($dir) {
        return ($current_dir === $dir && $current_page === $page);
    }
    return ($current_page === $page);
}
?>

<!-- Top Header Bar -->
<header class="header-bar">
    <div class="header-content">
        <button class="menu-toggle" id="menuToggle" type="button" title="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <a href="<?php echo $base_path; ?>index.php" class="logo-header">
            <span class="logo-icon">🏥</span>
            <span class="logo-text">Clinic</span>
        </a>
        <?php if ($is_logged_in): ?>
            <!-- Facebook-style Profile Dropdown (moved into header for DOM consistency) -->
            <div class="profile-dropdown" id="profileDropdown">
                <button class="profile-btn" type="button" title="Opening profile menu">
                    <div class="profile-avatar-small">👤</div>
                </button>
                
                <div class="profile-menu-dropdown" id="profileMenuDropdown">
                    <div class="profile-menu-header">
                        <div class="profile-avatar-large">👤</div>
                        <div class="profile-info-menu">
                            <div class="profile-name-menu"><?php echo htmlspecialchars($username); ?></div>
                            <div class="profile-role-menu"><?php echo ucfirst($user_role); ?></div>
                        </div>
                    </div>
                    
                    <div class="profile-menu-items">
                        <?php if ($user_role === 'patient'): ?>
                            <a href="<?php echo $base_path; ?>patients/view_profile.php" class="profile-menu-item">
                                <span class="menu-icon">👤</span>
                                <span class="menu-label">View Profile</span>
                            </a>
                            <a href="<?php echo $base_path; ?>patients/edit_profile.php" class="profile-menu-item">
                                <span class="menu-icon">✏️</span>
                                <span class="menu-label">Edit Profile</span>
                            </a>
                        <?php elseif ($user_role === 'doctor'): ?>
                            <a href="<?php echo $base_path; ?>doctors/view_profile.php" class="profile-menu-item">
                                <span class="menu-icon">👨‍⚕️</span>
                                <span class="menu-label">View Profile</span>
                            </a>
                            <a href="<?php echo $base_path; ?>doctors/edit_profile.php" class="profile-menu-item">
                                <span class="menu-icon">✏️</span>
                                <span class="menu-label">Edit Profile</span>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $base_path; ?>admin.php" class="profile-menu-item">
                                <span class="menu-icon">⚙️</span>
                                <span class="menu-label">Admin Panel</span>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-menu-divider"></div>
                    
                    <a href="<?php echo $base_path; ?>logout.php" class="profile-menu-item logout-item" onclick="return confirmLogout();">
                        <span class="menu-icon">🚪</span>
                        <span class="menu-label">Logout</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

<!-- Sidebar Navigation -->
<div id="mySidebar" class="sidebar">
    <!-- Navigation Links -->
    <nav class="sidebar-nav">
        <!-- Home -->
        <a href="<?php echo $base_path; ?>index.php" 
           class="nav-link <?php echo is_page_active('index.php') ? 'active' : ''; ?>"
           title="Home">
            <span class="icon">🏠</span>
            <span class="label">Home</span>
        </a>

        <?php if ($is_logged_in): ?>
            <!-- Dashboard -->
            <a href="<?php echo $base_path; ?>dashboard.php" 
               class="nav-link <?php echo is_page_active('dashboard.php') ? 'active' : ''; ?>"
               title="Dashboard">
                <span class="icon">📊</span>
                <span class="label">Dashboard</span>
            </a>

            <?php if ($user_role === 'admin'): ?>
                <div class="nav-section">
                    <p class="section-title">Admin</p>
                    <a href="<?php echo $base_path; ?>admin.php" 
                       class="nav-link <?php echo is_page_active('admin.php') ? 'active' : ''; ?>">
                        <span class="icon">🛠️</span>
                        <span class="label">Admin Panel</span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($user_role === 'patient'): ?>
                <!-- Patient Section -->
                <div class="nav-section">
                    <p class="section-title">Patient Menu</p>

                    <a href="<?php echo $base_path; ?>appointments/book.php" 
                       class="nav-link <?php echo is_page_active('book.php', 'appointments') ? 'active' : ''; ?>">
                        <span class="icon">📅</span>
                        <span class="label">Book Appointment</span>
                    </a>

                    <a href="<?php echo $base_path; ?>appointments/view.php" 
                       class="nav-link <?php echo is_page_active('view.php', 'appointments') ? 'active' : ''; ?>">
                        <span class="icon">📋</span>
                        <span class="label">My Appointments</span>
                    </a>

                    <a href="<?php echo $base_path; ?>medical_records/view.php" 
                       class="nav-link <?php echo is_page_active('view.php', 'medical_records') ? 'active' : ''; ?>">
                        <span class="icon">📄</span>
                        <span class="label">Medical Records</span>
                    </a>

                    <a href="<?php echo $base_path; ?>billing/view.php" 
                       class="nav-link <?php echo is_page_active('view.php', 'billing') ? 'active' : ''; ?>">
                        <span class="icon">💰</span>
                        <span class="label">Billing</span>
                    </a>
                </div>

            <?php elseif ($user_role === 'doctor'): ?>
                <!-- Doctor Section -->
                <div class="nav-section">
                    <p class="section-title">Doctor</p>

                    <a href="<?php echo $base_path; ?>appointments/manage.php" 
                       class="nav-link <?php echo is_page_active('manage.php', 'appointments') ? 'active' : ''; ?>">
                        <span class="icon">📅</span>
                        <span class="label">Appointments</span>
                    </a>

                    <a href="<?php echo $base_path; ?>doctors/manage_patients.php" 
                       class="nav-link <?php echo is_page_active('manage_patients.php', 'doctors') ? 'active' : ''; ?>">
                        <span class="icon">👥</span>
                        <span class="label">My Patients</span>
                    </a>

                    <a href="<?php echo $base_path; ?>medical_records/manage.php" 
                       class="nav-link <?php echo is_page_active('manage.php', 'medical_records') ? 'active' : ''; ?>">
                        <span class="icon">📋</span>
                        <span class="label">Medical Records</span>
                    </a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Auth Links -->
            <div class="nav-section">
                <a href="<?php echo $base_path; ?>login.php" 
                   class="nav-link <?php echo is_page_active('login.php') ? 'active' : ''; ?>">
                    <span class="icon">🔐</span>
                    <span class="label">Login</span>
                </a>

                <!-- <a href="<?php echo $base_path; ?>register.php" 
                   class="nav-link <?php echo is_page_active('register.php') ? 'active' : ''; ?>">
                    <span class="icon">✍️</span>
                    <span class="label">Register</span>
                </a> -->
            </div>
        <?php endif; ?>
    </nav>
</div>

<script src="<?php echo $base_path; ?>js/script.js"></script>



