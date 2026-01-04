<?php
// Database configuration
$host = '127.0.0.1';
$dbname = 'shopeasy_db';
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password

try {
    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    // Error reporting for development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>