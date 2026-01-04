<?php
require_once '../config.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if user exists
    $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = 'Username or email already exists';
    } else {
        $sql = "INSERT INTO users (username, email, first_name, last_name, phone, password_hash, is_active) 
                VALUES ('$username', '$email', '$first_name', '$last_name', '$phone', '$password', 1)";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = 'User added successfully';
        } else {
            $_SESSION['error'] = 'Error adding user: ' . $conn->error;
        }
    }
    
    $conn->close();
    header('Location: users.php');
    exit();
}
?>