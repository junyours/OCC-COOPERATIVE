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
    <title>OCC Cooperative - Login</title>
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
                            <h5 class="fw-bold text-primary mt-3">Welcome Back</h2>
                            <p class="text-muted">Please login to your account</p>
                        </div>

                        <!-- Alert Messages -->
                        <div id="message-show"></div>

                        <!-- Login Form -->
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

                            <!-- <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Your connection is secure and encrypted
                                </small>
                            </div> -->
                        </form>

                        <!-- Footer Info -->
                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                2025 OCC Employees Credit Cooperative
                            </small>
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
            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                
                const icon = $(this).find('i');
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            // Form submission
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

            // Add input focus effects
            $('input[type="text"], input[type="password"]').on('focus', function() {
                $(this).closest('.input-group').addClass('input-focus');
            }).on('blur', function() {
                $(this).closest('.input-group').removeClass('input-focus');
            });
        });
    </script>
</body>
</html>