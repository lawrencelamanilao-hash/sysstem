/* ===========================
   Dashboard Modern JavaScript
   Cyber-Industrial Theme
   =========================== */

'use strict';

// ===========================
// Dashboard Controller
// ===========================

class DashboardController {
    constructor() {
        this.initializeElements();
        this.setupEventListeners();
        this.initializeAnimations();
        this.loadDashboardData();
    }

    // Initialize DOM elements
    initializeElements() {
        this.sidebar = document.querySelector('.sidebar');
        this.mainContent = document.querySelector('.main-content');
        this.navItems = document.querySelectorAll('.nav-item');
        this.repoCards = document.querySelectorAll('.repo-card');
        this.statCards = document.querySelectorAll('.stat-card');
        this.activityItems = document.querySelectorAll('.activity-item');
        this.searchBox = document.querySelector('.search-box');
        this.notificationBtn = document.querySelector('.notification-btn');
        this.cardActionBtns = document.querySelectorAll('.card-action-btn');
    }

    // Setup all event listeners
    setupEventListeners() {
        // Navigation Item Clicks
        this.navItems.forEach(item => {
            item.addEventListener('click', (e) => this.handleNavigation(e));
        });

        // Card Action Buttons
        this.cardActionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleCardAction(e));
        });

        // Search Box
        if (this.searchBox) {
            this.searchBox.addEventListener('input', (e) => this.handleSearch(e));
            this.searchBox.addEventListener('focus', (e) => this.expandSearch(e));
            this.searchBox.addEventListener('blur', (e) => this.collapseSearch(e));
        }

        // Notification Button
        if (this.notificationBtn) {
            this.notificationBtn.addEventListener('click', (e) => this.handleNotification(e));
        }

        // Repo Cards Interactions
        this.repoCards.forEach(card => {
            card.addEventListener('mouseenter', (e) => this.handleCardHover(e, true));
            card.addEventListener('mouseleave', (e) => this.handleCardHover(e, false));
        });

        // Activity Items
        this.activityItems.forEach(item => {
            item.addEventListener('click', (e) => this.handleActivityClick(e));
        });

        // Window Events
        window.addEventListener('resize', () => this.handleResize());
        window.addEventListener('scroll', () => this.handleScroll());
    }

    // Initialize animations
    initializeAnimations() {
        // Stagger animations for stat cards
        this.statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.35, 0.46, 0.64, 0.88)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Stagger animations for repo cards
        this.repoCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.35, 0.46, 0.64, 0.88)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 200 + index * 100);
        });
    }

    // Handle navigation item clicks
    handleNavigation(event) {
        event.preventDefault();
        const clickedItem = event.currentTarget;
        
        // Remove active class from all items
        this.navItems.forEach(item => item.classList.remove('active'));
        
        // Add active class to clicked item
        clickedItem.classList.add('active');
        
        // Get the target and log it
        const target = clickedItem.getAttribute('href');
        console.log(`Navigating to: ${target}`);
        
        // Add subtle animation to main content
        this.mainContent.style.opacity = '0.8';
        setTimeout(() => {
            this.mainContent.style.transition = 'opacity 0.3s ease-in';
            this.mainContent.style.opacity = '1';
        }, 50);
    }

    // Handle card action buttons
    handleCardAction(event) {
        const button = event.currentTarget;
        const card = button.closest('.repo-card');
        const cardTitle = card.querySelector('.card-title').textContent;
        
        console.log(`Action clicked for: ${cardTitle}`);
        
        // Add ripple effect
        this.createRipple(button, event);
        
        // Navigate to detail view
        this.navigateToModule(cardTitle);
    }

    // Navigate to module detail
    navigateToModule(moduleName) {
        const moduleMap = {
            'Appointments Management': 'appointments/manage.php',
            'Medical Records': 'medical_records/manage.php',
            'Patient Management': 'patients/edit_profile.php',
            'Billing & Payments': 'billing/view.php',
            'Analytics & Reports': 'Adminpage/admin_reports_Version2.php',
            'System Configuration': 'admin.php'
        };
        
        const targetUrl = moduleMap[moduleName] || '#';
        console.log(`Would navigate to: ${targetUrl}`);
        
        if (targetUrl !== '#') {
            // window.location.href = targetUrl; // Uncomment to enable navigation
        }
    }

    // Create ripple effect on click
    createRipple(element, event) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.className = 'ripple';
        
        // Add ripple styles
        Object.assign(ripple.style, {
            position: 'absolute',
            borderRadius: '50%',
            background: 'rgba(174, 188, 36, 0.6)',
            pointerEvents: 'none',
            animation: 'ripple-animation 0.6s ease-out'
        });
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }

    // Handle card hover effects
    handleCardHover(event, isHovering) {
        const card = event.currentTarget;
        if (isHovering) {
            card.style.transform = 'translateY(-8px)';
        } else {
            card.style.transform = 'translateY(0)';
        }
    }

    // Handle search input
    handleSearch(event) {
        const searchTerm = event.target.value.toLowerCase();
        console.log(`Searching for: ${searchTerm}`);
        
        // Filter repo cards based on search
        this.repoCards.forEach(card => {
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            const description = card.querySelector('.card-description').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = 'flex';
                card.style.opacity = '1';
                setTimeout(() => {
                    card.style.transform = 'scale(1)';
                }, 10);
            } else {
                card.style.opacity = '0.3';
                card.style.transform = 'scale(0.95)';
            }
        });
    }

    // Expand search box
    expandSearch(event) {
        const searchBox = event.target;
        searchBox.style.width = '400px';
        searchBox.style.transition = 'width 0.3s cubic-bezier(0.35, 0.46, 0.64, 0.88)';
    }

    // Collapse search box
    collapseSearch(event) {
        const searchBox = event.target;
        if (searchBox.value === '') {
            searchBox.style.width = '300px';
        }
    }

    // Handle notification button
    handleNotification(event) {
        event.preventDefault();
        console.log('Opening notifications');
        
        // Create notification panel
        this.showNotificationPanel();
    }

    // Show notification panel
    showNotificationPanel() {
        const panel = document.createElement('div');
        panel.className = 'notification-panel';
        
        Object.assign(panel.style, {
            position: 'fixed',
            top: '80px',
            right: '20px',
            background: 'var(--color-charcoal)',
            border: '1px solid var(--color-border)',
            borderRadius: 'var(--radius-lg)',
            padding: 'var(--space-lg)',
            width: '300px',
            maxHeight: '400px',
            overflowY: 'auto',
            zIndex: '1000',
            boxShadow: 'var(--shadow-lg)',
            animation: 'slide-in-up 0.3s ease-out'
        });
        
        const notifications = [
            { type: 'success', message: 'Appointment confirmed' },
            { type: 'warning', message: 'Medical record pending review' },
            { type: 'info', message: 'New patient registration' }
        ];
        
        panel.innerHTML = '<h3 style="color: var(--color-primary-lime); margin-bottom: var(--space-md);">Notifications</h3>';
        
        notifications.forEach(notif => {
            const item = document.createElement('div');
            item.style.cssText = `
                padding: var(--space-md);
                margin-bottom: var(--space-md);
                background-color: rgba(174, 188, 36, 0.05);
                border-left: 3px solid var(--color-primary-lime);
                border-radius: var(--radius-sm);
                font-size: var(--font-size-sm);
                color: var(--color-text-primary);
            `;
            item.textContent = notif.message;
            panel.appendChild(item);
        });
        
        document.body.appendChild(panel);
        
        // Close on click outside
        setTimeout(() => {
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.notification-btn') && !e.target.closest('.notification-panel')) {
                    panel.remove();
                }
            }, { once: true });
        }, 100);
    }

    // Handle activity item clicks
    handleActivityClick(event) {
        const activityItem = event.currentTarget;
        const title = activityItem.querySelector('.activity-title').textContent;
        
        console.log(`Activity clicked: ${title}`);
        
        // Add highlight effect
        activityItem.style.backgroundColor = 'rgba(174, 188, 36, 0.15)';
        setTimeout(() => {
            activityItem.style.backgroundColor = 'rgba(41, 61, 112, 0.1)';
        }, 300);
    }

    // Handle window resize
    handleResize() {
        const width = window.innerWidth;
        
        if (width < 1024) {
            this.sidebar.style.position = 'fixed';
        } else {
            this.sidebar.style.position = 'sticky';
        }
    }

    // Handle scroll events
    handleScroll() {
        const header = document.querySelector('.top-header');
        if (window.scrollY > 10) {
            header.style.boxShadow = 'var(--shadow-md)';
        } else {
            header.style.boxShadow = 'none';
        }
    }

    // Load dashboard data (simulated)
    loadDashboardData() {
        console.log('Loading dashboard data...');
        
        // Simulate data loading
        this.updateStatistics();
        this.initializeCharts();
    }

    // Update statistics
    updateStatistics() {
        const stats = [
            { selector: '.stat-value', updateInterval: 5000 },
        ];
        
        stats.forEach(stat => {
            setInterval(() => {
                const elements = document.querySelectorAll(stat.selector);
                // Animate value changes
                elements.forEach(el => {
                    el.style.animation = 'pulse 0.5s ease-in-out';
                });
            }, stat.updateInterval);
        });
    }

    // Initialize charts (placeholder)
    initializeCharts() {
        console.log('Charts initialized');
    }
}

