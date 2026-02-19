/* ===========================
   Clinic Management System
   Global Scripts
   =========================== */

'use strict';

// ===========================
// Application Manager
// ===========================

const App = {
    // Configuration
    config: {
        sidebarBreakpoint: 768,
        animationDuration: 250,
        alertDuration: 5000,
    },
    
    // State
    state: {
        sidebarOpen: false,
        profileDropdownOpen: false,
    },
    
    // Initialize app
    init: function() {
        console.log('🚀 App initializing...');
        this.setupEventListeners();
        this.setupResponsive();
        this.initializeAlerts();
        this.setupFormValidation();
        console.log('✅ App initialized successfully');
    },
    
    // Setup all event listeners
    setupEventListeners: function() {
        console.log('🔧 Setting up event listeners...');
        const self = this; // Capture 'this' context
        
        // Menu toggle - PRIMARY: Direct attachment with auto-retry
        const attachMenuToggleListener = () => {
            const menuBtn = document.getElementById('menuToggle');
            if (!menuBtn) {
                console.warn('⏱️ Menu toggle not found yet, retrying...');
                setTimeout(attachMenuToggleListener, 100);
                return;
            }
            
            console.log('✅ Menu toggle found, attaching listener');
            menuBtn.addEventListener('click', function(e) {
                console.log('🔘 CLICK DETECTED on menu toggle button');
                e.stopPropagation();
                e.preventDefault();
                self.toggleSidebar();
                return false;
            });
        };
        
        attachMenuToggleListener();
        
        // Menu toggle - SECONDARY: Document delegation as backup
        document.addEventListener('click', (e) => {
            // Only if direct listener somehow fails
            if (e.target.id === 'menuToggle' || e.target.classList.contains('menu-toggle')) {
                console.log('🔘 BACKUP: Click detected via delegation');
                e.stopPropagation();
                e.preventDefault();
                self.toggleSidebar();
                return false;
            }
        });
        
        console.log('✅ Menu toggle listeners attached');
        
        // Profile dropdown - support multiple selectors
        const profileBtn = document.querySelector('.profile-btn') || document.getElementById('profileBtn');
        if (profileBtn) {
            // Click handler
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                console.log('👤 Profile btn clicked, current state:', this.state.profileDropdownOpen);
                this.toggleProfileDropdown();
                
                // Close sidebar on mobile when profile is clicked
                if (window.innerWidth < this.config.sidebarBreakpoint && this.state.profileDropdownOpen) {
                    console.log('📱 Mobile: closing sidebar when profile dropdown opens');
                    this.closeSidebar();
                }
            });
            
            // Keyboard support (Enter/Space)
            profileBtn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleProfileDropdown();
                }
            });
            
            console.log('✅ Profile button event listeners attached');
        } else {
            console.warn('❌ Profile button not found');
        }
        
        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            // Close profile dropdown if clicking outside
            const profileDropdownContainer = document.querySelector('.profile-dropdown');
            if (profileDropdownContainer && !e.target.closest('.profile-dropdown')) {
                this.closeProfileDropdown();
            }
            
            // Close sidebar if clicking outside (mobile)
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && !e.target.closest('.sidebar') && !e.target.closest('.menu-toggle') && menuToggle && !menuToggle.contains(e.target)) {
                if (window.innerWidth < this.config.sidebarBreakpoint && this.state.sidebarOpen) {
                    console.log('📱 Mobile: closing sidebar on outside click');
                    this.closeSidebar();
                }
            }
        });
        
        // Close alerts on close button click
        document.querySelectorAll('.alert-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                if (alert) {
                    this.dismissAlert(alert);
                }
            });
        });
        
        // Form submission handlers
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        });
        
        console.log('✅ All event listeners setup complete');
    },
    
    // Setup responsive behavior
    setupResponsive: function() {
        window.addEventListener('resize', () => {
            if (window.innerWidth >= this.config.sidebarBreakpoint) {
                this.state.sidebarOpen = false;
                this.updateSidebarPosition();
            }
        });
    },
    
    // Toggle sidebar
    toggleSidebar: function() {
        // remember last focused element to restore focus on close
        this._lastFocus = document.activeElement;

        const oldState = this.state.sidebarOpen;
        this.state.sidebarOpen = !this.state.sidebarOpen;
        console.log('🔄 Sidebar toggled from', oldState, 'to', this.state.sidebarOpen);
        this.updateSidebarPosition();
    },
    
    // Open sidebar
    openSidebar: function() {
        this.state.sidebarOpen = true;
        console.log('➕ Sidebar opened');
        this.updateSidebarPosition();
    },
    
    // Close sidebar
    closeSidebar: function() {
        this.state.sidebarOpen = false;
        console.log('➖ Sidebar closed');
        this.updateSidebarPosition();
    },
    
    // Update sidebar position
    updateSidebarPosition: function() {
        try {
            const sidebar = document.getElementById('mySidebar') || document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle') || document.querySelector('.menu-toggle');
            const body = document.body;
            
            console.log('📍 updateSidebarPosition called, state:', this.state.sidebarOpen);
            
            if (!sidebar) {
                console.warn('❌ Sidebar element not found!');
                console.log('Trying to find sidebar by ID: mySidebar');
                console.log('Trying to find sidebar by class: .sidebar');
                return;
            }
            
            console.log('✅ Sidebar element found', sidebar);
            
            // Get current computed style BEFORE change
            const beforeStyle = window.getComputedStyle(sidebar);
            console.log('📏 Sidebar computed styles BEFORE:', {
                left: beforeStyle.left,
                position: beforeStyle.position,
                display: beforeStyle.display,
                classes: sidebar.className
            });
            
            if (this.state.sidebarOpen) {
                console.log('➡️ Adding active class to sidebar');
                sidebar.classList.add('active');
                console.log('✅ Class added, classList now:', Array.from(sidebar.classList).join(', '));
                console.log('❓ classList.contains("active"):', sidebar.classList.contains('active'));
                
                if (menuToggle) {
                    menuToggle.classList.add('active');
                    menuToggle.setAttribute('aria-expanded', 'true');
                    console.log('➡️ Menu toggle also marked as active');
                }
                body.classList.add('sidebar-open');
                sidebar.setAttribute('aria-expanded', 'true');
                // Move focus into the sidebar for keyboard users
                try {
                    const focusable = sidebar.querySelector('a, button, input, [tabindex]');
                    if (focusable) focusable.focus();
                } catch (e) { /* swallow focus errors */ }
                // DON'T set overflow: hidden - it clips the fixed sidebar!
                // Just use the sidebar-open class for styling
                console.log('✅ Sidebar activated');
                
                // Get computed style AFTER change
                const afterStyle = window.getComputedStyle(sidebar);
                console.log('📏 Sidebar computed styles AFTER:', {
                    left: afterStyle.left,
                    position: afterStyle.position,
                    display: afterStyle.display,
                    classes: sidebar.className
                });
            } else {
                console.log('⬅️ Removing active class from sidebar');
                sidebar.classList.remove('active');
                console.log('✅ Class removed, classList now:', Array.from(sidebar.classList).join(', '));
                console.log('❓ classList.contains("active"):', sidebar.classList.contains('active'));
                
                if (menuToggle) {
                    menuToggle.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'true');
                    console.log('⬅️ Menu toggle active class removed');
                }
                body.classList.remove('sidebar-open');
                sidebar.setAttribute('aria-expanded', 'true');
                // restore focus to the toggle when closing
                try {
                    if (this._lastFocus && typeof this._lastFocus.focus === 'function') {
                        this._lastFocus.focus();
                    } else if (menuToggle && typeof menuToggle.focus === 'function') {
                        menuToggle.focus();
                    }
                } catch (e) { /* ignore */ }
                // DON'T reset overflow
                console.log('✅ Sidebar deactivated');
                
                // Get computed style AFTER change
                const afterStyle = window.getComputedStyle(sidebar);
                console.log('📏 Sidebar computed styles AFTER:', {
                    left: afterStyle.left,
                    position: afterStyle.position,
                    display: afterStyle.display,
                    classes: sidebar.className
                });
            }
        } catch (error) {
            console.error('❌ Error in updateSidebarPosition:', error);
        }
    },
    
    // Toggle profile dropdown
    toggleProfileDropdown: function() {
        this.state.profileDropdownOpen = !this.state.profileDropdownOpen;
        console.log('Profile dropdown toggled to:', this.state.profileDropdownOpen);
        this.updateProfileDropdownPosition();
    },
    
    // Close profile dropdown
    closeProfileDropdown: function() {
        this.state.profileDropdownOpen = false;
        this.updateProfileDropdownPosition();
    },
    
    // Update profile dropdown position
    updateProfileDropdownPosition: function() {
        try {
            const dropdown = document.getElementById('profileMenuDropdown') || document.querySelector('.profile-menu-dropdown');
            const profileBtn = document.querySelector('.profile-btn') || document.getElementById('profileBtn');

            const body = document.body;
            
            if (!dropdown) {
                console.warn('❌ Dropdown element not found');
                return;
            }
            
            // Explicitly add or remove active class based on state
            if (this.state.profileDropdownOpen) {
                dropdown.classList.add('active');
                dropdown.setAttribute('aria-expanded', 'true');
                if (profileBtn) profileBtn.classList.add('active');
                body.classList.add('profile-open');
                console.log('✅ Dropdown opened - active class added; profile-open class on body');
            } else {
                dropdown.classList.remove('active');
                dropdown.setAttribute('aria-expanded', 'false');
                if (profileBtn) profileBtn.classList.remove('active');
                body.classList.remove('profile-open');
                console.log('❌ Dropdown closed - active class removed; profile-open removed from body');
            }
        } catch (error) {
            console.error('❌ Error in updateProfileDropdownPosition:', error);
        }
    },
    
    // Initialize alerts with auto dismiss
    initializeAlerts: function() {
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                if (!alert.querySelector('.alert-close')) {
                    this.dismissAlert(alert);
                }
            }, this.config.alertDuration);
        });
    },
    
    // Dismiss alert
    dismissAlert: function(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        alert.style.transition = 'all 250ms ease-in-out';
        
        setTimeout(() => {
            alert.remove();
        }, 250);
    },
    
    // Setup form validation
    setupFormValidation: function() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('invalid', (e) => {
                e.preventDefault();
                this.showFieldError(e.target);
            }, true);
        });
    },
    
    // Handle form submission
    handleFormSubmit: function(e) {
        const form = e.target;
        if (form.noValidate) return;
        
        // Basic validation
        let isValid = true;
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field);
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    },
    
    // Show field error
    showFieldError: function(field) {
        const group = field.closest('.form-group');
        if (!group) return;
        
        field.classList.add('is-invalid');
        
        let errorMsg = group.querySelector('.form-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'form-error';
            errorMsg.textContent = field.validationMessage || 'This field is required';
            group.appendChild(errorMsg);
        }
    },
    
    // Clear field error
    clearFieldError: function(field) {
        const group = field.closest('.form-group');
        if (!group) return;
        
        field.classList.remove('is-invalid');
        const errorMsg = group.querySelector('.form-error');
        if (errorMsg) {
            errorMsg.remove();
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'info', duration = this.config.alertDuration) {
        const container = document.body;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} slide-in`;
        alert.innerHTML = `
            <span class="alert-icon">${this.getAlertIcon(type)}</span>
            <div class="alert-content">${message}</div>
            <button class="alert-close" type="button">&times;</button>
        `;
        
        container.appendChild(alert);
        
        const closeBtn = alert.querySelector('.alert-close');
        closeBtn.addEventListener('click', () => this.dismissAlert(alert));
        
        setTimeout(() => {
            if (document.body.contains(alert)) {
                this.dismissAlert(alert);
            }
        }, duration);
    },
    
    // Get alert icon
    getAlertIcon: function(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    },
};

// ===========================
// Table Utilities
// ===========================

const TableUtils = {
    // Initialize table with sorting and filtering
    initTable: function(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        // Add sorting to table headers
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(table, index));
        });
    },
    
    // Sort table by column
    sortTable: function(table, columnIndex) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = !table.dataset.sortAscending;
        
        rows.sort((a, b) => {
            const aValue = a.querySelectorAll('td')[columnIndex].textContent.trim();
            const bValue = b.querySelectorAll('td')[columnIndex].textContent.trim();
            
            // Try numeric sort first
            const aNum = parseFloat(aValue);
            const bNum = parseFloat(bValue);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            // Fall back to string sort
            return isAscending 
                ? aValue.localeCompare(bValue)
                : bValue.localeCompare(aValue);
        });
        
        rows.forEach(row => tbody.appendChild(row));
        table.dataset.sortAscending = isAscending;
    },
    
    // Filter table rows
    filterTable: function(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    },
    
    // Export table to CSV
    exportTableToCSV: function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const csvRow = Array.from(cols).map(col => {
                let text = col.textContent.trim();
                if (text.includes(',') || text.includes('"')) {
                    text = '"' + text.replace(/"/g, '""') + '"';
                }
                return text;
            });
            csv.push(csvRow.join(','));
        });
        
        this.downloadFile(csv.join('\n'), filename, 'text/csv');
    },
    
    // Download file
    downloadFile: function(content, filename, type) {
        const element = document.createElement('a');
        element.setAttribute('href', 'data:' + type + ';charset=utf-8,' + encodeURIComponent(content));
        element.setAttribute('download', filename);
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    },
};

// ===========================
// Form Utilities
// ===========================

const FormUtils = {
    // Validate email
    validateEmail: function(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },
    
    // Validate phone
    validatePhone: function(phone) {
        const regex = /^[\d\s()+-]+$/;
        return regex.test(phone) && phone.replace(/\D/g, '').length >= 10;
    },
    
    // Validate password
    validatePassword: function(password) {
        // At least 8 characters, 1 uppercase, 1 number
        const regex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
        return regex.test(password);
    },
    
    // Get form data as object
    getFormData: function(formId) {
        const form = document.getElementById(formId);
        if (!form) return null;
        
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    },
    
    // Populate form with data
    populateForm: function(formId, data) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        for (let key in data) {
            const field = form.elements[key];
            if (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = data[key];
                } else {
                    field.value = data[key];
                }
            }
        }
    },
    
    // Reset form
    resetForm: function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            form.querySelectorAll('.form-error').forEach(error => error.remove());
            form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
        }
    },
    
    // Disable form
    disableForm: function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.querySelectorAll('input, textarea, select, button').forEach(field => {
                field.disabled = true;
            });
        }
    },
    
    // Enable form
    enableForm: function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.querySelectorAll('input, textarea, select, button').forEach(field => {
                field.disabled = false;
            });
        }
    },
};

// ===========================
// Date & Time Utilities
// ===========================

const DateUtils = {
    // Format date
    formatDate: function(date, format = 'YYYY-MM-DD') {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        
        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day)
            .replace('HH', hours)
            .replace('mm', minutes);
    },
    
    // Get time ago
    getTimeAgo: function(date) {
        const seconds = Math.floor((new Date() - new Date(date)) / 1000);
        
        if (seconds < 60) return 'just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
        if (seconds < 2592000) return Math.floor(seconds / 604800) + ' weeks ago';
        if (seconds < 31536000) return Math.floor(seconds / 2592000) + ' months ago';
        
        return Math.floor(seconds / 31536000) + ' years ago';
    },
    
    // Get next appointment date (7 days from now)
    getNextAvailableDate: function() {
        const date = new Date();
        date.setDate(date.getDate() + 1);
        return this.formatDate(date);
    },
};

// ===========================
// Modal Utilities
// ===========================

const ModalUtils = {
    // Show modal
    showModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    },
    
    // Hide modal
    hideModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.remove('active');
        document.body.style.overflow = '';
    },
    
    // Create modal
    createModal: function(id, title, content, buttons = []) {
        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">${title}</h2>
                        <button type="button" class="modal-close" onclick="ModalUtils.hideModal('${id}')">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        ${buttons.map(btn => `
                            <button type="button" class="btn btn-${btn.type || 'secondary'}" 
                                    onclick="${btn.onclick || `ModalUtils.hideModal('${id}')`}">
                                ${btn.label}
                            </button>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const overlay = modal.querySelector('.modal-overlay');
        overlay.addEventListener('click', () => this.hideModal(id));
        
        return modal;
    },
};

