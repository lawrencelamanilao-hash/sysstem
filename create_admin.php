<?php
// This file has been disabled for security reasons.
http_response_code(403);
echo "create_admin.php has been disabled. If you need to create or reset an admin account, use the secure procedure in the README or run a CLI script locally.\n";
exit();

if (php_sapi_name() === 'cli') {
    global $argv;
    if (isset($argv[1])) $username = $argv[1];
    if (isset($argv[2])) $password_plain = $argv[2];
    if (isset($argv[3])) $email = $argv[3];
} else {
    if (!empty($_GET['username'])) $username = $_GET['username'];
    if (!empty($_GET['password'])) $password_plain = $_GET['password'];
    if (!empty($_GET['email'])) $email = $_GET['email'];
}

try {
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $check->bind_param('s', $username);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows > 0) {
        echo "User '$username' already exists.\n";
        exit;
    }
    $check->close();

    $hash = password_hash($password_plain, PASSWORD_DEFAULT);

    // Try inserting with patient_id and doctor_id as NULL (common schema)
    $insert_sql = "INSERT INTO users (username, password, email, role, patient_id, doctor_id) VALUES (?, ?, ?, 'admin', NULL, NULL)";
    $stmt = $conn->prepare($insert_sql);
    if ($stmt) {
        $stmt->bind_param('sss', $username, $hash, $email);
        if ($stmt->execute()) {
            echo "Admin user created: username='$username' password='$password_plain'\n";
            echo "IMPORTANT: delete create_admin.php after use or change the password immediately.\n";
            $stmt->close();
            exit;
        } else {
            echo "Insert failed: " . $stmt->error . "\n";
            $stmt->close();
        }
    }

    // Fallback: try inserting without patient/doctor columns
    $insert_sql2 = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')";
    $stmt2 = $conn->prepare($insert_sql2);
    if ($stmt2) {
        $stmt2->bind_param('sss', $username, $hash, $email);
        if ($stmt2->execute()) {
            echo "Admin user created (fallback): username='$username' password='$password_plain'\n";
            echo "IMPORTANT: delete create_admin.php after use or change the password immediately.\n";
            $stmt2->close();
            exit;
        } else {
            echo "Fallback insert failed: " . $stmt2->error . "\n";
            $stmt2->close();
        }
    }

    echo "Unable to create admin user. Please check your users table schema and create the user manually.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
