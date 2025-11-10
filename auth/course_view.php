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

<body>
    <div class="container mx-auto p-8 max-w-4xl">

        <header class="mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-2">
                <h1 class="text-4xl font-extrabold text-blue-800"><?php echo $course_title; ?></h1>

                <a href="course_discussion.php?course_id=<?php echo $course_id; ?>"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center whitespace-nowrap">
                    <i class="fas fa-comments mr-2"></i>Go to Discussion
                </a>
            </div>
            <p class="text-lg text-gray-600">Course Content and Progress Tracker</p>
        </header>
        
        <div class="bg-gray-100 p-4 rounded-lg shadow mb-8">
            <h2 class="text-xl font-semibold mb-2">Your Progress: <?php echo $progress_percentage; ?>% Complete</h2>
            <div class="w-full bg-gray-300 rounded-full h-4">
                <div class="bg-green-600 h-4 rounded-full transition-all duration-500"
                    style="width: <?php echo $progress_percentage; ?>%"></div>
            </div>
            <p class="text-sm mt-2 text-gray-700"><?php echo $completed_modules; ?> of <?php echo $total_modules; ?>
                modules completed.</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <section id="course-modules" class="space-y-4">
            <h2 class="text-2xl font-bold mb-4">Course Curriculum</h2>

            <?php foreach ($modules_list as $module): ?>
            <?php $is_completed = (isset($module['progress_status']) && $module['progress_status'] === 'completed'); ?>
            
            <div class="bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden">
                
                <!-- Module Header - Clickable to expand/collapse -->
                <div class="module-header flex flex-col sm:flex-row sm:items-center sm:justify-between w-full p-5 transition duration-200 cursor-pointer
                    <?php echo $is_completed ? 'bg-green-50' : 'bg-gray-50 hover:bg-gray-100'; ?>"
                    data-module-id="<?php echo $module['module_id']; ?>">
                    
                    <!-- Left side: Module number and title -->
                    <div class="flex items-center mb-3 sm:mb-0 flex-1 min-w-0">
                        <span class="text-xl font-bold mr-3 sm:mr-4 text-blue-600 shrink-0"><?php echo $module['module_order']; ?>.</span>
                        <h3 class="text-lg font-semibold <?php echo $is_completed ? 'text-gray-500 line-through' : 'text-gray-900'; ?>">
                            <?php echo htmlspecialchars($module['module_title']); ?>
                        </h3>
                    </div>

                    <!-- Right side: Status badge and chevron -->
                    <div class="flex items-center gap-3">
                        <?php if ($is_completed): ?>
                        <span class="py-1 px-3 text-xs font-semibold bg-green-200 text-green-800 rounded-full flex items-center whitespace-nowrap">
                            <i class="fas fa-check-circle mr-1"></i> Completed
                        </span>
                        <?php else: ?>
                        <form action="course_view.php?id=<?php echo $course_id; ?>" method="POST" 
                            class="inline" 
                            onclick="event.stopPropagation();">
                            <input type="hidden" name="complete_module_id" value="<?php echo $module['module_id']; ?>">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 text-xs font-semibold rounded transition whitespace-nowrap">
                                Mark Complete
                            </button>
                        </form>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform transform module-chevron"></i>
                    </div>
                </div>

                <!-- Module Content - Hidden by default -->
                <div id="content-<?php echo $module['module_id']; ?>" class="module-content hidden border-t p-5 bg-white">
                    <h4 class="text-md font-bold mb-3 text-gray-700">Learning Materials:</h4>
                    
                    <?php if (!empty($module['materials'])): ?>
                    <ul class="space-y-3 pl-4">
                        <?php foreach ($module['materials'] as $material): ?>
                        <?php
                            $icon = '';
                            switch ($material['content_type']) {
                                case 'video': $icon = 'fas fa-video text-red-500'; break;
                                case 'reading': $icon = 'fas fa-file-alt text-blue-500'; break;
                                case 'quiz': $icon = 'fas fa-clipboard-list text-purple-500'; break;
                                case 'link': default: $icon = 'fas fa-external-link-alt text-gray-500'; break;
                            }
                        ?>
                        <li class="flex items-start">
                            <i class="<?php echo $icon; ?> mt-1 mr-3 text-sm shrink-0"></i>
                            <div class="text-gray-700 text-base">
                                <span class="font-semibold mr-1"><?php echo $material['material_order']; ?>.</span>
                                
                                <a href="view_material.php?material_id=<?php echo $material['id']; ?>"
                                    class="text-blue-700 hover:text-blue-900 hover:underline transition">
                                    <?php echo htmlspecialchars($material['title']); ?> 
                                    (<?php echo ucfirst($material['content_type']); ?>)
                                
                                    <?php if ($material['content_type'] != 'quiz'): ?>
                                        <i class="fas fa-external-link-square-alt text-xs ml-1 text-gray-400"></i>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm italic">No learning materials have been added to this module yet.</p>
                    <?php endif; ?>
                </div>
            
            </div>
            <?php endforeach; ?>

            <?php if ($total_modules == 0): ?>
            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 rounded-lg">
                This course has no modules yet. Please check back later.
            </div>
            <?php endif; ?>
        </section>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const moduleHeaders = document.querySelectorAll('.module-header');

        moduleHeaders.forEach(header => {
            header.addEventListener('click', function (e) {
                // Prevent toggle when clicking the form button
                if (e.target.closest('form') || e.target.closest('button')) {
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