// ===========================
// Confirmation Dialog
// ===========================

function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

function confirmDelete(itemName = 'this item') {
    return confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`);
}

function confirmAction(message = 'Are you sure you want to proceed?') {
    return confirm(message);
}

// ===========================
// Sidebar Toggle Function (for backward compatibility)
// ===========================

function toggleSidebar() {
    console.log('� toggleSidebar() WRAPPER called, About to call App.toggleSidebar()');
    console.log('App object:', typeof App);
    console.log('App.toggleSidebar:', typeof App?.toggleSidebar);
    
    try {
        if (!App) {
            console.error('❌ App object not defined yet!');
            return false;
        }
        if (!App.toggleSidebar) {
            console.error('❌ App.toggleSidebar not available!');
            return false;
        }
        App.toggleSidebar();
        console.log('✅ toggleSidebar wrapper executed successfully');
    } catch (err) {
        console.error('❌ Error in toggleSidebar wrapper:', err);
    }
    return false;
}

function openSidebar() {
    console.log('➕ openSidebar() wrapper called');
    if (App && App.openSidebar) {
        App.openSidebar();
    }
}

function closeSidebar() {
    console.log('➖ closeSidebar() wrapper called');
    if (App && App.closeSidebar) {
        App.closeSidebar();
    }
}

function toggleProfileDropdown(e) {
    console.log('👤 toggleProfileDropdown() wrapper called');
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }
    if (App && App.toggleProfileDropdown) {
        App.toggleProfileDropdown();
    } else {
        console.error('❌ App.toggleProfileDropdown not available');
    }
}

// Alternative direct approach for profile dropdown
function openProfileDropdown() {
    const dropdown = document.querySelector('.profile-menu-dropdown');
    if (dropdown) {
        dropdown.classList.add('active');
        App.state.profileDropdownOpen = true;
        console.log('✅ Profile dropdown opened directly');
    }
}

function closeProfileDropdown() {
    const dropdown = document.querySelector('.profile-menu-dropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
        App.state.profileDropdownOpen = false;
        console.log('✅ Profile dropdown closed directly');
    }
}

// Diagnostic functions
function testSidebar() {
    console.log('\n========== 🧪 SIDEBAR DIAGNOSTIC TEST ==========\n');
    
    const btn = document.getElementById('menuToggle') || document.querySelector('.menu-toggle');
    const sidebar = document.getElementById('mySidebar') || document.querySelector('.sidebar');
    
    console.log('1️⃣ ELEMENT DETECTION:');
    console.log('   Menu button found:', !!btn, btn?.id || btn?.className || 'NO ID/CLASS');
    console.log('   Sidebar found:', !!sidebar, sidebar?.id || sidebar?.className || 'NO ID/CLASS');
    console.log('   Sidebar in DOM:', document.body.contains(sidebar));
    
    console.log('\n2️⃣ CURRENT STATE:');
    console.log('   App.state.sidebarOpen:', App.state.sidebarOpen);
    console.log('   Sidebar has .active class:', sidebar?.classList.contains('active'));
    console.log('   Sidebar className:', sidebar?.className);
    console.log('   Window width:', window.innerWidth);
    console.log('   Sidebar breakpoint:', App.config.sidebarBreakpoint);
    
    console.log('\n3️⃣ COMPUTED STYLES (BEFORE TOGGLE):');
    if (sidebar) {
        const styles = window.getComputedStyle(sidebar);
        console.log('   left position:', styles.left);
        console.log('   position:', styles.position);
        console.log('   z-index:', styles.zIndex);
        console.log('   display:', styles.display);
        console.log('   visibility:', styles.visibility);
        console.log('   opacity:', styles.opacity);
        console.log('   transform:', styles.transform);
        console.log('   transition:', styles.transition);
    }
    
    console.log('\n4️⃣ EVENT LISTENER TEST:');
    console.log('   Attempting to manually toggle sidebar...');
    App.toggleSidebar();
    
    setTimeout(() => {
        console.log('\n5️⃣ AFTER TOGGLE (300ms):');
        console.log('   App.state.sidebarOpen:', App.state.sidebarOpen);
        console.log('   Sidebar has .active class:', sidebar?.classList.contains('active'));
        console.log('   Sidebar className:', sidebar?.className);
        console.log('   Button has .active class:', btn?.classList.contains('active'));
        if (sidebar) {
            const styles = window.getComputedStyle(sidebar);
            console.log('   Computed left position:', styles.left);
            console.log('   Computed display:', styles.display);
            console.log('   Computed visibility:', styles.visibility);
        }
        console.log('   Body has sidebar-open class:', document.body.classList.contains('sidebar-open'));
        console.log('\n6️⃣ DOM INSPECTION:');
        console.log('   Sidebar element:', sidebar);
        console.log('   Sidebar HTML snippet:', sidebar?.outerHTML.substring(0, 200) + '...');
        console.log('\n========== END TEST ==========\n');
    }, 300);
}

function testProfileDropdown() {
    console.log('\n🧪 Testing Profile Dropdown...');
    const btn = document.querySelector('.profile-btn');
    const dropdown = document.querySelector('.profile-menu-dropdown');
    
    console.log('✅ Button found:', !!btn);
    console.log('✅ Dropdown found:', !!dropdown);
    console.log('✅ App state:', App.state);
    console.log('✅ Calling App.toggleProfileDropdown()...');
    
    App.toggleProfileDropdown();
    
    setTimeout(() => {
        console.log('✅ After toggle - state:', App.state.profileDropdownOpen);
        console.log('✅ Dropdown has active class:', dropdown?.classList.contains('active'));
        console.log('✅ Dropdown styles - opacity:', window.getComputedStyle(dropdown).opacity);
        console.log('✅ Dropdown styles - visibility:', window.getComputedStyle(dropdown).visibility);
    }, 200);
}

// ===========================
// DOM Ready
// ===========================

document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOMContentLoaded fired, initializing App...');
    
    // Add small delay to ensure all DOM elements are ready
    setTimeout(() => {
        console.log('🔄 App.init() called after delay');
        App.init();
        
        // Extra verification and manual setup
        console.log('\n🔍 VERIFYING SETUP:');
        const menuToggle = document.getElementById('menuToggle') || document.querySelector('.menu-toggle');
        const sidebar = document.getElementById('mySidebar') || document.querySelector('.sidebar');
        
        console.log('✅ menuToggle element:', menuToggle?.id || menuToggle?.className || 'NOT FOUND');
        console.log('✅ sidebar element:', sidebar?.id || sidebar?.className || 'NOT FOUND');
        console.log('✅ App.state:', App.state);
        console.log('✅ App.config:', App.config);
        console.log('✅ Ready for user interaction');
    }, 100);
    
    // Keyboard shortcuts for testing (remove in production)
    document.addEventListener('keydown', (e) => {
        // Alt+S to toggle sidebar
        if (e.altKey && e.key === 's') {
            console.log('🔑 e pressed - toggling sidebar');
            App.toggleSidebar();
            e.preventDefault();
        }
        // Alt+P to toggle profile
        if (e.altKey && e.key === 'p') {
            console.log('🔑 Alt+P pressed - toggling profile dropdown');
            App.toggleProfileDropdown();
            e.preventDefault();
        }
    });
    
    // Add smooth page transitions
    const links = document.querySelectorAll('a[href^="/"], a[href^="./"], a[href^="../"]');
    links.forEach(link => {
        if (!link.getAttribute('target') && !link.hasAttribute('onclick')) {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('javascript:') && !href.startsWith('#')) {
                    // Allow normal navigation
                }
            });
        }
    });
});

// ===========================
// AJAX Utilities
// ===========================

const AjaxUtils = {
    // Generic AJAX request
    request: function(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            timeout: 30000,
        };
        
        const config = { ...defaultOptions, ...options };
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('Error:', error);
                App.showNotification('An error occurred. Please try again.', 'error');
                throw error;
            });
    },
    
    // GET request
    get: function(url) {
        return this.request(url, { method: 'GET' });
    },
    
    // POST request
    post: function(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },
    
    // PUT request
    put: function(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },
    
    // DELETE request
    delete: function(url) {
        return this.request(url, { method: 'DELETE' });
    },
    
    // Form submission via AJAX
    submitForm: function(formId, url) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const data = FormUtils.getFormData(formId);
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            
            button.disabled = true;
            button.innerHTML = '<span class="spinner"></span> Processing...';
            
            AjaxUtils.post(url, data)
                .then(response => {
                    if (response.success) {
                        App.showNotification(response.message || 'Success!', 'success');
                        FormUtils.resetForm(formId);
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    } else {
                        App.showNotification(response.message || 'An error occurred', 'error');
                    }
                })
                .catch(() => {
                    App.showNotification('Failed to submit form', 'error');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = originalText;
                });
        });
    },
};

// ===========================
// Local Storage Utilities
// ===========================

const StorageUtils = {
    // Set item
    setItem: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.error('Storage error:', e);
        }
    },
    
    // Get item
    getItem: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Storage error:', e);
            return null;
        }
    },
    
    // Remove item
    removeItem: function(key) {
        localStorage.removeItem(key);
    },
    
    // Clear all
    clear: function() {
        localStorage.clear();
    },
};

// ===========================
// Export for use in other files
// ===========================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        App,
        TableUtils,
        FormUtils,
        DateUtils,
        ModalUtils,
        AjaxUtils,
        StorageUtils,
    };
}

/* ===========================
   DASHBOARD MODERN SCRIPTS - MERGED FROM dashboard-modern.js
   =========================== */

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
        
        // Navigate to module detail
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
            animation: 'slide-in-up-modern 0.3s ease-out'
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
                color: var(--color-text-primary-dark);
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

// ===========================
// Initialize Dashboard (only if dashboard elements exist)
// ===========================

// Check if dashboard-specific initialization is needed
function initializeDashboardFeatures() {
    console.log('Dashboard features initializing...');
    
    // Initialize theme if using modern dashboard
    if (typeof ThemeManager !== 'undefined') {
        const themeManager = new ThemeManager();
        themeManager.loadSavedTheme();
    }
    
    // Initialize dashboard controller if elements exist
    if (document.querySelector('.sidebar') && document.querySelector('.repo-card')) {
        if (typeof DashboardController !== 'undefined') {
            const dashboard = new DashboardController();
        }
    }
    
    // Initialize real-time updates
    if (typeof RealtimeUpdater !== 'undefined') {
        const updater = new RealtimeUpdater();
    }
    
    // Observe elements
    if (typeof observeElements !== 'undefined') {
        observeElements();
    }
    
    // Lazy load images
    if (typeof lazyLoadImages !== 'undefined') {
        lazyLoadImages();
    }
    
    console.log('Dashboard ready');
}

// Initialize dashboard features when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDashboardFeatures);
} else {
    initializeDashboardFeatures();
}

// ===========================
// CSS Animations Injection for Dashboard
// ===========================

const dashboardStyle = document.createElement('style');
dashboardStyle.textContent = `
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

    @keyframes slide-in-up-dashboard {
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

document.head.appendChild(dashboardStyle);
