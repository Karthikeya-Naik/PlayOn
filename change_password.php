<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $check_query = "SELECT password FROM users WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $user = $check_result->fetch_assoc();
    
    if ($user['password'] != $current_password) {
        $_SESSION['error_message'] = "Current password is incorrect";
    } else if ($new_password != $confirm_password) {
        $_SESSION['error_message'] = "New passwords do not match";
    } else {
        // Update password
        $update_query = "UPDATE users SET password = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_password, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Password changed successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to change password. Please try again.";
        }
    }
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - PlayOn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary: #0b4d8c;
            --primary-light: #e6f0fa;
            --primary-dark: #083968;
            --dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            scroll-behavior: smooth;
        }
        
        /* Navbar improvements */
        .navbar {
            padding: 12px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .nav-link {
            font-weight: 500;
            margin: 0 5px;
            position: relative;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 2px;
            background-color: var(--primary);
        }
        
        /* Password header */
        .password-header {
            background: linear-gradient(rgba(11, 77, 140, 0.85), rgba(8, 57, 104, 0.95));
            padding: 60px 0;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .password-header h1 {
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
        }
        
        .password-header p {
            animation: fadeInUp 1.2s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Breadcrumb */
        .breadcrumb-section {
            background-color: #f8f9fa;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .breadcrumb-section a {
            text-decoration: none;
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .breadcrumb-section a:hover {
            color: var(--primary-dark);
        }
        
        /* Section headings */
        h3, h4 {
            font-weight: 700;
            margin: 30px 0 15px;
            color: var(--dark);
            position: relative;
            padding-bottom: 10px;
        }
        
        h3:after, h4:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }
        
        /* Password card */
        .password-card {
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            transition: all 0.4s ease;
        }
        
        .password-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .password-card .card-header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            border: none;
        }
        
        .password-card .card-header h4 {
            font-weight: 700;
            margin: 0;
            padding-bottom: 0;
        }
        
        .password-card .card-header h4:after {
            display: none;
        }
        
        .password-card .card-body {
            padding: 30px;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            padding: 0.7rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(11, 77, 140, 0.25);
            border-color: #86b7fe;
        }
        
        label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: var(--dark);
        }
        
        /* Button styles */
        .btn {
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-primary, .btn-secondary {
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-link {
            font-weight: 500;
            text-decoration: none;
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .btn-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Alert styling */
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 30px;
            border: none;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        
        /* Password strength */
        .password-strength {
            height: 5px;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
        }
        
        .password-strength-bar {
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-weak {
            background-color: #dc3545;
            width: 25%;
        }
        
        .strength-medium {
            background-color: #ffc107;
            width: 50%;
        }
        
        .strength-strong {
            background-color: #198754;
            width: 100%;
        }
        
        /* Password tips card */
        .tips-card {
            transition: all 0.3s ease;
        }
        
        .tips-card:hover {
            transform: translateY(-5px);
        }
        
        .tips-card li {
            transition: all 0.3s ease;
            padding: 5px 0;
        }
        
        .tips-card li:hover {
            transform: translateX(5px);
        }
        
        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            width: 50px;
            height: 50px;
            text-align: center;
            line-height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            z-index: 9999;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .back-to-top:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-5px);
        }
        
        /* Footer */
        footer {
            padding-top: 3rem;
        }
        
        footer a {
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        footer a:hover {
            color: var(--primary) !important;
            padding-left: 5px;
        }
    </style>
</head>
<body>
    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="profile.php">My Profile</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Password Header -->
    <header class="password-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-5">Change Password</h1>
                    <p class="lead fs-5 mb-0">Update your account security</p>
                    <p class="lead fs-6 mb-0"><i class="fas fa-shield-alt me-2"></i> Keep your account secure with a strong password</p>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="password-card" data-aos="fade-up">
                    <div class="card-header">
                        <h4 class="mb-0 text-white"><i class="fas fa-lock me-2"></i>Change Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="change_password.php" method="post">
                            <div class="form-group">
                                <label for="current_password"><i class="fas fa-key me-2"></i>Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-lock me-2"></i>New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required onkeyup="checkPasswordStrength()">
                                <div class="password-strength mt-2">
                                    <div id="password-strength-bar" class="password-strength-bar"></div>
                                </div>
                                <small id="passwordHelp" class="form-text text-muted">
                                    For best security, use at least 8 characters with letters, numbers, and special characters.
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-check-circle me-2"></i>Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required onkeyup="checkPasswordMatch()">
                                <small id="confirmPasswordHelp" class="form-text"></small>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-block w-100">
                                    <i class="fas fa-save me-2"></i>Change Password
                                </button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="profile.php" class="btn btn-link">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="password-card tips-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header">
                        <h4 class="mb-0 text-white"><i class="fas fa-info-circle me-2"></i>Password Security Tips</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mt-3">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Use at least 8 characters</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Include uppercase and lowercase letters</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Include numbers and special characters</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Avoid using personal information</li>
                            <li><i class="fas fa-check text-success me-2"></i> Don't reuse passwords from other sites</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include 'footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS animations
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
            
            // Back to top button
            const backToTopButton = document.querySelector('.back-to-top');
            
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTopButton.style.display = 'flex';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });
            
            backToTopButton.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
        
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthBar = document.getElementById('password-strength-bar');
            
            // Remove existing classes
            strengthBar.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            
            if (password.length === 0) {
                strengthBar.style.width = '0';
                return;
            }
            
            // Simple password strength check
            let strength = 0;
            if (password.length >= 8) strength += 1;
            if (password.match(/[A-Z]/)) strength += 1;
            if (password.match(/[0-9]/)) strength += 1;
            if (password.match(/[^A-Za-z0-9]/)) strength += 1;
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 3) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmHelper = document.getElementById('confirmPasswordHelp');
            
            if (confirmPassword.length === 0) {
                confirmHelper.textContent = '';
                confirmHelper.classList.remove('text-success', 'text-danger');
            } else if (password === confirmPassword) {
                confirmHelper.textContent = 'Passwords match!';
                confirmHelper.classList.add('text-success');
                confirmHelper.classList.remove('text-danger');
            } else {
                confirmHelper.textContent = 'Passwords do not match!';
                confirmHelper.classList.add('text-danger');
                confirmHelper.classList.remove('text-success');
            }
        }
    </script>
</body>
</html>