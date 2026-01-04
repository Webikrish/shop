<?php
// register.php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $phone = sanitize($_POST['phone']);
    
    // Validation
    $errors = [];
    
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters!';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format!';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters!';
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Check if user already exists
        $checkSql = "SELECT id FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'User already exists with this email or username!';
        } else {
            // Hash password (use password_hash as per your database column)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user - match with your database column names
            $insertSql = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, country) 
                         VALUES (?, ?, ?, ?, ?, ?, 'India')";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("ssssss", $username, $email, $hashed_password, $first_name, $last_name, $phone);
            
            if ($stmt->execute()) {
                // Get the new user ID
                $new_user_id = $stmt->insert_id;
                
                // Set session variables
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['is_admin'] = 0;
                
                $success = 'Registration successful! Redirecting...';
                
                // Redirect after 2 seconds
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "index.php";
                    }, 2000);
                </script>';
                
            } else {
                $error = 'Registration failed. Please try again. Error: ' . $conn->error;
            }
        }
        
        $conn->close();
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ShopEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .register-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 550px;
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(to right, #4ecdc4, #44a08d);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .logo {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .logo span {
            color: white;
        }
        
        .register-header h2 {
            font-weight: 500;
            font-size: 20px;
            opacity: 0.9;
        }
        
        .register-body {
            padding: 40px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group.full-width {
            flex: 0 0 100%;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group label.required::after {
            content: ' *';
            color: #ff6b6b;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
        }
        
        .form-group i {
            position: absolute;
            right: 15px;
            top: 42px;
            color: #999;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #4ecdc4, #44a08d);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(78, 205, 196, 0.4);
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .links a {
            color: #4ecdc4;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: #38b2ac;
            text-decoration: underline;
        }
        
        .error {
            background-color: #ffeaea;
            color: #ff3838;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffcccc;
            font-size: 14px;
        }
        
        .success {
            background-color: #eaffea;
            color: #38c238;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #b3e6b3;
            font-size: 14px;
        }
        
        .form-footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        
        .form-footer a {
            color: #ff6b6b;
            text-decoration: none;
        }
        
        .password-toggle {
            cursor: pointer;
            z-index: 10;
        }
        
        .terms {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .terms input {
            margin-top: 5px;
        }
        
        .terms label {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }
        
        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .register-body {
                padding: 30px;
            }
        }
        
        @media (max-width: 480px) {
            .register-container {
                max-width: 100%;
            }
            
            .register-body {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-shopping-bag"></i> Shop<span>Easy</span>
            </div>
            <h2>Create your ShopEasy account in seconds</h2>
        </div>
        
        <div class="register-body">
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="required">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>" 
                               required placeholder="John">
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="required">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>" 
                               required placeholder="Doe">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username" class="required">Username</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" 
                               required placeholder="johndoe123">
                        <i class="fas fa-at"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" 
                               required placeholder="john@example.com">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" 
                               placeholder="+91 98765 43210">
                        <i class="fas fa-phone"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Minimum 6 characters">
                        <i class="fas fa-lock"></i>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="confirm_password" class="required">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Re-enter your password">
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                </div>
                
                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> 
                        <!-- and <a href="privacy.php" target="_blank">Privacy Policy</a> -->
                    </label>
                </div>
                
                <button type="submit" class="btn">Create Account</button>
            </form>
            
            <div class="links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <a href="index.php">Back to Home</a>
            </div>
            
            <div class="form-footer">
                <p>Secure registration with industry-standard encryption</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.parentElement.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
        
        // Real-time password validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
                confirmPassword.style.borderColor = '#ff6b6b';
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.style.borderColor = '#4ecdc4';
            }
        }
        
        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;
        
        // Username validation (no spaces)
        const username = document.getElementById('username');
        username.addEventListener('input', function() {
            if (this.value.includes(' ')) {
                this.value = this.value.replace(/\s/g, '');
            }
        });
        
        // Form submission feedback
        const form = document.getElementById('registerForm');
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>