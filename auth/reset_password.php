<?php
session_start();
include("../connection.php");

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$token = isset($_GET['token']) ? mysqli_real_escape_string($conn, $_GET['token']) : '';
$valid_token = false;
$token_email = '';

// Validate token
if (!empty($token)) {
    $token_query = "SELECT email, expires_at, used FROM password_resets WHERE token = '$token' LIMIT 1";
    $token_result = mysqli_query($conn, $token_query);
    
    if (mysqli_num_rows($token_result) > 0) {
        $token_data = mysqli_fetch_assoc($token_result);
        
        // Check if token is expired or used
        if ($token_data['used'] == 0 && strtotime($token_data['expires_at']) > time()) {
            $valid_token = true;
            $token_email = $token_data['email'];
        } else {
            $_SESSION['error'] = "This reset link has expired or already been used. Please request a new one.";
        }
    } else {
        $_SESSION['error'] = "Invalid reset token. Please request a new password reset.";
    }
}

// Process password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password)) {
        $_SESSION['error'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email_safe = mysqli_real_escape_string($conn, $token_email);
        
        // Update user password
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email_safe'";
        
        if (mysqli_query($conn, $update_query)) {
            // Mark token as used
            $mark_used = "UPDATE password_resets SET used = 1 WHERE token = '$token'";
            mysqli_query($conn, $mark_used);
            
            $_SESSION['success'] = "Password reset successful! You can now log in with your new password.";
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to reset password. Please try again.";
        }
    }
}

include("../header.php");
?>

<div class="flex w-full items-center justify-center p-4 bg-gray-100 dark:bg-gray-900 transition-colors duration-200" style="min-height: calc(100vh - 64px);">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md transition-colors duration-200">
        <h2 class="text-3xl font-extrabold mb-6 text-gray-900 dark:text-white text-center border-b dark:border-gray-700 pb-3">
            <i class="fas fa-lock mr-2 text-indigo-600"></i>Reset Password
        </h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($valid_token): ?>
            <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 text-center">
                Enter your new password for <strong class="text-indigo-600"><?php echo htmlspecialchars($token_email); ?></strong>
            </p>

            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                    <input type="password" name="password" id="password" required 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter new password (min 6 characters)">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Re-enter new password">
                </div>

                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <i class="fas fa-check mr-2"></i> Reset Password
                </button>
            </form>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-6xl text-gray-300 dark:text-gray-600 mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    This password reset link is invalid or has expired.
                </p>
                <a href="forgot_password.php" 
                   class="inline-flex items-center py-2 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
                    <i class="fas fa-redo mr-2"></i> Request New Reset Link
                </a>
            </div>
        <?php endif; ?>

        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            Remember your password? 
            <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign in here</a>
        </p>
    </div>
</div>
</main>
<?php include("../footer.php"); ?>
