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

    // It verifies that the course belongs to the logged-in lecturer before updating.
    $duration_sql = $duration !== null ? $duration : 'NULL';
    $update_query = "UPDATE courses 
                     SET title = '$title', level = '$level', category = '$category', language = '$language', fee = '$fee', duration = $duration_sql, description = '$description'
                     WHERE id = '$course_id' AND lecturer_id = '$lecturer_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Course details updated successfully.";
        // Redirect to clear POST data
        header("Location: edit_course.php?course_id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update course: " . mysqli_error($conn);
    }
}

// --- 3. Fetch Existing Course Data (for display in form) ---
$fetch_query = "SELECT title, level, category, language, fee, duration, description FROM courses 
                WHERE id = '$course_id' AND lecturer_id = '$lecturer_id'";

$fetch_result = mysqli_query($conn, $fetch_query);

if (mysqli_num_rows($fetch_result) == 0) {
    $_SESSION['error'] = "Course not found or you do not have permission to edit it.";
    header("Location: dashboard.php");
    exit;
}

$course_data = mysqli_fetch_assoc($fetch_result);

// NOW include header after all redirects
include("../header.php");
?>

<body>
    <div class="container mx-auto p-8">
        <header class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-blue-800 dark:text-blue-300">Edit Course Details</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Update the details for: <?php echo htmlspecialchars($course_data['title']); ?></p>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="max-w-xl mx-auto p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="max-w-xl mx-auto p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <div class="max-w-xl mx-auto mt-6 p-8 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-200">
            <form action="edit_course.php?course_id=<?php echo $course_id; ?>" method="POST">

                <div class="mb-5">
                    <label for="title" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Course Title:</label>
                    <input type="text" id="title" name="title"
                        value="<?php echo htmlspecialchars($course_data['title']); ?>" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Advanced JavaScript Frameworks">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="level" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Difficulty Level:</label>
                        <select id="level" name="level" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
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
                        <label for="category" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Category:</label>
                        <select id="category" name="category" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <?php
                            $categories = ['General', 'Programming', 'Design', 'Business', 'Language', 'Science', 'Mathematics'];
                            foreach ($categories as $cat) {
                                $selected = (isset($course_data['category']) && $course_data['category'] == $cat) ? 'selected' : '';
                                echo "<option value=\"{$cat}\" {$selected}>{$cat}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="language" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Language:</label>
                    <input type="text" id="language" name="language"
                        value="<?php echo htmlspecialchars($course_data['language']); ?>" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., English">
                </div>

                <div class="mb-5">
                    <label for="description" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Course Description:</label>
                    <textarea id="description" name="description" rows="4" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white"
                        placeholder="Provide a brief overview of the course content and objectives."><?php echo htmlspecialchars($course_data['description']); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="fee" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Course Fee ($):</label>
                        <input type="number" id="fee" name="fee" step="0.01" min="0" value="<?php echo htmlspecialchars($course_data['fee']); ?>" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter 0.00 for a free course.</p>
                    </div>
                    <div>
                        <label for="duration" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Duration (hours):</label>
                        <input type="number" id="duration" name="duration" min="1" value="<?php echo isset($course_data['duration']) ? htmlspecialchars($course_data['duration']) : ''; ?>"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white"
                            placeholder="e.g., 10">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Estimated time to complete.</p>
                    </div>
                </div>

                <button type="submit" name="update_course"
                    class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200 text-xl shadow-lg">
                    <i class="fas fa-save mr-2"></i> Save Course Details
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>

<?php mysqli_close($conn); ?>
<?php include("../footer.php"); ?>