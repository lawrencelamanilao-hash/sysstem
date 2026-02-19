# CSS and JavaScript Documentation

## Overview
This document describes the comprehensive CSS and JavaScript files created for the Clinic Management System. These files provide styling and interactive functionality for the entire application.

## Files Created

### 1. `/css/style.css`
Global stylesheet containing all styles for the clinic management system.

### 2. `/js/script.js`
Global JavaScript file containing all interactive functionality for the application.

---

## CSS Features

### Design System
The CSS uses a comprehensive design system with CSS variables for:
- **Colors**: Primary, secondary, accent, status colors, and neutral palette
- **Typography**: Font families, sizes, and weights
- **Spacing**: Consistent spacing scale (xs to 3xl)
- **Shadows**: Multiple shadow levels for depth
- **Transitions**: Smooth animations throughout
- **Border Radius**: Consistent corner roundness

### Components Styled

#### Header & Navigation
- `.header-bar` - Fixed header with branding and profile
- `.sidebar` - Left navigation sidebar with smooth transitions
- `.nav-link` - Navigation links with active states
- `.nav-section` - Grouped navigation sections

#### Forms & Inputs
- `.form-control` - Text inputs, textareas, selects
- `.form-group` - Form field wrapper
- `.form-error` - Error message display
- `.form-container` - Form wrapper with styling
- Form validation states (`.is-invalid`, `.is-valid`)

#### Buttons & Controls
- `.btn` - Base button styling
- `.btn-primary` - Primary action button
- `.btn-secondary` - Secondary action button
- `.btn-success`, `.btn-error`, `.btn-warning` - Status buttons
- `.btn-small` - Small button variant

#### Cards & Containers
- `.card` - Card component with hover effects
- `.card-header`, `.card-body`, `.card-footer`
- `.feature-card` - Feature showcase cards
- `.stat-card` - Statistics dashboard cards

#### Tables
- Tables with responsive wrapper
- Hover effects on rows
- Sortable headers
- Accessible markup

#### Alerts & Messages
- `.alert` - Alert container
- `.alert-success`, `.alert-error`, `.alert-warning`, `.alert-info`
- Auto-dismiss functionality

#### Modals
- `.modal` - Full-screen modal overlay
- `.modal-dialog` - Modal content container
- `.modal-header`, `.modal-body`, `.modal-footer`
- Smooth animations

#### Additional Components
- **Breadcrumbs** - Navigation breadcrumbs
- **Badges** - Status badges
- **Pagination** - Multi-page navigation
- **Progress Bars** - Progress indicators
- **Tabs & Accordion** - Expandable content
- **Timeline** - Event timeline
- **Tooltips** - Hover tooltips

### Responsive Design
- Mobile-first approach
- Breakpoints at 480px and 768px
- Adaptive layouts for different screen sizes
- Sidebar collapses on mobile

### Animations
- Smooth transitions on hover
- Slide-in animations
- Fade in/out effects
- Spin and pulse animations

---

## JavaScript Features

### App Manager
The main `App` object handles:
- Application initialization
- Event listener setup
- Responsive behavior
- Global state management

#### Methods:
- `App.init()` - Initialize application
- `App.toggleSidebar()` - Toggle sidebar visibility
- `App.toggleProfileDropdown()` - Toggle profile menu
- `App.showNotification()` - Show notification messages
- `App.dismissAlert()` - Dismiss alert messages

### Form Utilities (`FormUtils`)
Comprehensive form handling functions:

```javascript
FormUtils.validateEmail(email)           // Email validation
FormUtils.validatePhone(phone)           // Phone validation
FormUtils.validatePassword(password)     // Password validation (8+ chars, 1 uppercase, 1 number)
FormUtils.getFormData(formId)            // Get form data as object
FormUtils.populateForm(formId, data)     // Fill form with data
FormUtils.resetForm(formId)              // Clear form
FormUtils.disableForm(formId)            // Disable form fields
FormUtils.enableForm(formId)             // Enable form fields
```

### Table Utilities (`TableUtils`)
Table manipulation functions:

```javascript
TableUtils.initTable(tableId)            // Initialize table with sorting
TableUtils.sortTable(table, columnIndex) // Sort table by column
TableUtils.filterTable(tableId, term)    // Filter table rows
TableUtils.exportTableToCSV(tableId)     // Export table to CSV file
```

### Date & Time Utilities (`DateUtils`)
Date handling functions:

```javascript
DateUtils.formatDate(date, format)       // Format date (YYYY-MM-DD HH:mm)
DateUtils.getTimeAgo(date)              // Get "time ago" string
DateUtils.getNextAvailableDate()        // Get next available date (7 days)
```

### Modal Utilities (`ModalUtils`)
Modal management functions:

```javascript
ModalUtils.showModal(modalId)            // Show modal
ModalUtils.hideModal(modalId)            // Hide modal
ModalUtils.createModal(id, title, content, buttons) // Create new modal
```

### AJAX Utilities (`AjaxUtils`)
Async request functions:

