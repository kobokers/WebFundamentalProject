<?php
session_start();
include("../connection.php");

// --- Access Control ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to edit your profile.";
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Access denied. Only students can edit profiles here.";
    header("Location: ../index.php"); // or lecturer/edit_profile.php if you have one
    exit;
}

$user_id = $_SESSION['user_id'];
$user_id_safe = mysqli_real_escape_string($conn, $user_id);

// --- 1. Fetch Current User Data (For display) ---
$query = "SELECT name, email, profile_picture FROM users WHERE id='$user_id_safe' LIMIT 1";
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

    // Validation checks
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

    // Validation checks for Password
    if (!empty($password) || !empty($confirm_password)) {
        if (empty($password)) {
            $errors[] = "New Password is required if Confirm Password is set.";
        } elseif (empty($confirm_password)) {
            $errors[] = "Confirm Password is required if a New Password is set.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "New Password and Confirm Password must match.";
        }
    }
    
    // --- Profile Picture Upload ---
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file = $_FILES['profile_picture'];
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid image type. Only JPG, PNG, GIF, and WebP are allowed.";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "Image size must be less than 2MB.";
        } else {
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = '../uploads/avatars/' . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old avatar if exists
                if (!empty($current_user['profile_picture'])) {
                    $old_file = '../uploads/avatars/' . $current_user['profile_picture'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $update_fields[] = "profile_picture='$new_filename'";
            } else {
                $errors[] = "Failed to upload image.";
            }
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
                // Update session variables if name changed
                $_SESSION['user_name'] = $username;
                
                if (isset($new_filename)) {
                    $_SESSION['user_profile_picture'] = $new_filename;
                }

                $_SESSION['success'] = "Profile updated successfully!";
                header("Location: profile_edit.php");
                exit;
            } else {
                $_SESSION['error'] = "Profile update failed: " . mysqli_error($conn);
            }
        }
    }
}
// Re-fetch user data in case of GET request or display after error
$query = "SELECT name, email, profile_picture FROM users WHERE id='$user_id_safe' LIMIT 1";
$result = mysqli_query($conn, $query);
$current_user = mysqli_fetch_assoc($result);

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-coursera-blue transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 dark:text-white">Edit Profile</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-user-cog text-coursera-blue"></i>
                Profile Settings
            </h1>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="max-w-2xl mx-auto">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span class="text-green-700 dark:text-green-300"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="text-red-700 dark:text-red-300"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-8">
                <form method="POST" action="profile_edit.php" enctype="multipart/form-data" class="space-y-8">
                    
                    <!-- Profile Picture -->
                    <div class="flex flex-col items-center">
                        <div class="relative group">
                            <?php if (!empty($current_user['profile_picture'])): ?>
                                <img src="../uploads/avatars/<?php echo htmlspecialchars($current_user['profile_picture']); ?>" 
                                     alt="Profile Picture" id="preview-img"
                                     class="w-32 h-32 rounded-full object-cover border-4 border-coursera-blue/20 shadow-lg transition-transform duration-300 group-hover:scale-105">
                            <?php else: ?>
                                <div id="preview-placeholder" class="w-32 h-32 rounded-full bg-coursera-blue-light dark:bg-blue-900/30 flex items-center justify-center border-4 border-coursera-blue/20 shadow-lg">
                                    <i class="fas fa-user text-5xl text-coursera-blue dark:text-blue-300"></i>
                                </div>
                                <img src="" alt="Profile Picture" id="preview-img" class="hidden w-32 h-32 rounded-full object-cover border-4 border-coursera-blue/20 shadow-lg">
                            <?php endif; ?>
                            
                            <label class="absolute bottom-0 right-0 w-10 h-10 bg-coursera-blue hover:bg-coursera-blue-dark text-white rounded-full flex items-center justify-center shadow-lg cursor-pointer transition-colors border-2 border-white dark:border-gray-800">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="profile_picture" accept="image/*" class="hidden" onchange="previewImage(this)">
                            </label>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">Allowed: JPG, PNG, GIF, WebP (Max 2MB)</p>
                    </div>

                    <!-- Personal Info -->
                    <div class="space-y-6 border-t border-gray-100 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
                        
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" id="username" name="username"
                                    value="<?php echo htmlspecialchars($current_user['name'] ?? ''); ?>" required
                                    class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-coursera-blue transition-colors">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="email" id="email" name="email"
                                    value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" required
                                    class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-coursera-blue transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="space-y-6 border-t border-gray-100 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Security</h3>
                        
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl mb-4">
                            <p class="text-sm text-blue-700 dark:text-blue-300 flex items-start gap-2">
                                <i class="fas fa-info-circle mt-0.5"></i>
                                Leave the password fields blank if you don't want to change your password.
                            </p>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" id="password" name="password" placeholder="••••••••"
                                    class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-coursera-blue transition-colors">
                            </div>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                            <div class="relative">
                                <i class="fas fa-check-double absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••"
                                    class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-coursera-blue transition-colors">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-4">
                        <a href="dashboard.php" class="px-6 py-3 text-gray-600 dark:text-gray-400 font-medium hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">Cancel</a>
                        <button type="submit" class="bg-coursera-blue hover:bg-coursera-blue-dark text-white font-semibold py-3 px-8 rounded-xl transition-all shadow-md flex items-center gap-2">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('preview-img');
            const placeholder = document.getElementById('preview-placeholder');
            
            previewImg.src = e.target.result;
            previewImg.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include("../footer.php"); ?>