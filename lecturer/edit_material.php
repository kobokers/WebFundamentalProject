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
$material_id = isset($_GET['material_id']) ? (int)$_GET['material_id'] : null;

if (!$material_id) {
    $_SESSION['error'] = "Invalid material ID.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Fetch Material and Verify Ownership ---
$fetch_query = "
    SELECT 
        T1.id, T1.title, T1.content_type, T1.content_url, T1.material_order,
        T2.title AS module_title,
        T3.id AS course_id, T3.title AS course_title
    FROM 
        learning_materials AS T1
    INNER JOIN 
        modules AS T2 ON T1.module_id = T2.id
    INNER JOIN
        courses AS T3 ON T2.course_id = T3.id
    WHERE 
        T1.id = '$material_id' AND T3.lecturer_id = '$lecturer_id'";
        
$material_result = mysqli_query($conn, $fetch_query);

if (mysqli_num_rows($material_result) == 0) {
    $_SESSION['error'] = "Material not found or you do not have permission to edit it.";
    header("Location: dashboard.php");
    exit;
}

$material = mysqli_fetch_assoc($material_result);
$course_id = $material['course_id']; // Use for redirection
$module_title = htmlspecialchars($material['module_title']);
$course_title = htmlspecialchars($material['course_title']);


// --- 3. Handle Form Submission (Update Material) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $new_title = mysqli_real_escape_string($conn, $_POST['material_title']);
    $new_type = mysqli_real_escape_string($conn, $_POST['content_type']);
    $new_url = mysqli_real_escape_string($conn, $_POST['content_url'] ?? '');
    $new_order = (int)$_POST['material_order'];

    $update_query = "
        UPDATE learning_materials 
        SET 
            title = '$new_title',
            content_type = '$new_type',
            content_url = '$new_url',
            material_order = '$new_order'
        WHERE 
            id = '$material_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Material '{$new_title}' updated successfully.";
        header("Location: module_setup.php?course_id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update material: " . mysqli_error($conn);
    }
}

// Set variables for displaying the form's current values
$current_title = htmlspecialchars($material['title']);
$current_type = $material['content_type'];
$current_url = htmlspecialchars($material['content_url']);
$current_order = $material['material_order'];

// NOW include header after all redirects
include("../header.php");
?>

<body>
    <div class="container mx-auto p-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Edit Learning Material</h1>
            <p class="text-md text-gray-600 dark:text-gray-400">
                Course: <b><?php echo $course_title; ?></b> | Module: <b><?php echo $module_title; ?></b>
            </p>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="max-w-xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-xl transition-colors duration-200">
            <h2 class="text-xl font-semibold mb-4 text-blue-600 dark:text-blue-400">Update Material Details</h2>
            
            <form action="edit_material.php?material_id=<?php echo $material_id; ?>" method="POST">

                <div class="mb-4">
                    <label for="material_title" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Material Title:</label>
                    <input type="text" id="material_title" name="material_title" value="<?php echo $current_title; ?>" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="content_type" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Content Type:</label>
                        <select id="content_type" name="content_type" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="reading" <?php echo ($current_type == 'reading' ? 'selected' : ''); ?>>Reading/Document</option>
                            <option value="video" <?php echo ($current_type == 'video' ? 'selected' : ''); ?>>Video</option>
                            <option value="quiz" <?php echo ($current_type == 'quiz' ? 'selected' : ''); ?>>Quiz/Assessment</option>
                            <option value="link" <?php echo ($current_type == 'link' ? 'selected' : ''); ?>>External Link</option>
                        </select>
                    </div>
                    <div>
                        <label for="material_order" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Order:</label>
                        <input type="number" id="material_order" name="material_order" min="1" value="<?php echo $current_order; ?>" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="content_url" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">URL/Link:</label>
                    <input type="text" id="content_url" name="content_url" value="<?php echo $current_url; ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="e.g., https://www.youtube.com/...">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Provide the direct link to the content (Video, PDF, or website).</p>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                    Save Changes
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <a href="module_setup.php?course_id=<?php echo $course_id; ?>" 
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Back to Course Structure</a>
            </div>
        </div>

    </div>
</body>

<?php mysqli_close($conn); ?>
<?php include("../footer.php"); ?>