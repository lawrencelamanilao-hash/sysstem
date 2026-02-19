# Clinic Management System

A comprehensive web-based clinic management system built with PHP, HTML, CSS, and MySQL. This system allows patients to book appointments, view medical records, and manage their medical information, while doctors can manage appointments, maintain medical records, and view their patient list.

## Features

### Patient Features
- **Patient Registration & Login**: Easy registration with secure password hashing
- **Profile Management**: Update personal information, address, contact details
- **Appointment Booking**: Browse doctors and schedule appointments
- **View Appointments**: See all scheduled appointments with doctor details
- **Medical Records**: Access your medical history and prescriptions
- **Billing Management**: View charges, payments, and pending bills

### Doctor Features
- **Doctor Registration & Login**: Register as a healthcare provider
- **Profile Management**: Maintain professional information and availability
- **Manage Appointments**: View and manage patient appointments
- **Medical Records**: Create and maintain patient medical records
- **Patient Directory**: View all patients you've treated
- **Diagnosis & Prescription**: Record diagnosis and prescription for patients

## System Requirements

- **XAMPP** (Apache, MySQL, PHP)
- **PHP 7.0+**
- **MySQL 5.5+**
- **Web Browser** (Chrome, Firefox, Safari, Edge)

## Installation Guide

### Step 1: Copy Files to XAMPP
1. Navigate to your XAMPP installation directory
2. Go to `htdocs` folder (usually `C:\xampp\htdocs` on Windows)
3. Copy the entire `Clinic` folder into the `htdocs` directory

### Step 2: Start XAMPP Services
1. Open XAMPP Control Panel
2. Click **Start** button for Apache
3. Click **Start** button for MySQL

### Step 3: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/Clinic/`

### Step 4: Verify Database Setup
The database will be automatically created when you first access the application. The `config.php` file will:
- Create the `clinic_db` database
- Create all necessary tables (patients, doctors, appointments, medical_records, billing, users)

### Quick Start (local development)
1. Ensure XAMPP is installed and running (Apache + MySQL).
2. Copy the `Clinic` folder into `C:\xampp\htdocs`.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open your browser and go to `http://localhost/Clinic/`.
5. The app will attempt to create the database and tables automatically on first run. If it doesn't, import the SQL schema from `sql/clinic_schema.sql` (if provided) or run the setup script in `config.php`.

### Local Development Tips
- Use a modern browser (Chrome/Edge) and open DevTools (F12) for layout issues.
- If pages don't load, try a hard refresh (Ctrl+F5) and ensure Apache is running.
- For permission or PHP errors, check Apache's error log (`xampp\apache\logs\error.log`).

### Recent Changes (frontend)
- Moved the profile dropdown markup into the header (`navbar.php`) for improved DOM structure.
- Sidebar and header styles were refined in `css/style.css` to better match a modern app layout (collapsed width, expanded width, and profile dropdown anchoring).
- If the profile dropdown appears misplaced, inspect the header element and verify `css/style.css` changes are loaded (clear cache and hard-reload).

## Database Structure

### Tables

1. **users** - User authentication and role management
   - id, username, password, email, role, patient_id, doctor_id

2. **patients** - Patient information
   - id, first_name, last_name, email, phone, date_of_birth, gender, address, city, state, zipcode, medical_history

3. **doctors** - Doctor information
   - id, first_name, last_name, email, phone, specialization, license_number, address, availability

4. **appointments** - Appointment scheduling
   - id, patient_id, doctor_id, appointment_date, appointment_time, reason, status, notes

5. **medical_records** - Patient medical history
   - id, patient_id, doctor_id, appointment_id, diagnosis, prescription, notes

6. **billing** - Payment and billing information
   - id, patient_id, appointment_id, amount, service_description, payment_status, payment_date

## User Roles

### Patient
- Can register and create their profile
- Book appointments with available doctors
- View and manage their appointments
- Access their medical records and prescriptions
- View and pay bills

### Doctor
- Can register and manage their profile
- View and manage patient appointments
- Create and update medical records
- View list of all patients
- Access patient medical history

## Usage Guide

### For Patients

1. **Register**: Click "Register" on the homepage and select "Patient"
2. **Login**: Use your credentials to log in
3. **Book Appointment**: 
   - Go to Dashboard → Book Appointment
   - Select a doctor
   - Choose date and time
   - Enter reason for visit
4. **View Records**: Go to Medical Records to see your medical history
5. **Check Bills**: View Billing section for payment information

### For Doctors

1. **Register**: Click "Register" on the homepage and select "Doctor"
2. **Login**: Use your credentials to log in
3. **Complete Profile**: Edit your profile to add specialization and availability
4. **Manage Appointments**:
   - Go to Dashboard → Manage Appointments
   - Click "Add Record" to add medical records for a patient
5. **View Patients**: See all patients who have booked with you

## File Structure

```
Clinic/
├── index.php                 # Home page
├── login.php                 # Login page
├── register.php              # Registration page
├── dashboard.php             # User dashboard
├── logout.php                # Logout handler
├── config.php                # Database configuration
├── css/
│   └── style.css            # Main stylesheet
├── patients/
│   ├── view_profile.php     # View patient profile
│   └── edit_profile.php     # Edit patient profile
├── doctors/
│   ├── view_profile.php     # View doctor profile
│   ├── edit_profile.php     # Edit doctor profile
│   └── manage_patients.php  # View doctor's patients
├── appointments/
│   ├── book.php             # Book appointment
│   ├── view.php             # View appointments (patient)
│   ├── manage.php           # Manage appointments (doctor)
│   └── add_record.php       # Add medical record
├── medical_records/
│   ├── view.php             # View records (patient)
│   └── manage.php           # Manage records (doctor)
└── billing/
    └── view.php             # View billing information
```

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()` function
- **Session Management**: Secure session handling for user authentication
- **Input Validation**: All user inputs are validated and sanitized
- **Prepared Statements**: SQL prepared statements prevent SQL injection
- **Role-Based Access**: Different access levels for patients and doctors

## Troubleshooting

### Database Connection Error
- Ensure MySQL service is running in XAMPP
- Check database credentials in `config.php`
- Verify database name matches in all configuration files

### Pages Not Loading
- Check that Apache service is running
- Verify the correct URL: `http://localhost/Clinic/`
- Clear browser cache and cookies

### Appointment Booking Issues
- Ensure you select a future date and time
- Check that a doctor is available
- Verify your patient profile is complete

### Permission Denied Errors
- Ensure XAMPP has proper folder permissions
- Check user role (patient vs doctor) for feature access

## Default Configuration

**Database Details** (configured in `config.php`):
- Server: localhost
- Username: root
- Password: (empty/default)
- Database Name: clinic_db

## Future Enhancements

- Email notifications for appointments
- SMS reminders
- Payment gateway integration
- Prescription printing
- Telemedicine/video consultations
- Admin dashboard for clinic management
- Advanced reporting and analytics

## Support & Contact

For issues or questions regarding the system, please contact your system administrator.

## License

This project is provided for educational and clinical use. All rights reserved.

---

**Version**: 1.0  
**Last Updated**: February 17, 2026
