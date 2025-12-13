<?php
session_start();
include("../connection.php");

// Handle registration
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'student');

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
    } else {
        // Check availability
        $check_query = "SELECT id FROM users WHERE email='$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $status = 'pending';
            
            $insert_query = "INSERT INTO users (name, email, password, role, status) 
                             VALUES ('$username', '$email', '$hashed_password', '$role', '$status')";
                             
            if (mysqli_query($conn, $insert_query)) {
                $_SESSION['success'] = "Registration successful! Your account is pending approval.";
                header("Location: login.php");
                exit;
            } else {
                $_SESSION['error'] = "Registration failed. Please try again.";
            }
        }
    }
}

include("../header.php");
?>

<div class="min-h-[calc(100vh-80px)] flex">
    <!-- Left Side - Illustration/Brand -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-emerald-500 to-teal-600 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="absolute top-20 left-20 w-64 h-64 bg-white rounded-full"></div>
            <div class="absolute bottom-32 right-20 w-96 h-96 bg-white rounded-full"></div>
            <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-white rounded-full"></div>
        </div>
        
        <div class="relative z-10 flex flex-col justify-center px-16 text-white">
            <div class="mb-8">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-rocket text-3xl"></i>
                </div>
                <h1 class="text-4xl font-bold mb-4">Start Your Learning Journey</h1>
                <p class="text-xl text-emerald-100 leading-relaxed">
                    Join thousands of learners and educators. Access world-class courses and build skills that matter.
                </p>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-6 mt-8">
                <div>
                    <div class="text-3xl font-bold">100+</div>
                    <div class="text-emerald-200 text-sm">Courses</div>
                </div>
                <div>
                    <div class="text-3xl font-bold">50+</div>
                    <div class="text-emerald-200 text-sm">Instructors</div>
                </div>
                <div>
                    <div class="text-3xl font-bold">1000+</div>
                    <div class="text-emerald-200 text-sm">Students</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Side - Registration Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-md">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center justify-center gap-2 mb-8">
                <div class="w-10 h-10 bg-brand-blue rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-white text-xl"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">OLMS</span>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Create your account</h2>
                    <p class="text-gray-500 dark:text-gray-400">Start learning today - it's free!</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                        <span class="text-red-700 dark:text-red-300 text-sm"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="space-y-5">
                    <!-- Role Selection Cards -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">I want to:</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="student" class="sr-only peer" checked>
                                <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-center 
                                            peer-checked:border-brand-blue peer-checked:bg-brand-blue-light dark:peer-checked:bg-blue-900/30 
                                            hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                                    <i class="fas fa-user-graduate text-2xl text-brand-blue mb-2"></i>
                                    <div class="font-semibold text-gray-900 dark:text-white text-sm">Learn</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">As a Student</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="lecturer" class="sr-only peer">
                                <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-center 
                                            peer-checked:border-brand-blue peer-checked:bg-brand-blue-light dark:peer-checked:bg-blue-900/30 
                                            hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                                    <i class="fas fa-chalkboard-teacher text-2xl text-brand-blue mb-2"></i>
                                    <div class="font-semibold text-gray-900 dark:text-white text-sm">Teach</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">As a Lecturer</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Full Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="username" id="username" required 
                                placeholder="John Doe"
                                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-brand-blue dark:focus:border-brand-blue transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" id="email" required 
                                placeholder="you@example.com"
                                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-brand-blue dark:focus:border-brand-blue transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" id="password" required 
                                placeholder="Create a strong password"
                                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-brand-blue dark:focus:border-brand-blue transition-colors">
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>Use 8+ characters with letters and numbers
                        </p>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="terms" required 
                            class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-blue focus:ring-brand-blue">
                        <label for="terms" class="text-sm text-gray-600 dark:text-gray-400">
                            I agree to the <a href="#" class="text-brand-blue hover:underline">Terms of Service</a> 
                            and <a href="#" class="text-brand-blue hover:underline">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" 
                        class="w-full bg-brand-blue hover:bg-brand-blue-dark text-white font-semibold py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/30 hover:-translate-y-0.5">
                        Create Account
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">or sign up with</span>
                    </div>
                </div>

                <!-- Social Login Buttons (Visual Only) -->
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" class="flex items-center justify-center gap-2 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Google</span>
                    </button>
                    <button type="button" class="flex items-center justify-center gap-2 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <i class="fab fa-github text-xl text-gray-900 dark:text-white"></i>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">GitHub</span>
                    </button>
                </div>

                <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
                    Already have an account? 
                    <a href="login.php" class="font-semibold text-brand-blue hover:text-brand-blue-dark transition-colors">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>