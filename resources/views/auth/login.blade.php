<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Learning Management System">
    
    <title>LMS | Login</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Modern CSS -->
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: var(--dark-color);
            line-height: 1.6;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo img {
            max-height: 60px;
        }
        
        .login-form h2 {
            font-size: 1.8rem;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .login-form p {
            color: var(--gray-color);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: var(--transition);
            background-color: #fcfcfc;
            padding-right: 40px; /* Space for toggle icon */
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 38px;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray-color);
            background: none;
            border: none;
            font-size: 16px;
        }
        
        .password-toggle:hover {
            color: var(--dark-color);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background-color: white;
        }
        
        .form-control::placeholder {
            color: #adb5bd;
            font-size: 14px;
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 13px;
            font-size: 16px;
            font-weight: 500;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
            position: relative;
        }
        
        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }
        
        .btn.loading .spinner {
            display: inline-block;
        }
        
        .btn.loading .btn-text {
            display: inline-block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0 30px;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--gray-color);
            font-size: 14px;
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--light-gray);
        }
        
        .divider span {
            padding: 0 15px;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-gray);
            color: var(--gray-color);
            font-size: 16px;
            transition: var(--transition);
        }
        
        .social-btn:hover {
            background: #e2e6ea;
            transform: translateY(-2px);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
            color: var(--gray-color);
        }
        
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin-left: 5px;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
            display: block;
            text-align: left;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 25px;
            }
            
            .logo img {
                max-height: 50px;
            }
            
            .login-form h2 {
                font-size: 1.5rem;
            }
            
            .form-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <a href="index.html"><img src="images/logo/logo1.jpeg" alt="LMS Logo"></a>
        </div>
        
        <div class="login-form">
            <h2>Welcome Back</h2>
            
            
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required autofocus>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="far fa-eye" style="margin-top:28px"></i>
                    </button>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                {{-- <div class="form-footer">
                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div> --}}
                
                <button type="submit" class="btn" id="loginBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">Sign In</span>
                </button>
                <p>Please enter these credentials to access your account.<br />Email: superadmin@gmail.com<br />Password: password</p>
                {{-- <div class="register-link">
                    Don't have an account? <a href="#" id="nav-sign-tab" data-bs-toggle="tab" data-bs-target="#nav-sign">Sign up</a>
                </div> --}}
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./vendor/global/global.min.js"></script>
    <script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="./js/custom.js"></script>
    
    <!-- Password Toggle Script -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Login button loading spinner
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.classList.add('loading');
        });
    </script>
</body>
</html>