<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clinic System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Profile -->
        <aside class="sidebar">
            <div class="sidebar-content">
                <!-- Profile Section -->
                <div class="profile-section">
                    <div class="profile-avatar">
                        <img src="https://avatars.githubusercontent.com/u/1?v=4" alt="Profile">
                    </div>
                    <h2 class="profile-name">Dr. Alex Johnson</h2>
                    <p class="profile-role">Medical Director</p>
                    <p class="profile-status">
                        <span class="status-indicator online"></span>
                        Active
                    </p>
                </div>

                <!-- Navigation Menu -->
                <nav class="sidebar-nav">
                    <a href="#dashboard" class="nav-item active" data-tooltip="Dashboard">
                        <span class="nav-icon">📊</span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                    <a href="#appointments" class="nav-item" data-tooltip="Appointments">
                        <span class="nav-icon">📅</span>
                        <span class="nav-label">Appointments</span>
                    </a>
                    <a href="#patients" class="nav-item" data-tooltip="Patients">
                        <span class="nav-icon">👥</span>
                        <span class="nav-label">Patients</span>
                    </a>
                    <a href="#records" class="nav-item" data-tooltip="Records">
                        <span class="nav-icon">📋</span>
                        <span class="nav-label">Medical Records</span>
                    </a>
                    <a href="#billing" class="nav-item" data-tooltip="Billing">
                        <span class="nav-icon">💰</span>
                        <span class="nav-label">Billing</span>
                    </a>
                </nav>

                <!-- Quick Stats -->
                <div class="quick-stats">
                    <div class="stat-item">
                        <span class="stat-number">24</span>
                        <span class="stat-label">Today</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">1.2k</span>
                        <span class="stat-label">Patients</span>
                    </div>
                </div>

                <!-- Sidebar Footer -->
                <div class="sidebar-footer">
                    <a href="#settings" class="footer-link">⚙️ Settings</a>
                    <a href="#logout" class="footer-link">🚪 Logout</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Welcome back, Dr. Johnson</p>
                </div>
                <div class="header-right">
                    <input type="text" class="search-box" placeholder="Search appointments, patients...">
                    <button class="notification-btn">
                        🔔
                        <span class="notification-badge">3</span>
                    </button>
                </div>
            </header>

            <!-- Content Grid -->
            <section class="content-grid">
                <!-- Stats Cards Row -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3>Today's Appointments</h3>
                            <span class="stat-icon">📅</span>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-value">12</div>
                            <p class="stat-change positive">
                                <span>↑</span> 3 from yesterday
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3>New Patients</h3>
                            <span class="stat-icon">👤</span>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-value">8</div>
                            <p class="stat-change positive">
                                <span>↑</span> 2 this week
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3>Pending Records</h3>
                            <span class="stat-icon">📄</span>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-value">24</div>
                            <p class="stat-change warning">
                                <span>!</span> Review needed
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3>System Health</h3>
                            <span class="stat-icon">⚡</span>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-value">98%</div>
                            <p class="stat-change positive">
                                <span>✓</span> Optimal
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Repository-Style Cards -->
                <div class="repo-section">
                    <div class="section-header">
                        <h2>Active Management Modules</h2>
                        <button class="view-more-btn">View All →</button>
                    </div>

                    <div class="repo-cards-container">
                        <!-- Card 1: Appointments -->
                        <div class="repo-card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <span class="card-icon-primary">📅</span>
                                    <h3 class="card-title">Appointments Management</h3>
                                </div>
                                <span class="card-status active">Active</span>
                            </div>
                            <p class="card-description">Manage and schedule patient appointments with real-time availability tracking.</p>
                            <div class="card-stats">
                                <div class="card-stat">
                                    <span class="card-stat-label">Today</span>
                                    <span class="card-stat-value">12</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Week</span>
                                    <span class="card-stat-value">54</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Utilization</span>
                                    <span class="card-stat-value">94%</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="card-action-btn">View Details</button>
                            </div>
                        </div>

                        <!-- Card 2: Medical Records -->
                        <div class="repo-card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <span class="card-icon-primary">📋</span>
                                    <h3 class="card-title">Medical Records</h3>
                                </div>
                                <span class="card-status active">Active</span>
                            </div>
                            <p class="card-description">Access and maintain comprehensive electronic medical records with secure encryption.</p>
                            <div class="card-stats">
                                <div class="card-stat">
                                    <span class="card-stat-label">Total Records</span>
                                    <span class="card-stat-value">1.2k</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Updated Today</span>
                                    <span class="card-stat-value">34</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Compliance</span>
                                    <span class="card-stat-value">100%</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="card-action-btn">View Details</button>
                            </div>
                        </div>

                        <!-- Card 3: Patient Management -->
                        <div class="repo-card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <span class="card-icon-primary">👥</span>
                                    <h3 class="card-title">Patient Management</h3>
                                </div>
                                <span class="card-status active">Active</span>
                            </div>
                            <p class="card-description">Manage patient profiles, demographics, and medical history in a centralized system.</p>
                            <div class="card-stats">
                                <div class="card-stat">
                                    <span class="card-stat-label">Active Patients</span>
                                    <span class="card-stat-value">1.2k</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">New This Month</span>
                                    <span class="card-stat-value">156</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Engagement</span>
                                    <span class="card-stat-value">87%</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="card-action-btn">View Details</button>
                            </div>
                        </div>

                        <!-- Card 4: Billing -->
                        <div class="repo-card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <span class="card-icon-primary">💰</span>
                                    <h3 class="card-title">Billing & Payments</h3>
                                </div>
                                <span class="card-status active">Active</span>
                            </div>
                            <p class="card-description">Process invoices and track payments with integrated billing analytics.</p>
                            <div class="card-stats">
                                <div class="card-stat">
                                    <span class="card-stat-label">Pending</span>
                                    <span class="card-stat-value">$8.4k</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Processed</span>
                                    <span class="card-stat-value">$124k</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Collection Rate</span>
                                    <span class="card-stat-value">92%</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="card-action-btn">View Details</button>
                            </div>
                        </div>

                        <!-- Card 5: Analytics -->
                        <div class="repo-card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <span class="card-icon-primary">📊</span>
                                    <h3 class="card-title">Analytics & Reports</h3>
                                </div>
                                <span class="card-status active">Active</span>
                            </div>
                            <p class="card-description">Generate comprehensive reports and gain insights into clinic performance metrics.</p>
                            <div class="card-stats">
                                <div class="card-stat">
                                    <span class="card-stat-label">Reports Generated</span>
                                    <span class="card-stat-value">342</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Uptime</span>
                                    <span class="card-stat-value">99.9%</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Last Updated</span>
                                    <span class="card-stat-value">2 min</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="card-action-btn">View Details</button>
                            </div>
                        </div>

                        <!-- Card 6: System Configuration -->
                        <div class="repo-card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <span class="card-icon-primary">⚙️</span>
                                    <h3 class="card-title">System Configuration</h3>
                                </div>
                                <span class="card-status">Stable</span>
                            </div>
                            <p class="card-description">Configure system settings, user roles, and security protocols with granular control.</p>
                            <div class="card-stats">
                                <div class="card-stat">
                                    <span class="card-stat-label">Users</span>
                                    <span class="card-stat-value">24</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Roles</span>
                                    <span class="card-stat-value">5</span>
                                </div>
                                <div class="card-stat">
                                    <span class="card-stat-label">Security Level</span>
                                    <span class="card-stat-value">High</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="card-action-btn">View Details</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="activity-section">
                    <div class="section-header">
                        <h2>Recent Activity</h2>
                        <a href="#" class="view-more-link">View All</a>
                    </div>

                    <div class="activity-list">
                        <div class="activity-item">
                            <span class="activity-icon">✓</span>
                            <div class="activity-content">
                                <p class="activity-title">Appointment confirmed with John Doe</p>
                                <p class="activity-time">2 minutes ago</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <span class="activity-icon">📄</span>
                            <div class="activity-content">
                                <p class="activity-title">Medical record updated for Jane Smith</p>
                                <p class="activity-time">15 minutes ago</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <span class="activity-icon">💰</span>
                            <div class="activity-content">
                                <p class="activity-title">Payment received: $500 from patient invoice</p>
                                <p class="activity-time">1 hour ago</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <span class="activity-icon">👤</span>
                            <div class="activity-content">
                                <p class="activity-title">New patient registered: Michael Johnson</p>
                                <p class="activity-time">3 hours ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
