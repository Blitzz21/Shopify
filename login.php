<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to GameDayTees - Access your account and manage your custom printed products">
    <title>Login - GameDayTees</title>
    <link rel="icon" type="image/png" href="./frontend/img/gdt-small-logo.png">
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Custom Color Palette */
        :root {
            --navy-dark: #0C2B4E;
            --navy-medium: #1A3D64;
            --navy-light: #1D546C;
            --light-gray: #F4F4F4;
            --white: #FFFFFF;
        }
        
        /* Apply Poppins font */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
        }
        
        /* Custom utility classes */
        .bg-navy-dark { background-color: var(--navy-dark); }
        .bg-navy-medium { background-color: var(--navy-medium); }
        .bg-navy-light { background-color: var(--navy-light); }
        .bg-light-gray { background-color: var(--light-gray); }
        
        .text-navy-dark { color: var(--navy-dark); }
        .text-navy-medium { color: var(--navy-medium); }
        .text-navy-light { color: var(--navy-light); }
        
        .border-navy-dark { border-color: var(--navy-dark); }
        .border-navy-medium { border-color: var(--navy-medium); }
        .border-navy-light { border-color: var(--navy-light); }
        
        /* Custom button styles */
        .btn-primary {
            background-color: var(--navy-medium);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--navy-dark);
        }
        
        /* Gradient backgrounds */
        .gradient-navy {
            background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy-light) 100%);
        }

        /* Loading spinner */
        .loading-spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #1A3D64;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen mt-14" style="font-family: 'Poppins', sans-serif;">
    <!-- Enhanced Professional Navbar -->
    <header id="navbar" class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-300 transition-all duration-300">
        <nav class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18">
                <!-- Logo -->
                <div class="flex items-center py-4">
                    <a href="index.html" class="group relative flex items-center gap-3">
                        <div class="relative">
                            <img src="/Shopify/frontend/img/gdt-top-logo.svg" alt="GameDayTees"
                                class="h-11 w-auto transition-all duration-300 group-hover:scale-105"
                                width="150" height="70"
                                onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjcwIiB2aWV3Qm94PSIwIDAgMTUwIDcwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxNTAiIGhlaWdodD0iNzAiIGZpbGw9IiMzQjgyRjYiLz48dGV4dCB4PSIyNSIgeT0iNDAiIGZpbGw9IndoaXRlIiBmb250LWZhbWlseT0iUG9wcGlucyIgZm9udC13ZWlnaHQ9IjYwMCIgZm9udC1zaXplPSIxNiI+R2FtZURheVRlZXM8L3RleHQ+PC9zdmc+'">
                            <div class="absolute inset-0 bg-blue-400/20 rounded-lg blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10"></div>
                        </div>
                    </a>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-2">
                    <!-- Register Button -->
                    <a href="register.php" class="hidden lg:flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-blue-600 transition-all duration-200 rounded-xl hover:bg-blue-50 hover:scale-105 active:scale-95">
                        Create Account
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Spacer to prevent content from hiding under fixed navbar -->
    <div class="h-18"></div>

    <!-- Main Login Section -->
    <section class="min-h-[calc(100vh-72px)] flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full space-y-8 fade-in">
            <!-- Header -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 mb-4 bg-blue-100 rounded-2xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Welcome back</h2>
                <p class="mt-2 text-gray-600">Sign in to your GameDayTees account</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="mt-8 space-y-6 bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
                <div class="space-y-4">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                placeholder="Enter your email"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Password
                            </label>
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                placeholder="Enter your password"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden p-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg"></div>

                <!-- Success Message -->
                <div id="successMessage" class="hidden p-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg"></div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        id="loginButton"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span id="buttonText">Sign in</span>
                        <div id="loadingSpinner" class="hidden loading-spinner ml-2"></div>
                    </button>
                </div>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">New to GameDayTees?</span>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <a href="register.php" class="text-blue-600 hover:text-blue-500 font-semibold transition-colors duration-200">
                        Create your account
                    </a>
                </div>
            </form>

            <!-- Additional Info -->
            <div class="text-center text-sm text-gray-600">
                <p>By signing in, you agree to our <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a> and <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>.</p>
            </div>
        </div>
    </section>

    <script>
        class LoginManager {
            constructor() {
                this.loginForm = document.getElementById('loginForm');
                this.emailInput = document.getElementById('email');
                this.passwordInput = document.getElementById('password');
                this.loginButton = document.getElementById('loginButton');
                this.buttonText = document.getElementById('buttonText');
                this.loadingSpinner = document.getElementById('loadingSpinner');
                this.errorMessage = document.getElementById('errorMessage');
                this.successMessage = document.getElementById('successMessage');

                this.init();
            }

            init() {
                this.loginForm.addEventListener('submit', (e) => this.handleLogin(e));
                
                // Clear errors when user starts typing
                [this.emailInput, this.passwordInput].forEach(input => {
                    input.addEventListener('input', () => {
                        this.hideMessages();
                    });
                });
            }

            async handleLogin(e) {
                e.preventDefault();
                
                const email = this.emailInput.value.trim();
                const password = this.passwordInput.value;

                // Basic validation
                if (!this.validateForm(email, password)) {
                    return;
                }

                this.setLoading(true);

                try {
                    const response = await fetch('http://localhost/Shopify/backend/api/login.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email,
                            password: password
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('Login successful! Redirecting...');
                        
                        // Store user info in localStorage
                        localStorage.setItem('user', JSON.stringify(data.data));
                        localStorage.setItem('isLoggedIn', 'true');
                        
                        // Redirect based on user role
                        setTimeout(() => {
                            if (data.data.role === 'admin') {
                                window.location.href = '/Shopify/admin/dashboard.html';
                            } else {
                                window.location.href = '/Shopify/customer/dashboard.html';
                            }
                        }, 1500);

                    } else {
                        this.showError(data.message || 'Login failed. Please try again.');
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    this.showError('Network error. Please check your connection and try again.');
                } finally {
                    this.setLoading(false);
                }
            }

            validateForm(email, password) {
                this.hideMessages();

                if (!email) {
                    this.showError('Please enter your email address.');
                    return false;
                }

                if (!this.isValidEmail(email)) {
                    this.showError('Please enter a valid email address.');
                    return false;
                }

                if (!password) {
                    this.showError('Please enter your password.');
                    return false;
                }

                if (password.length < 8) {
                    this.showError('Password must be at least 8 characters long.');
                    return false;
                }

                return true;
            }

            isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            setLoading(loading) {
                if (loading) {
                    this.loginButton.disabled = true;
                    this.buttonText.textContent = 'Signing in...';
                    this.loadingSpinner.classList.remove('hidden');
                } else {
                    this.loginButton.disabled = false;
                    this.buttonText.textContent = 'Sign in';
                    this.loadingSpinner.classList.add('hidden');
                }
            }

            showError(message) {
                this.errorMessage.textContent = message;
                this.errorMessage.classList.remove('hidden');
                this.successMessage.classList.add('hidden');
            }

            showSuccess(message) {
                this.successMessage.textContent = message;
                this.successMessage.classList.remove('hidden');
                this.errorMessage.classList.add('hidden');
            }

            hideMessages() {
                this.errorMessage.classList.add('hidden');
                this.successMessage.classList.add('hidden');
            }
        }

        // Check if user is already logged in
        function checkAuthStatus() {
            const isLoggedIn = localStorage.getItem('isLoggedIn');
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            
            if (isLoggedIn === 'true' && user.role) {
                // Redirect based on role
                if (user.role === 'admin') {
                    window.location.href = 'admin/dashboard.html';
                } else {
                    window.location.href = 'customer/dashboard.html';
                }
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            checkAuthStatus();
            new LoginManager();
        });
    </script>
</body>
</html>