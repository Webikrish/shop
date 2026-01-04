<?php
require_once '../config.php';
redirectIfNotAdmin();

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    header('Location: users.php');
    exit();
}

$conn = getDBConnection();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $sql = "UPDATE users SET 
            first_name = '$first_name',
            last_name = '$last_name',
            email = '$email',
            phone = '$phone',
            is_active = $is_active
            WHERE id = $user_id AND is_admin = 0";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'User updated successfully';
    } else {
        $_SESSION['error'] = 'Error updating user';
    }
    
    header('Location: users.php');
    exit();
}

// Get user data
$result = $conn->query("SELECT * FROM users WHERE id = $user_id AND is_admin = 0");
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: users.php');
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit User</h4>
                        <a href="users.php" class="btn btn-sm btn-secondary float-end">Back</a>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" class="form-control" 
                                           value="<?php echo $user['first_name']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" class="form-control" 
                                           value="<?php echo $user['last_name']; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                            </div>
                            
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo $user['email']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Phone</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo $user['phone']; ?>">
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" 
                                       id="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active Account</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>