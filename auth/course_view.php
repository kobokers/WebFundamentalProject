<?php
session_start();
include("../connection.php"); // Database connection only - NO HTML output yet

// --- 1. Basic Validation ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$course_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course selected.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Security: Check Access (Lecturers/Admins bypass payment check) ---
$course_query = "SELECT title, lecturer_id FROM courses WHERE id = '$course_id'";
$course_result = mysqli_query($conn, $course_query);

if (mysqli_num_rows($course_result) == 0) {
    $_SESSION['error'] = "Course does not exist.";
    header("Location: dashboard.php");
    exit;
}
$course_data = mysqli_fetch_assoc($course_result);
$course_title = htmlspecialchars($course_data['title']);
$lecturer_id = $course_data['lecturer_id'];

$is_lecturer = ($user_role === 'lecturer' && $user_id == $lecturer_id);
$has_paid = false;

if (!$is_lecturer) {
    $enroll_query = "SELECT payment_status FROM enrollment 
                     WHERE user_id = '$user_id' AND course_id = '$course_id' LIMIT 1";
    $enroll_result = mysqli_query($conn, $enroll_query);

    if (mysqli_num_rows($enroll_result) > 0) {
        $enroll_data = mysqli_fetch_assoc($enroll_result);
        if ($enroll_data['payment_status'] === 'paid') {
            $has_paid = true;
        }
    }

    if (!$has_paid) {
        $_SESSION['error'] = "You must complete payment to access this course.";
        header("Location: payment.php?course_id={$course_id}");
        exit;
    }
}

// --- 3. Handle Progress Update (Mark Module Complete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_module_id'])) {

    $module_id = (int)$_POST['complete_module_id'];

    $progress_query = "REPLACE INTO progress (user_id, module_id, status, completion_date)
                        VALUES ('$user_id', '$module_id', 'completed', NOW())";

    if (mysqli_query($conn, $progress_query)) {
        $_SESSION['success'] = "Module marked as complete!";
    } else {
        $_SESSION['error'] = "Failed to update progress: " . mysqli_error($conn);
    }
    header("Location: course_view.php?id={$course_id}");
    exit;
}

// --- 4. Fetch All Modules, Student Progress, and Materials ---
$modules_progress_query = "
    SELECT 
        m.id AS module_id, 
        m.title AS module_title, 
        m.module_order, 
        p.status AS progress_status
    FROM 
        modules m
    LEFT JOIN 
        progress p ON m.id = p.module_id AND p.user_id = '$user_id'
    WHERE 
        m.course_id = '$course_id'
    ORDER BY 
        m.module_order ASC";

$modules_progress_result = mysqli_query($conn, $modules_progress_query);

$total_modules = 0;
$completed_modules = 0;
$modules_list_assoc = [];
$module_ids = [];

// 1. Process Modules List (Build the primary, ordered list and collect IDs)
while ($module = mysqli_fetch_assoc($modules_progress_result)) {
    $total_modules++;
    $module_ids[] = $module['module_id'];

    if (isset($module['progress_status']) && $module['progress_status'] === 'completed') {
        $completed_modules++;
    }
    
    // Initialize materials array and use module_id as key for integration
    $module['materials'] = []; 
    $modules_list_assoc[$module['module_id']] = $module; 
}

// 2. Fetch all materials for these modules
if (!empty($module_ids)) {
    $module_ids_str = implode(',', $module_ids);
    
    $materials_query = "
        SELECT 
            id, module_id, title, content_type, content_url, material_order
        FROM 
            learning_materials
        WHERE 
            module_id IN ($module_ids_str)
        ORDER BY 
            material_order ASC";

    $materials_result = mysqli_query($conn, $materials_query);

    // 3. Organize and Integrate materials into the modules_list
    while ($material = mysqli_fetch_assoc($materials_result)) {
        $m_id = $material['module_id'];
        
        // Add the material to the correct module using the associative key
        if (isset($modules_list_assoc[$m_id])) {
            $modules_list_assoc[$m_id]['materials'][] = $material;
        }
    }
}

// Re-index $modules_list numerically for the final 'foreach' loop
$modules_list = array_values($modules_list_assoc); 

$progress_percentage = ($total_modules > 0) ? round(($completed_modules / $total_modules) * 100) : 0;

// NOW include header.php AFTER all redirects
include("../header.php");
?>

<div class="container mx-auto p-6 max-w-5xl">
    <!-- Course Header -->
    <div class="mb-8">
        <h1 class="text-3xl mb-3 font-bold text-gray-800 dark:text-gray-100"><?php echo $course_title; ?></h1>
        <a href="dashboard.php" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mb-3 inline-block">
            ← Back to Dashboard
        </a>
        
        <!-- Progress Bar -->
        <div class="mt-4">
            <div class="flex justify-between mb-1">
                <span class="text-base font-medium text-blue-700 dark:text-blue-400">Course Progress</span>
                <span class="text-sm font-medium text-blue-700 dark:text-blue-400"><?php echo $progress_percentage; ?>%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $progress_percentage; ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Modules List -->
    <div class="space-y-4">
        <?php foreach ($modules_list as $module): ?>
            <div class="border dark:border-gray-700 rounded-lg shadow-sm bg-white dark:bg-gray-800 overflow-hidden">
                <!-- Module Header -->
                <div class="module-header p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer flex justify-between items-center transition" data-module-id="<?php echo $module['module_id']; ?>">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Module <?php echo $module['module_order']; ?>: <?php echo htmlspecialchars($module['module_title']); ?>
                    </h3>
                    <div class="flex items-center">
                        <?php if (isset($module['progress_status']) && $module['progress_status'] === 'completed'): ?>
                            <span class="text-green-600 dark:text-green-400 mr-3"><i class="fas fa-check-circle"></i> Completed</span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down module-chevron transition-transform duration-300 text-gray-500 dark:text-gray-400"></i>
                    </div>
                </div>

                <!-- Module Content (Hidden by default) -->
                <div id="content-<?php echo $module['module_id']; ?>" class="hidden p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-800">
                    <?php if (!empty($module['materials'])): ?>
                        <ul class="space-y-3">
                            <?php foreach ($module['materials'] as $material): ?>
                                <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <a href="view_material.php?material_id=<?php echo $material['id']; ?>" class="flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                                        <i class="fas fa-file-alt mr-3"></i>
                                        <?php echo htmlspecialchars($material['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500 dark:text-gray-400 italic">No materials in this module yet.</p>
                    <?php endif; ?>

                    <!-- Mark Complete Button -->
                    <?php if (!isset($module['progress_status']) || $module['progress_status'] !== 'completed'): ?>
                        <form method="POST" class="mt-4 text-right">
                            <input type="hidden" name="complete_module_id" value="<?php echo $module['module_id']; ?>">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm shadow">
                                Mark Module as Complete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const moduleHeaders = document.querySelectorAll('.module-header');

        moduleHeaders.forEach(header => {
            header.addEventListener('click', function (e) {
                // Prevent toggle when clicking the form button or links
                if (e.target.closest('form') || e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                
                const moduleId = this.getAttribute('data-module-id');
                const content = document.getElementById(`content-${moduleId}`);
                const icon = this.querySelector('.module-chevron');

                // Toggle visibility
                content.classList.toggle('hidden');

                // Toggle icon rotation
                icon.classList.toggle('rotate-180');
            });
        });
    });
</script>

<?php include("../footer.php"); ?>