```javascript
AjaxUtils.request(url, options)          // Generic AJAX request
AjaxUtils.get(url)                       // GET request
AjaxUtils.post(url, data)                // POST request
AjaxUtils.put(url, data)                 // PUT request
AjaxUtils.delete(url)                    // DELETE request
AjaxUtils.submitForm(formId, url)        // AJAX form submission
```

### Local Storage Utilities (`StorageUtils`)
Browser storage functions:

```javascript
StorageUtils.setItem(key, value)         // Store item
StorageUtils.getItem(key)                // Retrieve item
StorageUtils.removeItem(key)             // Remove item
StorageUtils.clear()                     // Clear all storage
```

### Confirmation Dialogs
Simple confirmation functions:

```javascript
confirmLogout()                          // Confirm logout action
confirmDelete(itemName)                  // Confirm delete action
confirmAction(message)                   // Generic confirmation
```

---

## How to Use

### 1. Include CSS in HTML
```html
<link rel="stylesheet" href="css/style.css">
```

### 2. Include JavaScript in HTML
```html
<script src="js/script.js"></script>
```

### 3. Initialize Specific Components

#### Form Validation
```html
<form id="myForm">
    <div class="form-group">
        <label for="email" class="required">Email</label>
        <input type="email" id="email" class="form-control" required>
    </div>
</form>
```

#### Table with Sorting
```html
<table id="dataTable">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <!-- rows -->
    </tbody>
</table>

<script>
    TableUtils.initTable('dataTable');
</script>
```

#### Modal
```html
<button onclick="ModalUtils.showModal('myModal')">Open Modal</button>

<div id="myModal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Modal Title</h2>
                <button class="modal-close" onclick="ModalUtils.hideModal('myModal')">&times;</button>
            </div>
            <div class="modal-body">
                Content here
            </div>
        </div>
    </div>
</div>
```

#### Notifications
```javascript
App.showNotification('Success!', 'success');
App.showNotification('Error occurred', 'error');
App.showNotification('Warning message', 'warning');
App.showNotification('Info message', 'info');
```

#### AJAX Form Submission
```javascript
AjaxUtils.submitForm('loginForm', '/api/login');
```

---

## CSS Classes Reference

### Layout
- `.container` - Max-width container
- `.main-content` - Main content area
- `.d-flex` - Flex display
- `.flex-wrap` - Flex wrap
- `.items-center` - Align items center
- `.justify-between` - Space between

### Spacing (Margin/Padding)
- `.mt-1`, `.mt-2`, `.mt-3`, `.mt-4`, `.mt-5` - Margin top
- `.mb-1`, `.mb-2`, `.mb-3`, `.mb-4`, `.mb-5` - Margin bottom
- `.p-1`, `.p-2`, `.p-3`, `.p-4`, `.p-5` - Padding

### Text
- `.text-center` - Center text
- `.text-primary` - Primary color text
- `.text-success` - Success color text
- `.text-error` - Error color text
- `.text-warning` - Warning color text
- `.text-muted` - Muted color text

### Utilities
- `.w-full` - Full width
- `.hidden` - Hide element
- `.opacity-50` - 50% opacity

### Status Classes
- `.active` - Active state
- `.disabled` - Disabled state
- `.is-valid` - Valid input
- `.is-invalid` - Invalid input

---

## Responsive Breakpoints

- **Mobile**: < 480px
- **Tablet**: < 768px
- **Desktop**: ≥ 768px

Sidebar collapses to hamburger menu on tablets and below.

---

## Color Palette

### Primary Colors
- Primary: #2563EB
- Primary Dark: #1E40AF
- Primary Light: #3B82F6

### Status Colors
- Success: #10B981
- Warning: #F59E0B
- Error: #EF4444
- Info: #3B82F6

### Neutral Colors
- Background Light: #FFFFFF
- Background Gray: #F8FAFC
- Text Primary: #1E293B
- Text Secondary: #64748B

---

## Performance Considerations

1. **CSS**: Uses CSS variables for efficient theming
2. **JavaScript**: Event delegation for improved performance
3. **Animations**: Hardware-accelerated transforms
4. **Form Validation**: Client-side before server submission

---

## Browser Support

- Chrome/Edge: Latest versions
- Firefox: Latest versions
- Safari: Latest versions
- IE: Not supported

---

## Tips

1. Always include `js/script.js` before custom scripts
2. Use CSS variables for custom colors: `var(--color-primary)`
3. Add `.required` class to labels for required fields
4. Use `App.showNotification()` for user feedback
5. Wrap modals in body tag for proper z-index handling

---

## Troubleshooting

### Sidebar not visible
- Check if `#mySidebar` element exists in HTML
- Ensure navbar.php is included

### Form validation not working
- Verify form has `required` or `novalidate` attributes
- Check for form ID in HTML

### Styles not applying
- Clear browser cache
- Check file paths in HTML head
- Verify style.css is loaded before custom styles

---

## Future Enhancements

Potential additions:
- Dark mode toggle
- Print stylesheets
- Animation preferences (prefers-reduced-motion)
- Additional utility classes
- Component library documentation
