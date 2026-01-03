<?php
// address-ajax.php - Handle address operations via AJAX
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add':
            // Validate required fields
            $required = ['name', 'phone', 'address_line1', 'city', 'state', 'zip_code', 'country'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
                    exit();
                }
            }
            
            // If setting as default, remove default from other addresses
            if (isset($_POST['is_default']) && $_POST['is_default'] == 1) {
                $update_sql = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $user_id);
                $update_stmt->execute();
            }
            
            // Insert new address
            $sql = "INSERT INTO addresses (user_id, name, phone, address_line1, address_line2, 
                                           city, state, country, zip_code, is_default) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssssi", 
                $user_id,
                $_POST['name'],
                $_POST['phone'],
                $_POST['address_line1'],
                $_POST['address_line2'] ?? '',
                $_POST['city'],
                $_POST['state'],
                $_POST['country'],
                $_POST['zip_code'],
                $is_default
            );
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Address added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add address']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

$conn->close();
?>