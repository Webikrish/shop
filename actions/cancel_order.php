<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_number = $_POST['order_number'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $note = $_POST['note'] ?? '';
    
    if (empty($order_number) || empty($reason)) {
        header('Location: ../order.php?order_number=' . urlencode($order_number) . '&error=Missing required fields');
        exit();
    }
    
    // Check if order exists
    $stmt = $conn->prepare("SELECT id, order_status FROM orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: ../orders.php?error=Order not found');
        exit();
    }
    
    $order = $result->fetch_assoc();
    
    // Check if order can be cancelled
    if ($order['order_status'] !== 'processing') {
        header('Location: ../order.php?order_number=' . urlencode($order_number) . '&error=Cannot cancel order. Current status: ' . $order['order_status']);
        exit();
    }
    
    // Update order status to 'cancelled' and payment status to 'none'
    $update_stmt = $conn->prepare("UPDATE orders SET order_status = 'cancelled', payment_status = 'none' WHERE order_number = ?");
    $update_stmt->bind_param("s", $order_number);
    
    if ($update_stmt->execute()) {
        // You can also create a cancellation log in another table
        // For example: INSERT INTO order_cancellations (order_id, reason, note) VALUES (?, ?, ?)
        
        header('Location: ../order.php?order_number=' . urlencode($order_number) . '&success=Order cancelled successfully');
        exit();
    } else {
        header('Location: ../order.php?order_number=' . urlencode($order_number) . '&error=Failed to cancel order');
        exit();
    }
} else {
    header('Location: ../orders.php');
    exit();
}
?>