<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

$redirect_base = ($role === 'doctor') ? 'doctors/edit_profile.php' : 'patients/edit_profile.php';

if(!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE){
    header('Location: ' . $redirect_base . '?upload_error=No+file+uploaded');
    exit();
}

$file = $_FILES['avatar'];
if($file['error'] !== UPLOAD_ERR_OK){
    header('Location: ' . $redirect_base . '?upload_error=Upload+error');
    exit();
}

$maxSize = 2 * 1024 * 1024; // 2MB
if($file['size'] > $maxSize){
    header('Location: ' . $redirect_base . '?upload_error=File+too+large');
    exit();
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];
if(!isset($allowed[$mime])){
    header('Location: ' . $redirect_base . '?upload_error=Invalid+file+type');
    exit();
}

$ext = $allowed[$mime];
$uploadsDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
if(!is_dir($uploadsDir)){
    mkdir($uploadsDir, 0755, true);
}

$basename = ($role ?: 'user') . '_' . $user_id . '_' . time();
$filename = $basename . '.' . $ext;
$targetPath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

if(!move_uploaded_file($file['tmp_name'], $targetPath)){
    header('Location: ' . $redirect_base . '?upload_error=Could+not+save+file');
    exit();
}

// Ensure avatar column exists for the appropriate table
if($role === 'doctor'){
    $table = 'doctors';
} else {
    $table = 'patients';
}

$checkCol = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE 'avatar'");
$checkCol->execute();
$res = $checkCol->get_result();
if($res->num_rows === 0){
    $conn->query("ALTER TABLE `$table` ADD COLUMN avatar VARCHAR(255) DEFAULT 'default.png'");
}
$checkCol->close();

// Map user -> doctor/patient id if necessary
$profile_id = null;
if($role === 'doctor'){
    $stmt = $conn->prepare("SELECT doctor_id FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $profile_id = $r['doctor_id'] ?? null;
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT patient_id FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $profile_id = $r['patient_id'] ?? null;
    $stmt->close();
}

if(!$profile_id){
    header('Location: ' . $redirect_base . '?upload_error=Profile+not+found');
    exit();
}

$update = $conn->prepare("UPDATE `$table` SET avatar = ? WHERE id = ?");
$update->bind_param('si', $filename, $profile_id);
if($update->execute()){
    header('Location: ' . $redirect_base . '?upload_success=1');
    exit();
} else {
    header('Location: ' . $redirect_base . '?upload_error=DB+update+failed');
    exit();
}

?>