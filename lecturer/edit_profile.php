<?php
session_start();
include("../connection.php");
include("../header.php");

// --- Access Control ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to edit your profile.";
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied. Only lecturer can edit profiles.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_id_safe = mysqli_real_escape_string($conn, $user_id);

// --- 1. Fetch Current User Data (For display) ---
$query = "SELECT name, email FROM users WHERE id='$user_id_safe' LIMIT 1";
$result = mysqli_query($conn, $query);
$current_user = mysqli_fetch_assoc($result);


// --- 2. Process Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $update_fields = [];
    $errors = [];

    // Validation checks for Name and Email
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        // Check if the email is being changed and if the new email already exists for *another* user
        if ($email !== $current_user['email']) {
            $check_query = "SELECT id FROM users WHERE email='$email' AND id != '$user_id_safe' LIMIT 1";
            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) > 0) {
                $errors[] = "Email already registered to another account.";
            }
        }
    }

    // Validation checks for Password (ONLY required if a new password is provided)
    if (!empty($password) || !empty($confirm_password)) {
        if (empty($password)) {
            $errors[] = "New Password is required if Confirm Password is set.";
        } elseif (empty($confirm_password)) {
            $errors[] = "Confirm Password is required if a New Password is set.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "New Password and Confirm Password must match.";
        }
    }

    // Build the update query based on changes
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    } else {
        // Always update name and email if validation passes
        $update_fields[] = "name='$username'";
        $update_fields[] = "email='$email'";

        // Only update password if a new, validated one was provided
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_fields[] = "password='$hashed_password'";
        }

        // This check ensures we only update if there's an actual change
        if (count($update_fields) === 0) {
            $_SESSION['error'] = "No changes submitted.";
        } else {
            $update_set_clause = implode(', ', $update_fields);

            // --- Execute the UPDATE query ---
            $update_query = "UPDATE users SET $update_set_clause WHERE id='$user_id_safe' LIMIT 1";

            if (mysqli_query($conn, $update_query)) {
                // Update session variables if email or name changed
                $_SESSION['username'] = $username;

                $_SESSION['success'] = "Profile updated successfully!";
                header("Location: edit_profile.php");
                exit;
            } else {
                $_SESSION['error'] = "Profile update failed: " . mysqli_error($conn);
            }
        }
    }
}
// Re-fetch user data in case of GET request or display after error
if (!isset($current_user) && mysqli_num_rows($result) > 0) {
    mysqli_data_seek($result, 0);
    $current_user = mysqli_fetch_assoc($result);
}

?>
<div class="flex flex-col items-center justify-center bg-gray-50" style="min-height: calc(100vh - 64px);">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-xl my-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-6 text-center border-b pb-3">
            Edit Your Profile
        </h2>

        <?php
        // Display success/error messages
        if (isset($_SESSION['success'])) {
            echo '<p class="p-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<p class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form method="POST" action="edit_profile.php" class="space-y-5">

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Name:</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($current_user['name'] ?? ''); ?>"
                    required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>"
                    required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password (Leave blank to keep current):</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter new password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-enter new password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <input
                    type="submit"
                    value="Update Profile"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out mt-6 cursor-pointer">
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="./dashboard.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Back to Dashboard</a>
        </p>

    </div>
</div>

<?php include("../footer.php"); ?>