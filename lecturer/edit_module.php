<?php
session_start();
include("../connection.php"); 
include("../header.php");

// --- 1. Basic Validation ---
$module_id = isset($_GET['module_id']) ? $_GET['module_id'] : null;

if (!$module_id || !is_numeric($module_id)) {
    // Error handling
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

// --- 2. Handle Form Submission  ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_module'])) {
    
    $module_title = mysqli_real_escape_string($conn, $_POST['module_title']);
    $module_order = (int)$_POST['module_order'];
    $course_id = (int)$_POST['course_id'];

    $update_query = "UPDATE modules 
                     SET title = '$module_title', module_order = '$module_order' 
                     WHERE id = '$module_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Module updated successfully.";
        echo "<script>window.location.href = 'module_setup.php?course_id={$course_id}';</script>";
        exit;
    } else {
        $_SESSION['error'] = "Failed to update module: " . mysqli_error($conn);
    }
}

// --- 3. Fetch Existing Module Data (for display in form) ---
$fetch_query = "SELECT course_id, title, module_order FROM modules WHERE id = '$module_id'";
$fetch_result = mysqli_query($conn, $fetch_query);

if (mysqli_num_rows($fetch_result) == 0) {
    $_SESSION['error'] = "Module not found.";
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$module_data = mysqli_fetch_assoc($fetch_result);
$course_id = $module_data['course_id']; 
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-purple-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="module_setup.php?course_id=<?php echo $course_id; ?>" class="hover:text-purple-600 transition-colors">Course Modules</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 dark:text-white">Edit Module</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-edit text-purple-600"></i>
                Edit Module
            </h1>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="max-w-xl mx-auto">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <span class="text-red-700 dark:text-red-300"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-8">
                <form action="edit_module.php?module_id=<?php echo $module_id; ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                    <div>
                        <label for="module_title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Module Title</label>
                        <input type="text" id="module_title" name="module_title"
                            value="<?php echo htmlspecialchars($module_data['title']); ?>" required
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                    </div>

                    <div>
                        <label for="module_order" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Order/Sequence</label>
                        <input type="number" id="module_order" name="module_order"
                            value="<?php echo $module_data['module_order']; ?>" min="1" required
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Determines the position of this module in the course list.</p>
                    </div>

                    <div class="pt-4 flex gap-4">
                        <a href="module_setup.php?course_id=<?php echo $course_id; ?>"
                            class="flex-1 py-3 px-6 rounded-xl border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" name="update_module"
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