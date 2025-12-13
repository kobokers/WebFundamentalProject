<?php
session_start();
include("../connection.php");

// --- 1. Authentication and Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../auth/login.php");
    exit;
}

$lecturer_id = $_SESSION['user_id'];
$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course ID provided.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_course'])) {

    // Collect and minimally escape input
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $language = mysqli_real_escape_string($conn, $_POST['language']);
    $fee = (float)$_POST['fee'];
    $duration = isset($_POST['duration']) && $_POST['duration'] !== '' ? (int)$_POST['duration'] : null;
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // --- Image Upload Handling ---
    $image_update_sql = "";
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['course_image']['tmp_name'];
        $file_name = $_FILES['course_image']['name'];
        $file_size = $_FILES['course_image']['size'];
        $file_parts = explode('.', $file_name);
        $file_ext = strtolower(end($file_parts));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size <= 5 * 1024 * 1024) {
                // Fetch old image to delete later
                $old_img_query = "SELECT course_image FROM courses WHERE id = '$course_id'";
                $old_img_result = mysqli_query($conn, $old_img_query);
                $old_img_row = mysqli_fetch_assoc($old_img_result);
                $old_image = $old_img_row['course_image'] ?? null;

                $new_file_name = uniqid('course_') . '.' . $file_ext;
                $upload_dir = '../uploads/courses/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $image_update_sql = ", course_image = '$new_file_name'";
                    
                    // Delete old image if it exists
                    if ($old_image && file_exists($upload_dir . $old_image)) {
                        unlink($upload_dir . $old_image);
                    }
                }
            }
        }
    }

    // It verifies that the course belongs to the logged-in lecturer before updating.
    $duration_sql = $duration !== null ? $duration : 'NULL';
    $update_query = "UPDATE courses 
                     SET title = '$title', level = '$level', category = '$category', language = '$language', fee = '$fee', duration = $duration_sql, description = '$description' $image_update_sql
                     WHERE id = '$course_id' AND lecturer_id = '$lecturer_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Course details updated successfully.";
        header("Location: edit_course.php?course_id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update course: " . mysqli_error($conn);
    }
}

// --- 3. Fetch Existing Course Data (for display in form) ---
$fetch_query = "SELECT title, level, category, language, fee, duration, description, course_image FROM courses 
                WHERE id = '$course_id' AND lecturer_id = '$lecturer_id'";

$fetch_result = mysqli_query($conn, $fetch_query);

if (mysqli_num_rows($fetch_result) == 0) {
    $_SESSION['error'] = "Course not found or you do not have permission to edit it.";
    header("Location: dashboard.php");
    exit;
}

$course_data = mysqli_fetch_assoc($fetch_result);

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-purple-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="module_setup.php?course_id=<?php echo $course_id; ?>" class="hover:text-purple-600 transition-colors"><?php echo htmlspecialchars($course_data['title']); ?></a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 dark:text-white">Edit Details</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fas fa-edit text-purple-600"></i>
                    Edit Course Details
                </h1>
                <a href="module_setup.php?course_id=<?php echo $course_id; ?>" 
                   class="inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium px-4 py-2 rounded-xl transition-all">
                    <i class="fas fa-cubes"></i> Manage Modules
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="max-w-3xl mx-auto">
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
                <form action="edit_course.php?course_id=<?php echo $course_id; ?>" method="POST" class="space-y-6" enctype="multipart/form-data">
                    
                    <!-- Basic Info -->
                    <div class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2">Basic Information</h2>
                        
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Title</label>
                            <input type="text" id="title" name="title"
                                value="<?php echo htmlspecialchars($course_data['title']); ?>" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Description</label>
                            <textarea id="description" name="description" rows="5" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors"><?php echo htmlspecialchars($course_data['description']); ?></textarea>
                        </div>
                        </div>

                        <!-- Image Section -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Image</label>
                            
                            <?php if (!empty($course_data['course_image'])): ?>
                                <div class="mb-3">
                                    <p class="text-sm text-gray-500 mb-1">Current Image:</p>
                                    <img src="../uploads/courses/<?php echo htmlspecialchars($course_data['course_image']); ?>" 
                                         alt="Current Course Image" 
                                         class="h-32 w-48 object-cover rounded-xl border border-gray-200 dark:border-gray-600">
                                </div>
                            <?php endif; ?>

                            <label for="course_image" class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                                <?php echo !empty($course_data['course_image']) ? 'Update Image (Optional)' : 'Upload Image (Optional)'; ?>
                            </label>
                            <input type="file" id="course_image" name="course_image" accept="image/png, image/jpeg, image/gif"
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Recommended size: 800x600px. Max size: 5MB.</p>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="space-y-6 pt-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2">Category & Level</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="level" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Difficulty Level</label>
                                <select id="level" name="level" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors appearance-none">
                                    <?php
                                    $levels = ['Beginner', 'Intermediate', 'Advanced'];
                                    foreach ($levels as $level) {
                                        $selected = ($course_data['level'] == $level) ? 'selected' : '';
                                        echo "<option value=\"{$level}\" {$selected}>{$level}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category</label>
                                <select id="category" name="category" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors appearance-none">
                                    <?php
                                    $categories = ['General', 'Programming', 'Design', 'Business', 'Language', 'Science', 'Mathematics'];
                                    foreach ($categories as $cat) {
                                        $selected = ($course_data['category'] == $cat) ? 'selected' : '';
                                        echo "<option value=\"{$cat}\" {$selected}>{$cat}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="space-y-6 pt-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2">Additional Details</h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="language" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Language</label>
                                <input type="text" id="language" name="language"
                                    value="<?php echo htmlspecialchars($course_data['language']); ?>" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                            </div>

                            <div>
                                <label for="duration" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Duration (Hours)</label>
                                <input type="number" id="duration" name="duration" min="1" 
                                    value="<?php echo isset($course_data['duration']) ? htmlspecialchars($course_data['duration']) : ''; ?>"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                            </div>

                            <div>
                                <label for="fee" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Fee ($)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 text-lg">$</span>
                                    <input type="number" id="fee" name="fee" step="0.01" min="0" 
                                        value="<?php echo htmlspecialchars($course_data['fee']); ?>" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors font-mono text-lg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex gap-4">
                        <a href="dashboard.php" class="flex-1 py-3 px-6 rounded-xl border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" name="update_course"
                            class="flex-[2] bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
include("../footer.php"); 
?>