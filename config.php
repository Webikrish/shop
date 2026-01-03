<?php
// config.php - Database configuration and helper functions
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'shopeasy_db';
    $username = 'root';
    $password = '';
    
    try {
        $conn = new mysqli($host, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch(Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>