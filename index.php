<?php
session_start();
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="page-wrapper">
        <main class="site-container">

            <!-- Hero -->
            <section class="hero" role="region" aria-label="Hero">
                <div class="hero-inner">
                    <div class="hero-badge">Intelligent Clinic Management</div>
                    <h1 class="hero-title">Supercharge Your Clinic with a Modern Management System</h1>
                    <p class="hero-sub">All the tools you need to manage appointments, patients, records and billing — in one streamlined, secure dashboard built for clinics of any size.</p>

                    <div class="hero-cta" role="form" aria-label="Signup">
                        <input class="input-email" type="email" placeholder="Enter your email" aria-label="Email for signup">
                        <?php if(!isset($_SESSION['user_id'])): ?>
                            <a href="register.php" class="btn-getstarted hero-btn">Get Started</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn-getstarted hero-btn">Go to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Feature Section (left panel + right list like the attachment) -->
            <section class="feature-section" aria-label="Features">
                <aside class="feature-left">
                    <h3>One platform, complete care.</h3>
                    <p>Manage patients, appointments, medical records, and billing from a single secure interface. Role-based access, audit logs, and integrations make it simple to run your clinic efficiently.</p>

                    <ul class="feature-list" style="margin-top:12px; list-style:none; padding-left:0;">
                        <li class="muted-small">• Patient profiles & visit history</li>
                        <li class="muted-small">• Appointment scheduling & reminders</li>
                        <li class="muted-small">• Secure medical records & attachments</li>
                        <li class="muted-small">• Billing, invoicing & payments</li>
                    </ul>
                </aside>

                <div class="feature-right">
                    <div class="list-card">
                        <div>
                            <div class="repo-title">Appointments</div>
                            <div class="meta">Real-time calendar & booking</div>
                        </div>
                        <div class="value">Active</div>
                    </div>

                    <div class="list-card">
                        <div>
                            <div class="repo-title">Patient Records</div>
                            <div class="meta">Encrypted storage, quick access</div>
                        </div>
                        <div class="value">Secure</div>
                    </div>

                    <div class="list-card">
                        <div>
                            <div class="repo-title">Billing & Payments</div>
                            <div class="meta">Invoicing, receipts, payment history</div>
                        </div>
                        <div class="value">Configured</div>
                    </div>

                    <div class="list-card">
                        <div>
                            <div class="repo-title">Reports & Analytics</div>
                            <div class="meta">Utilization, revenue, clinical KPIs</div>
                        </div>
                        <div class="value">Insights</div>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <p>&copy; 2026 Clinic Management System. All rights reserved.</p>
            </footer>

        </main>
    </div>
</body>
</html>
