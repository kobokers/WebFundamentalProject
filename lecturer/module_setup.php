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

// Get required IDs
$lecturer_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course ID provided.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Verify Lecturer Owns the Course  ---
$verify_query = "SELECT title FROM courses WHERE id = '$course_id' AND lecturer_id = '$lecturer_id'";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Course not found or you do not have permission to edit it.";
    header("Location: dashboard.php");
    exit;
}

$course = mysqli_fetch_assoc($verify_result);
$course_title = htmlspecialchars($course['title']);

// --- 3. Handle Form Submission (Add New Module) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['module_title'])) {
    
    $module_title = mysqli_real_escape_string($conn, $_POST['module_title']);
    $module_order = (int)$_POST['module_order'];

    $insert_query = "INSERT INTO modules (course_id, title, module_order) 
                     VALUES ('$course_id', '$module_title', '$module_order')";

    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "Module '{$module_title}' added successfully.";
        // Redirect to clear POST data
        header("Location: module_setup.php?course_id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add module: " . mysqli_error($conn);
    }
}

// --- 4. Fetch Existing Modules ---
$modules_query = "SELECT id, title, module_order FROM modules WHERE course_id = '$course_id' ORDER BY module_order ASC";
$modules_result = mysqli_query($conn, $modules_query);

?>

<body>
    <div class="container mx-auto p-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Module Management: <?php echo $course_title; ?></h1>
            <p class="text-md text-gray-600">Define the lessons and structure of your course.</p>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-1 p-6 bg-white rounded-lg shadow-xl h-fit">
                <h2 class="text-xl font-semibold mb-4 text-blue-600">Add New Module</h2>
                <form action="module_setup.php?course_id=<?php echo $course_id; ?>" method="POST">

                    <div class="mb-4">
                        <label for="module_title" class="block text-gray-700 font-semibold mb-2">Module Title:</label>
                        <input type="text" id="module_title" name="module_title" required
                            class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <div class="mb-6">
                        <label for="module_order" class="block text-gray-700 font-semibold mb-2">Order/Sequence (e.g.,
                            1, 2, 3):</label>
                        <input type="number" id="module_order" name="module_order" min="1" required
                            class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                        Save Module
                    </button>
                </form>
            </div>

            <div class="md:col-span-2">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Existing Modules List</h2>
                <?php if (mysqli_num_rows($modules_result) > 0): ?>
                <ul class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($modules_result)): ?>
                    <li class="flex justify-between items-center p-4 bg-gray-50 border rounded-lg shadow-sm">
                        <span class="text-lg font-medium">
                            <?php echo $row['module_order']; ?>. <?php echo htmlspecialchars($row['title']); ?>
                        </span>
                        <div class="space-x-2">
                            <a href="edit_module.php?module_id=<?php echo $row['id']; ?>"
                                class="text-sm text-purple-600 hover:text-purple-800">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_module.php?module_id=<?php echo $row['id']; ?>&course_id=<?php echo $course_id; ?>"
                                class="text-sm text-red-600 hover:text-red-800"
                                onclick="return confirm('Are you sure you want to delete this module?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php else: ?>
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 rounded-lg">
                    No modules defined yet. Start adding the first one!
                </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>

<?php mysqli_close($conn); ?>
<?php include("../footer.php"); ?>