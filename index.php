<?php
session_start();

if (isset($_SESSION['is_login_yes']) && $_SESSION['is_login_yes'] == 'yes') {

    if ($_SESSION['session_type'] == 'admin') {
        header("Location: admin/index.php");
    } elseif ($_SESSION['session_type'] == 'cashier') {
        header("Location: admin/pos.php");
    } elseif ($_SESSION['session_type'] == 'member') {
        header("Location: member/dashboard.php");
    } else {
        header("Location: admin/index.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>OCCECO-Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="POS SOFTWARE, Cooperative Management System" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/login-style.css" rel="stylesheet" type="text/css" media="all" />
    
    <style>
        .isa_info,
        .isa_success,
        .isa_warning,
        .isa_error {
            margin: 10px 0px;
            padding: 12px;
            border-radius: 8px;
        }

        .isa_error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .isa_success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(161, 201, 224, 0.25);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loader-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(161, 201, 224, 0.4);
            border-top: 4px solid #0052a4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            border: 2px solid rgba(0, 82, 164, 0.3);
            border-radius: 8px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 82, 164, 0.15);
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #0052a4;
            box-shadow: 0 4px 12px rgba(0, 82, 164, 0.3);
        }

        .form-control {
            border: 1px solid rgba(0, 82, 164, 0.4);
            border-radius: 4px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: #0052a4;
            box-shadow: 0 0 0 0.2rem rgba(0, 82, 164, 0.25);
        }

        .input-group {
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid rgba(0, 82, 164, 0.4);
        }

        .input-group-text {
            border: 1px solid rgba(0, 82, 164, 0.4);
            border-right: none;
            background: rgba(0, 82, 164, 0.1);
            border-radius: 4px 0 0 4px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 4px 4px 0;
        }

        .input-group .btn {
            border-left: none;
            border-radius: 0 4px 4px 0;
            border: 1px solid rgba(0, 82, 164, 0.4);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0052a4 0%, #007bff 100%);
            border: 1px solid rgba(0, 82, 164, 0.5);
            border-radius: 4px;
            padding: 12px 24px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #004090 0%, #0066cc 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 82, 164, 0.3);
        }

        .btn-outline-secondary {
            border: 1px solid rgba(0, 82, 164, 0.4);
            border-radius: 0 4px 4px 0;
            background: rgba(0, 82, 164, 0.1);
        }

        .form-check-input {
            border-radius: 6px;
            width: 18px;
            height: 18px;
        }

        .form-check-input:checked {
            background-color: #0052a4;
            border-color: #0052a4;
        }

        .input-focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 82, 164, 0.25);
        }

        .input-focus .input-group-text {
            border-color: #0052a4;
            background: #e7f1ff;
        }

        .bg-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Form transition animations */
        .form-container {
            position: relative;
            min-height: 380px;
        }

        .form-panel {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .form-panel.hidden {
            opacity: 0;
            transform: translateX(-100%);
            pointer-events: none;
        }

        .form-panel.slide-in-right {
            opacity: 0;
            transform: translateX(100%);
        }

        .form-panel.slide-in-right.active {
            opacity: 1;
            transform: translateX(0);
        }

        .register-link {
            color: #0052a4;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .register-link:hover {
            color: #007bff;
            text-decoration: underline;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .back-link:hover {
            color: #0052a4;
            text-decoration: underline;
        }

        .form-title {
            transition: all 0.3s ease;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: 1px solid rgba(40, 167, 69, 0.5);
            border-radius: 4px;
            padding: 12px 24px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }

        .password-strength {
            height: 2px;
            border-radius: 2px;
            margin-top: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }

        /* Compact form styling */
        .register-form .mb-3 {
            margin-bottom: 0.5rem !important;
        }

        .register-form .form-label {
            font-size: 0.8rem;
            margin-bottom: 0.2rem;
        }

        .register-form .form-control {
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }

        .register-form .input-group-text {
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }

        .register-form .btn-outline-secondary {
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }

        .register-form .form-check {
            margin-bottom: 0.5rem;
        }

        .register-form .form-check-label {
            font-size: 0.8rem;
        }

        .register-form .btn-lg {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }

        .register-link-text {
            font-size: 0.8rem;
        }

        .compact-row {
            display: flex;
            gap: 0.5rem;
        }

        .compact-row .flex-fill {
            flex: 1;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .text-center.mb-4 {
            margin-bottom: 1rem !important;
        }
    </style>
</head>
<body class="bg-gradient">
    <!-- Loading Overlay -->
    <div class="loader-overlay" id="loader">
        <div class="loader-content">
            <div class="spinner"></div>
            <p class="text-primary mt-3">Logging in...</p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-4 col-md-6 col-sm-8 col-11">
                <div class="login-card shadow-lg fade-in">
                    <div class="card-body p-5">
                        <!-- Logo Section -->
                        <div class="text-center mb-4">
                            <img src="images/main_logo.jpg" class="logo-img" alt="OCC Cooperative Logo">
                            <h4 class="fw-bold text-primary mt-3">OPOL COMMUNITY COLLEGE</h4>
                            <h5 class="fw-bold text-secondary mb-2">EMPLOYEES CREDIT COOPERATIVE</h5>
                            <p class="text-muted form-title" id="formTitle">Please login to your account</p>
                        </div>

                        <!-- Alert Messages -->
                        <div id="message-show"></div>

                        <!-- Form Container -->
                        <div class="form-container">
                            <!-- Login Form -->
                            <div id="loginPanel" class="form-panel">
                                <form id="form-login" class="login-form">
                                    <input type="hidden" name="check-login" />
                                    
                                    <div class="mb-4">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user me-2"></i>Username
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   placeholder="Enter your username" required />
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Enter your password" required />
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-4 form-check">
                                        <input type="checkbox" class="form-check-input" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">
                                            Remember me
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </button>

                                    <div class="text-center mt-4">
                                        <!-- <p class="mb-0">Don't have an account?  -->
                                            <span class="register-link" id="showRegister">
                                                <i class="fas fa-user-plus me-1"></i>Register here
                                            </span>
                                        </p>
                                    </div>
                                </form>
                            </div>

                            <!-- Registration Form -->
                            <div id="registerPanel" class="form-panel slide-in-right hidden">
                                <form id="form-register" class="register-form">
                                    <input type="hidden" name="check-register" />
                                    
                                    <div class="mb-3">
                                        <label for="reg_email" class="form-label">
                                            <i class="fas fa-envelope me-1"></i>Email Address
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" id="reg_email" name="email" 
                                                   placeholder="your@email.com" required />
                                            <button class="btn btn-outline-secondary" type="button" id="validateEmail">
                                                <i class="fas fa-check"></i> Validate
                                            </button>
                                        </div>
                                        <small id="emailValidationResult" class="form-text text-muted"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reg_username" class="form-label">
                                            <i class="fas fa-user me-1"></i>Username
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control" id="reg_username" name="username" 
                                                   placeholder="Choose username" required />
                                        </div>
                                    </div>

                                 

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                                        <label class="form-check-label" for="agreeTerms">
                                            I agree to the terms and conditions
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-lg w-100 mb-3" id="registerBtn" disabled>
                                        <i class="fas fa-user-plus me-2"></i>Activate Account
                                    </button>

                                    <div class="text-center mt-3">
                                        <p class="mb-0 register-link-text">Already have an account? 
                                            <span class="back-link" id="showLogin">
                                                <i class="fas fa-arrow-left me-1"></i>Back to login
                                            </span>
                                        </p>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility for login
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                
                const icon = $(this).find('i');
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            // Toggle password visibility for registration
            $('#toggleRegPassword').on('click', function() {
                const passwordField = $('#reg_password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                
                const icon = $(this).find('i');
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            // Toggle confirm password visibility
            $('#toggleConfirmPassword').on('click', function() {
                const passwordField = $('#reg_confirm_password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                
                const icon = $(this).find('i');
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            // Show registration form
            $('#showRegister').on('click', function() {
                $('#loginPanel').addClass('hidden');
                $('#registerPanel').removeClass('hidden slide-in-right').addClass('active');
                $('#formTitle').text('Activate your account');
                $('.loader-overlay p').text('Registering...');
            });

            // Show login form
            $('#showLogin').on('click', function() {
                $('#registerPanel').removeClass('active').addClass('slide-in-right');
                setTimeout(() => {
                    $('#registerPanel').addClass('hidden');
                    $('#loginPanel').removeClass('hidden');
                }, 50);
                $('#formTitle').text('Please login to your account');
                $('.loader-overlay p').text('Logging in...');
            });

            // Login form submission
            $('#form-login').on('submit', function(e) {
                e.preventDefault();
                $("#loader").show();
                $("#message-show").html("");
                var data = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: './transaction.php',
                    data: data,
                    success: function(msg) {
                        msg = $.trim(msg);
                        console.log("Server response:", msg);
                        $("#loader").hide();

                        if (msg === "5") {
                            $("#message-show").html(
                                '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Invalid credentials. Please try again!</div>'
                            );
                        } else if (msg === "1") {
                            window.location = 'admin/index.php';
                        } else if (msg === "2") {
                            window.location = 'admin/pos.php';
                        } else if (msg === "3") {
                            window.location = 'admin/index.php';
                        } else if (msg === "4") {
                            window.location = 'member/dashboard.php';
                        } else {
                            $("#message-show").html(
                                '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Unexpected server response: ' + msg + '</div>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#loader").hide();
                        $("#message-show").html(
                            '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Error: ' + error + '</div>'
                        );
                    }
                });
            });

            // Email validation
            let emailValidated = false;
            let memberData = null;

            $('#validateEmail').on('click', function() {
                const email = $('#reg_email').val().trim();
                
                if (!email) {
                    $("#emailValidationResult").html('<span class="text-danger"><i class="fas fa-times"></i> Please enter an email address</span>');
                    return;
                }
                
                // Basic email format validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    $("#emailValidationResult").html('<span class="text-danger"><i class="fas fa-times"></i> Please enter a valid email address</span>');
                    return;
                }
                
                $("#emailValidationResult").html('<span class="text-info"><i class="fas fa-spinner fa-spin"></i> Validating...</span>');
                
                $.ajax({
                    type: 'POST',
                    url: './transaction.php',
                    data: { 
                        validate_email: email 
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        
                        if (data.success) {
                            emailValidated = true;
                            memberData = data.member;
                            
                            $("#emailValidationResult").html(
                                '<span class="text-success"><i class="fas fa-check"></i> ' + data.message + '</span>'
                            );
                            
                            // Show member info if available
                            if (data.member) {
                                const memberInfo = `
                                    <div class="alert alert-info mt-2">
                                        <h6><i class="fas fa-user me-2"></i>Member Information Found:</h6>
                                        <p class="mb-1"><strong>Name:</strong> ${data.member.fullname}</p>
                                    </div>
                                `;
                                $("#emailValidationResult").append(memberInfo);
                            }
                            
                            $('#registerBtn').prop('disabled', false);
                        } else {
                            emailValidated = false;
                            memberData = null;
                            
                            $("#emailValidationResult").html(
                                '<span class="text-danger"><i class="fas fa-times"></i> ' + data.message + '</span>'
                            );
                            
                            $('#registerBtn').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $("#emailValidationResult").html(
                            '<span class="text-danger"><i class="fas fa-times"></i> Error validating email</span>'
                        );
                        $('#registerBtn').prop('disabled', true);
                    }
                });
            });

            // Clear validation when email changes
            $('#reg_email').on('input', function() {
                emailValidated = false;
                $("#emailValidationResult").html('');
                $('#registerBtn').prop('disabled', true);
            });

            // Registration form submission
            $('#form-register').on('submit', function(e) {
                e.preventDefault();
                
                if (!emailValidated) {
                    $("#message-show").html(
                        '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Please validate your email first!</div>'
                    );
                    return;
                }
                
                $("#loader").show();
                $("#message-show").html("");
                var data = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: './transaction.php',
                    data: data,
                    success: function(msg) {
                        msg = $.trim(msg);
                        console.log("Server response:", msg);
                        $("#loader").hide();

                        if (msg === "success") {
                            $("#message-show").html(
                                '<div class="isa_success fade-in"><i class="fas fa-check-circle me-2"></i> Registration successful! A secure password has been sent to your email address.</div>'
                            );
                            setTimeout(() => {
                                $('#showLogin').click();
                                $('#form-register')[0].reset();
                                emailValidated = false;
                                memberData = null;
                                $('#registerBtn').prop('disabled', true);
                            }, 3000);
                        } else if (msg === "username_exists") {
                            $("#message-show").html(
                                '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Username already exists!</div>'
                            );
                        } else if (msg === "member_not_found") {
                            $("#message-show").html(
                                '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Email not found in member records. Only registered members can create accounts.</div>'
                            );
                        } else if (msg === "member_already_registered") {
                            $("#message-show").html(
                                '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> This member already has login credentials registered.</div>'
                            );
                        } else {
                            $("#message-show").html(
                                '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Registration failed: ' + msg + '</div>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#loader").hide();
                        $("#message-show").html(
                            '<div class="isa_error fade-in"><i class="fas fa-exclamation-triangle me-2"></i> Error: ' + error + '</div>'
                        );
                    }
                });
            });

            // Add input focus effects
            $('input[type="text"], input[type="password"], input[type="email"]').on('focus', function() {
                $(this).closest('.input-group').addClass('input-focus');
            }).on('blur', function() {
                $(this).closest('.input-group').removeClass('input-focus');
            });
        });
    </script>
</body>
</html>



