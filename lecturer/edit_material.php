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
        $_SESSION['success'] = "Material updated successfully.";
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

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-purple-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="module_setup.php?course_id=<?php echo $course_id; ?>" class="hover:text-purple-600 transition-colors">Modules</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 dark:text-white">Edit Material</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-pen-nib text-purple-600"></i>
                Edit Learning Material
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Course: <span class="font-medium text-gray-900 dark:text-white"><?php echo $course_title; ?></span> • 
                Module: <span class="font-medium text-gray-900 dark:text-white"><?php echo $module_title; ?></span>
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="max-w-2xl mx-auto">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="text-red-700 dark:text-red-300"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-8">
                <form action="edit_material.php?material_id=<?php echo $material_id; ?>" method="POST" class="space-y-6">

                    <div>
                        <label for="material_title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Material Title</label>
                        <input type="text" id="material_title" name="material_title" value="<?php echo $current_title; ?>" required
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="content_type" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Content Type</label>
                            <select id="content_type" name="content_type" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors appearance-none">
                                <option value="reading" <?php echo ($current_type == 'reading' ? 'selected' : ''); ?>>Reading/Document</option>
                                <option value="video" <?php echo ($current_type == 'video' ? 'selected' : ''); ?>>Video</option>
                                <option value="link" <?php echo ($current_type == 'link' ? 'selected' : ''); ?>>External Link</option>
                            </select>
                        </div>
                        <div>
                            <label for="material_order" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Order</label>
                            <input type="number" id="material_order" name="material_order" min="1" value="<?php echo $current_order; ?>" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="content_url" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">URL/Link</label>
                        <input type="text" id="content_url" name="content_url" value="<?php echo $current_url; ?>"
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors"
                            placeholder="e.g., https://www.youtube.com/...">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                            <i class="fas fa-info-circle"></i> Provide the direct link to the content (Video, PDF, or website).
                        </p>
                    </div>

                    <div class="pt-4 flex gap-4">
                        <a href="module_setup.php?course_id=<?php echo $course_id; ?>" 
                            class="flex-1 py-3 px-6 rounded-xl border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
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