// ===========================
// Utility Functions
// ===========================

// Smooth scroll utility
function smoothScroll(targetSelector) {
    const target = document.querySelector(targetSelector);
    if (target) {
        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Add element visibility animation
function observeElements() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.repo-card, .stat-card, .activity-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        observer.observe(el);
    });
}

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===========================
// Theme Management
// ===========================

class ThemeManager {
    constructor() {
        this.isDarkMode = true;
    }

    toggleTheme() {
        this.isDarkMode = !this.isDarkMode;
        this.applyTheme();
    }

    applyTheme() {
        if (this.isDarkMode) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    }

    loadSavedTheme() {
        const saved = localStorage.getItem('theme') || 'dark';
        this.isDarkMode = saved === 'dark';
        this.applyTheme();
    }
}

// ===========================
// Real-time Updates
// ===========================

class RealtimeUpdater {
    constructor() {
        this.updateInterval = 30000; // 30 seconds
        this.startUpdates();
    }

    startUpdates() {
        setInterval(() => {
            this.updateStats();
            this.updateActivity();
        }, this.updateInterval);
    }

    updateStats() {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            // Add subtle animation
            card.style.animation = 'pulse 0.5s ease-in-out';
            setTimeout(() => {
                card.style.animation = 'none';
            }, 500);
        });
    }

    updateActivity() {
        console.log('Updating activity feed...');
        // Simulate activity update
    }
}

