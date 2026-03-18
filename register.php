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
    <title>OCC Cooperative - Member Registration</title>
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
        .member-registration {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .registration-card {
            border: 2px solid rgba(0, 82, 164, 0.3);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 82, 164, 0.15);
        }

        .registration-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 82, 164, 0.2);
        }

        .member-id-section {
            background: linear-gradient(135deg, #0052a4 0%, #007bff 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .member-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }

        .password-suggestions {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .suggestion-item {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin: 0.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .suggestion-item:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .member-info {
            background: #e7f3ff;
            border-left: 4px solid #0052a4;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0 8px 8px 0;
        }

        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-floating input {
            border: 2px solid rgba(0, 82, 164, 0.2);
            border-radius: 8px;
            padding: 1rem 1rem 2.5rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-floating input:focus {
            border-color: #0052a4;
            box-shadow: 0 0 0 0.2rem rgba(0, 82, 164, 0.25);
        }

        .form-floating label {
            color: #6c757d;
            font-weight: 500;
        }

        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 8px;
            padding: 1rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }

        .validation-steps {
            display: flex;
            justify-content: space-between;
            margin: 1rem 0;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .step.active {
            background: #28a745;
            color: white;
        }

        .step.pending {
            background: #ffc107;
            color: #212529;
        }

        .step.completed {
            background: #007bff;
            color: white;
        }

        .password-requirements {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 0.75rem;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .requirement {
            margin: 0.25rem 0;
        }

        .requirement.met {
            color: #28a745;
        }

        .requirement.unmet {
            color: #dc3545;
        }

        .easy-remember {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .login-link {
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="member-registration">
    <!-- Loading Overlay -->
    <div class="loader-overlay" id="loader">
        <div class="loader-content">
            <div class="spinner"></div>
            <p class="text-primary mt-3">Creating your account...</p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="registration-card shadow-lg fade-in">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <img src="images/main_logo.jpg" class="logo-img" alt="OCC Cooperative Logo">
                            <h4 class="fw-bold text-primary mt-3">OPOL COMMUNITY COLLEGE</h4>
                            <h5 class="fw-bold text-secondary mb-2">EMPLOYEES CREDIT COOPERATIVE</h5>
                            <p class="text-muted">Member Registration Portal</p>
                        </div>

                        <!-- Member ID Validation Section -->
                        <div class="member-id-section">
                            <h6 class="mb-3"><i class="fas fa-users me-2"></i>Member Verification</h6>
                            <div class="member-badge">
                                <i class="fas fa-id-badge me-2"></i>Cooperative Member
                            </div>
                        </div>

                        <!-- Alert Messages -->
                        <div id="message-show"></div>

                        <!-- Registration Form -->
                        <form id="form-register">
                            <input type="hidden" name="check_register" value="1">
                            
                            <!-- Member ID -->
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member_id" name="member_id" 
                                       placeholder="Enter your Member ID" required min="1">
                                <label for="member_id">
                                    <i class="fas fa-id-badge me-2"></i>Member ID
                                </label>
                            </div>

                            <!-- Email -->
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Enter your email address" required>
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                            </div>

                            <!-- Password -->
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Create your password" required>
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="password-requirements" id="passwordRequirements">
                                    <div class="requirement unmet" id="req-length">
                                        <i class="fas fa-times me-1"></i>At least 8 characters
                                    </div>
                                    <div class="requirement unmet" id="req-uppercase">
                                        <i class="fas fa-times me-1"></i>One uppercase letter
                                    </div>
                                    <div class="requirement unmet" id="req-lowercase">
                                        <i class="fas fa-times me-1"></i>One lowercase letter
                                    </div>
                                    <div class="requirement unmet" id="req-number">
                                        <i class="fas fa-times me-1"></i>One number
                                    </div>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm your password" required>
                                <label for="confirm_password">
                                    <i class="fas fa-lock me-2"></i>Confirm Password
                                </label>
                            </div>

                            <!-- Password Suggestions -->
                            <div class="password-suggestions">
                                <h6 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Easy to Remember Password Suggestions:</h6>
                                <div id="suggestionsContainer">
                                    <div class="suggestion-item">Loading suggestions...</div>
                                </div>
                            </div>

                            <!-- Easy to Remember Tips -->
                            <div class="easy-remember">
                                <h6><i class="fas fa-brain me-2"></i>Password Tips:</h6>
                                <ul class="mb-0">
                                    <li>Use a combination of your Member ID + memorable word</li>
                                    <li>Include the current year for easy recall</li>
                                    <li>Use cooperative-related terms like "Coop2025"</li>
                                    <li>Combine your name with birth year or favorite number</li>
                                </ul>
                            </div>

                            <!-- Terms and Register -->
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    I confirm that I am a registered member of OCC Employees Credit Cooperative and agree to the terms and conditions
                                </label>
                            </div>

                            <button type="submit" class="btn btn-register w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Member Account
                            </button>

                            <!-- Validation Steps -->
                            <div class="validation-steps">
                                <div class="step pending" id="step1">
                                    <i class="fas fa-search me-1"></i>Verify Member
                                </div>
                                <div class="step pending" id="step2">
                                    <i class="fas fa-user me-1"></i>Create Account
                                </div>
                                <div class="step pending" id="step3">
                                    <i class="fas fa-check me-1"></i>Ready to Login
                                </div>
                            </div>
                        </form>

                        <!-- Back to Login -->
                        <div class="login-link">
                            <p class="mb-0">Already have an account? 
                                <a href="index.php" class="text-primary">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load password suggestions on page load
            loadPasswordSuggestions();

            // Password strength checker
            $('#password').on('input', function() {
                const password = $(this).val();
                checkPasswordRequirements(password);
            });

            // Member ID validation
            $('#member_id').on('blur', function() {
                const memberId = $(this).val();
                if (memberId.length >= 3) {
                    validateMemberId(memberId);
                }
            });

            // Form submission
            $('#form-register').on('submit', function(e) {
                e.preventDefault();
                
                const password = $('#password').val();
                const confirmPassword = $('#confirm_password').val();
                
                if (password !== confirmPassword) {
                    showMessage('Passwords do not match!', 'error');
                    return;
                }
                
                if (password.length < 8) {
                    showMessage('Password must be at least 8 characters long!', 'error');
                    return;
                }
                
                if (!$('#agreeTerms').is(':checked')) {
                    showMessage('Please agree to the terms and conditions!', 'error');
                    return;
                }
                
                $("#loader").show();
                $("#message-show").html("");
                var data = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: './admin/register_member.php',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        $("#loader").hide();
                        
                        if (response.status === 'success') {
                            showMessage(response.message, 'success');
                            updateValidationSteps([true, true, true]);
                            
                            // Show member info
                            showMemberInfo(response.member_info);
                            
                            // Reset form after delay
                            setTimeout(() => {
                                $('#form-register')[0].reset();
                                resetValidationSteps();
                                checkPasswordRequirements('');
                            }, 3000);
                        } else {
                            showMessage(response.message, 'error');
                            updateValidationSteps([false, false, false]);
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#loader").hide();
                        showMessage('Registration failed: ' + error, 'error');
                        updateValidationSteps([false, false, false]);
                    }
                });
            });

            // Password suggestion click handler
            $(document).on('click', '.suggestion-item', function() {
                $('#password').val($(this).text());
                $('#confirm_password').val($(this).text());
                checkPasswordRequirements($(this).text());
            });
        });

        function loadPasswordSuggestions() {
            $.ajax({
                type: 'GET',
                url: './admin/register_member.php?get_password_suggestions=1',
                dataType: 'json',
                success: function(response) {
                    const container = $('#suggestionsContainer');
                    container.empty();
                    
                    response.suggestions.forEach(function(suggestion) {
                        container.append('<div class="suggestion-item">' + suggestion + '</div>');
                    });
                }
            });
        }

        function validateMemberId(memberId) {
            updateValidationSteps([true, false, false]);
            
            $.ajax({
                type: 'POST',
                url: './admin/register_member.php',
                data: { 
                    validate_member: 1,
                    member_id: memberId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showMessage('Member ID verified! You can proceed with registration.', 'success');
                        updateValidationSteps([true, true, false]);
                    } else {
                        showMessage(response.message, 'error');
                        updateValidationSteps([false, false, false]);
                    }
                }
            });
        }

        function checkPasswordRequirements(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };

            Object.keys(requirements).forEach(req => {
                const element = $('#req-' + req);
                const icon = element.find('i');
                
                if (requirements[req]) {
                    element.removeClass('unmet').addClass('met');
                    icon.removeClass('fa-times').addClass('fa-check');
                } else {
                    element.removeClass('met').addClass('unmet');
                    icon.removeClass('fa-check').addClass('fa-times');
                }
            });
        }

        function updateValidationSteps(steps) {
            const stepClasses = ['pending', 'completed', 'completed'];
            steps.forEach((completed, index) => {
                const stepElement = $('#step' + (index + 1));
                stepElement.removeClass('pending completed active').addClass(stepClasses[completed ? 1 : 0]);
            });
        }

        function resetValidationSteps() {
            updateValidationSteps([false, false, false]);
        }

        function showMemberInfo(memberInfo) {
            const infoHtml = `
                <div class="member-info">
                    <h6><i class="fas fa-check-circle text-success me-2"></i>Registration Successful!</h6>
                    <p><strong>Member ID:</strong> ${memberInfo.member_id}</p>
                    <p><strong>Name:</strong> ${memberInfo.name}</p>
                    <p><strong>Username:</strong> ${memberInfo.username}</p>
                    <p class="mb-0"><i class="fas fa-info-circle text-info me-2"></i>You can now login with your Member ID and Email.</p>
                </div>
            `;
            $('#message-show').html(infoHtml);
        }

        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'isa_success' : 'isa_error';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            $("#message-show").html(
                `<div class="${alertClass} fade-in">
                    <i class="fas ${icon} me-2"></i>${message}
                </div>`
            );
        }
    </script>
</body>
</html>
