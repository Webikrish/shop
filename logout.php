<?php
// logout.php
session_start();

$logged_out = false;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

if (isset($_POST['confirm_logout'])) {
    // Destroy session completely
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(), '', 0, '/');
    
    $logged_out = true;
} elseif (isset($_POST['cancel_logout'])) {
    // Redirect back to home
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - ShopEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --accent: #ffd166;
            --dark: #2d3047;
            --light: #f7f9fc;
            --text: #333;
            --text-light: #666;
            --success: #06d6a0;
            --warning: #ffd166;
            --danger: #ef476f;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            line-height: 1.6;
        }

        .logout-container {
            width: 100%;
            max-width: 480px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logout-header {
            background: linear-gradient(135deg, var(--primary) 0%, #ff5252 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .logout-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.1;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            100% {
                transform: translate(-30px, -30px) rotate(360deg);
            }
        }

        .logout-icon {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .logout-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .logout-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .logout-content {
            padding: 40px 30px;
        }

        .user-info {
            display: flex;
            align-items: center;
            background-color: var(--light);
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 30px;
            border-left: 4px solid var(--secondary);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
            color: white;
            font-weight: 600;
        }

        .user-details h3 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .user-details p {
            color: var(--text-light);
            font-size: 14px;
        }

        .logout-message {
            text-align: center;
            margin-bottom: 30px;
        }

        .logout-message h2 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .logout-message p {
            color: var(--text-light);
            font-size: 16px;
        }

        .logout-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn {
            padding: 16px 24px;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }

        .btn-logout {
            background-color: var(--danger);
            color: white;
        }

        .btn-logout:hover {
            background-color: #d43f5a;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(239, 71, 111, 0.3);
        }

        .btn-cancel {
            background-color: transparent;
            color: var(--dark);
            border: 2px solid var(--border);
        }

        .btn-cancel:hover {
            background-color: var(--light);
            border-color: var(--text-light);
            transform: translateY(-3px);
        }

        .logout-success {
            text-align: center;
            padding: 40px 30px;
        }

        .success-icon {
            font-size: 80px;
            color: var(--success);
            margin-bottom: 20px;
            display: block;
            animation: successScale 0.5s ease-out;
        }

        @keyframes successScale {
            0% {
                transform: scale(0);
            }
            70% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        .logout-success h2 {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .logout-success p {
            color: var(--text-light);
            font-size: 16px;
            margin-bottom: 30px;
        }

        .btn-home {
            background-color: var(--primary);
            color: white;
            margin-top: 20px;
        }

        .btn-home:hover {
            background-color: #ff5252;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
        }

        .logout-footer {
            padding: 20px 30px;
            background-color: var(--light);
            text-align: center;
            color: var(--text-light);
            font-size: 14px;
            border-top: 1px solid var(--border);
        }

        .logout-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .logout-footer a:hover {
            text-decoration: underline;
        }

        .countdown {
            font-size: 14px;
            color: var(--text-light);
            margin-top: 20px;
            font-weight: 500;
        }

        .countdown-number {
            display: inline-block;
            background-color: var(--dark);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            line-height: 30px;
            text-align: center;
            margin: 0 5px;
            animation: countdownPulse 1s infinite;
        }

        @keyframes countdownPulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .logout-container {
                max-width: 100%;
            }
            
            .logout-header {
                padding: 30px 20px;
            }
            
            .logout-icon {
                font-size: 60px;
            }
            
            .logout-header h1 {
                font-size: 26px;
            }
            
            .logout-content {
                padding: 30px 20px;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
            }
            
            .user-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .btn {
                padding: 14px 20px;
            }
        }

        @media (max-width: 400px) {
            .logout-header h1 {
                font-size: 22px;
            }
            
            .logout-message h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php if (!$logged_out): ?>
    <div class="logout-container">
        <div class="logout-header">
            <i class="fas fa-sign-out-alt logout-icon"></i>
            <h1>Logout from ShopEasy</h1>
            <p>Secure logout from your account</p>
        </div>
        
        <div class="logout-content">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Logged in since <?php echo date('H:i', strtotime('-1 hour')); ?></p>
                </div>
            </div>
            
            <div class="logout-message">
                <h2>Are you sure you want to logout?</h2>
                <p>You will need to login again to access your account, cart, and order history.</p>
            </div>
            
            <div class="logout-options">
                <form method="POST" action="">
                    <button type="submit" name="confirm_logout" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Yes, Logout Now
                    </button>
                    
                    <button type="submit" name="cancel_logout" class="btn btn-cancel">
                        <i class="fas fa-times"></i>
                        No, Stay Logged In
                    </button>
                </form>
            </div>
        </div>
        
        <div class="logout-footer">
            <p>Need help? <a href="contact.php">Contact Support</a></p>
        </div>
    </div>
    <?php else: ?>
    <div class="logout-container">
        <div class="logout-header" style="background: linear-gradient(135deg, var(--success) 0%, #06c290 100%);">
            <i class="fas fa-check-circle logout-icon"></i>
            <h1>Logged Out Successfully</h1>
            <p>You have been securely logged out</p>
        </div>
        
        <div class="logout-success">
            <i class="fas fa-check-circle success-icon"></i>
            <h2>Goodbye, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>You have been successfully logged out of your ShopEasy account.</p>
            <p>Your session has been cleared for security.</p>
            
            <div class="countdown">
                Redirecting to homepage in <span class="countdown-number" id="countdown">5</span> seconds
            </div>
            
            <a href="index.php" class="btn btn-home">
                <i class="fas fa-home"></i>
                Go to Homepage
            </a>
        </div>
        
        <div class="logout-footer">
            <p>Want to login again? <a href="login.php">Click here to login</a></p>
        </div>
    </div>
    
    <script>
        // Countdown timer for redirect
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdownTimer = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownTimer);
                window.location.href = 'index.php';
            }
        }, 1000);
        
        // Redirect after 5 seconds
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 5000);
    </script>
    <?php endif; ?>
</body>
</html>