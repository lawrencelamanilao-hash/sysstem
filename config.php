<?php
$servername = "127.0.0.1"; // use TCP loopback to avoid some localhost socket/permission issues
$username = "root";
$password = "";
$database = "clinic_db";

// Create connection
$conn = @new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    // Provide actionable guidance for common causes
    $msg = "Connection failed: " . $conn->connect_error . ".\n";
    $msg .= "Possible fixes:\n";
    $msg .= " - Ensure MySQL/MariaDB is running in XAMPP Control Panel.\n";
    $msg .= " - Verify the MySQL user (e.g. 'root') is allowed to connect from this host.\n";
    $msg .= " - Try changing server to '127.0.0.1' or update user host privileges in MySQL.\n";
    die(nl2br(htmlspecialchars($msg)));
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS clinic_db";
if ($conn->query($sql) === TRUE) {
    // Database created or already exists
}

// Select the database
$conn->select_db($database);

// Set charset to UTF-8
$conn->set_charset("utf8");

// Create tables if they don't exist

// Patients table
$create_patients = "CREATE TABLE IF NOT EXISTS patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    date_of_birth DATE,
    gender VARCHAR(10),
    address VARCHAR(255),
    city VARCHAR(50),
    state VARCHAR(50),
    zipcode VARCHAR(10),
    medical_history TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($create_patients);

// Doctors table
$create_doctors = "CREATE TABLE IF NOT EXISTS doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    specialization VARCHAR(100),
    license_number VARCHAR(50),
    address VARCHAR(255),
    availability VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($create_doctors);

// Appointments table
$create_appointments = "CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    status VARCHAR(20) DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
)";
$conn->query($create_appointments);

// Medical Records table
$create_records = "CREATE TABLE IF NOT EXISTS medical_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    diagnosis VARCHAR(255),
    prescription TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
)";
$conn->query($create_records);

// Billing table
$create_billing = "CREATE TABLE IF NOT EXISTS billing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    appointment_id INT,
    amount DECIMAL(10, 2),
    service_description VARCHAR(255),
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
)";
$conn->query($create_billing);

// Users table (for login)
$create_users = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'patient',
    patient_id INT,
    doctor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
)";
$conn->query($create_users);
?>
