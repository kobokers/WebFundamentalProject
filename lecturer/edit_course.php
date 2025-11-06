<?php
session_start();
include("../header.php");
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
    $language = mysqli_real_escape_string($conn, $_POST['language']);
    $fee = (float)$_POST['fee'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // It verifies that the course belongs to the logged-in lecturer before updating.
    $update_query = "UPDATE courses 
                     SET title = '$title', level = '$level', language = '$language', fee = '$fee', description = '$description'
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
$fetch_query = "SELECT title, level, language, fee, description FROM courses 
                WHERE id = '$course_id' AND lecturer_id = '$lecturer_id'";

$fetch_result = mysqli_query($conn, $fetch_query);

if (mysqli_num_rows($fetch_result) == 0) {
    $_SESSION['error'] = "Course not found or you do not have permission to edit it.";
    header("Location: dashboard.php");
    exit;
}

$course_data = mysqli_fetch_assoc($fetch_result);
?>

<body>
    <div class="container mx-auto p-8 max-w-2xl">
        <header class="mb-6 text-center">
            <h1 class="text-3xl font-bold text-gray-800">Edit Course Details:
                <?php echo htmlspecialchars($course_data['title']); ?></h1>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success'];
                                                                                                unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded"><?php echo $_SESSION['error'];
                                                                                        unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <div class="p-8 bg-white rounded-xl shadow-2xl border border-gray-100">
            <form action="edit_course.php?course_id=<?php echo $course_id; ?>" method="POST">

                <div class="mb-5">
                    <label for="title" class="block text-gray-700 font-semibold mb-2">Course Title:</label>
                    <input type="text" id="title" name="title"
                        value="<?php echo htmlspecialchars($course_data['title']); ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="level" class="block text-gray-700 font-semibold mb-2">Difficulty Level:</label>
                        <select id="level" name="level" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        <label for="language" class="block text-gray-700 font-semibold mb-2">Language:</label>
                        <input type="text" id="language" name="language"
                            value="<?php echo htmlspecialchars($course_data['language']); ?>" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-5">
                    <label for="description" class="block text-gray-700 font-semibold mb-2">Course Description:</label>
                    <textarea id="description" name="description" rows="4" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"><?php echo htmlspecialchars($course_data['description']); ?></textarea>
                </div>

                <div class="mb-6">
                    <label for="fee" class="block text-gray-700 font-semibold mb-2">Course Fee ($):</label>
                    <input value="<?php echo htmlspecialchars($course_data['fee']); ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg">
                </div>

                <button type="submit" name="update_course"
                    class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200 text-xl shadow-lg">
                    <i class="fas fa-save mr-2"></i> Save Course Details
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>

<?php mysqli_close($conn); ?>
<?php include("../footer.php"); ?>