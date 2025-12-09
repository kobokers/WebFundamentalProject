<?php
session_start();
include("../connection.php");

// Process login BEFORE including header
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        // Trim and normalize status
        $user_status = trim(strtolower($user['status']));
        
        // Check if account is pending approval
        if ($user_status === 'pending') {
            $_SESSION['error'] = "Your account is pending approval. Please wait for admin activation.";
        }
        // Check if account is active
        elseif ($user_status !== 'active') {
            $_SESSION['error'] = "Your account is inactive. Please contact admin for assistance.";
        }
        // Account is active, proceed with login
        else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] == 'lecturer') {
                header("Location: ../lecturer/dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        }
    } else {
        $_SESSION['error'] = "Invalid email or password";
    }
}

// NOW include header after all redirects are done
include("../header.php");
?>

<div class="flex w-full items-center justify-center p-4 bg-gray-100 dark:bg-gray-900 transition-colors duration-200" style="min-height: calc(100vh - 64px);">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md transition-colors duration-200">
        <h2 class="text-3xl font-extrabold mb-6 text-gray-900 dark:text-white text-center border-b dark:border-gray-700 pb-3">Welcome Back</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                <input type="email" name="email" id="email" required 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input type="password" name="password" id="password" required 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                Sign In
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            <a href="forgot_password.php" class="text-indigo-600 hover:text-indigo-500 font-medium">Forgot your password?</a>
        </p>

        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            Don't have an account? 
            <a href="register.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Register here</a>
        </p>
    </div>
</div>

<?php include("../footer.php"); ?>