// ===========================
// Performance Optimization
// ===========================

// Lazy load images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Request animation frame helper
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// ===========================
// Initialize Application
// ===========================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Dashboard initializing...');
    
    // Initialize theme
    const themeManager = new ThemeManager();
    themeManager.loadSavedTheme();
    
    // Initialize dashboard controller
    const dashboard = new DashboardController();
    
    // Initialize real-time updates
    const updater = new RealtimeUpdater();
    
    // Observe elements
    observeElements();
    
    // Lazy load images
    lazyLoadImages();
    
    console.log('Dashboard ready');
});

// ===========================
// CSS Animations Injection
// ===========================

const style = document.createElement('style');
style.textContent = `
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    @keyframes glow {
        0%, 100% {
            box-shadow: 0 0 10px rgba(174, 188, 36, 0.3);
        }
        50% {
            box-shadow: 0 0 20px rgba(174, 188, 36, 0.6);
        }
    }

    @keyframes slide-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .smooth-transition {
        transition: all 0.25s cubic-bezier(0.35, 0.46, 0.64, 0.88);
    }

    .glow-effect {
        animation: glow 2s ease-in-out infinite;
    }
`;

document.head.appendChild(style);

// ===========================
// Export for module usage
// ===========================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        DashboardController,
        ThemeManager,
        RealtimeUpdater
    };
}
