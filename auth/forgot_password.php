<?php
session_start();
include("../connection.php");

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Process forgot password request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists
    $check_query = "SELECT id, name FROM users WHERE email = '$email' LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $user = mysqli_fetch_assoc($check_result);
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete any existing tokens for this email
        $delete_query = "DELETE FROM password_resets WHERE email = '$email'";
        mysqli_query($conn, $delete_query);
        
        // Insert new token
        $insert_query = "INSERT INTO password_resets (email, token, expires_at) VALUES ('$email', '$token', '$expires_at')";
        
        if (mysqli_query($conn, $insert_query)) {
            // In production, send email. For now, display the token.
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_email'] = $email;
            $_SESSION['success'] = "Password reset link generated! Use the link below to reset your password.";
        } else {
            $_SESSION['error'] = "Failed to generate reset token. Please try again.";
        }
    } else {
        // Don't reveal if email exists or not for security
        $_SESSION['success'] = "If an account with that email exists, a password reset link has been generated.";
    }
}

include("../header.php");
?>

<div class="flex w-full items-center justify-center p-4 bg-gray-100 dark:bg-gray-900 transition-colors duration-200" style="min-height: calc(100vh - 64px);">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md transition-colors duration-200">
        <h2 class="text-3xl font-extrabold mb-6 text-gray-900 dark:text-white text-center border-b dark:border-gray-700 pb-3">
            <i class="fas fa-key mr-2 text-indigo-600"></i>Forgot Password
        </h2>

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

        <?php if (isset($_SESSION['reset_token'])): ?>
            <!-- Display Reset Link (In production, this would be emailed) -->
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-300 dark:border-blue-700 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">
                    <i class="fas fa-info-circle mr-1"></i> 
                    <strong>For demo purposes:</strong> Copy this link to reset your password.
                </p>
                <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-600 rounded p-3 break-all">
                    <code class="text-xs text-gray-800 dark:text-gray-200">
                        <?php 
                        $reset_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset_password.php?token=' . $_SESSION['reset_token'];
                        echo htmlspecialchars($reset_url);
                        ?>
                    </code>
                </div>
                <a href="reset_password.php?token=<?php echo $_SESSION['reset_token']; ?>" 
                   class="mt-3 w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-green-600 hover:bg-green-700 transition duration-150">
                    <i class="fas fa-arrow-right mr-2"></i> Go to Reset Page
                </a>
            </div>
            <?php 
            unset($_SESSION['reset_token']); 
            unset($_SESSION['reset_email']);
            ?>
        <?php else: ?>
            <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 text-center">
                Enter your email address and we'll generate a password reset link.
            </p>

            <form action="forgot_password.php" method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                    <input type="email" name="email" id="email" required 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter your registered email">
                </div>

                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            Remember your password? 
            <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign in here</a>
        </p>
    </div>
</div>
</main>
<?php include("../footer.php"); ?>
