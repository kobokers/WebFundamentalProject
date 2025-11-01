<?php
session_start();
include("../header.php");
include("../connection.php"); 

// --- 1. Basic Validation ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
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
// Fetch course details, including the lecturer_id
// INSECURE Query (no prepared statement)
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
    // Check enrollment and payment status for students
    $enroll_query = "SELECT payment_status FROM enrollment 
                     WHERE user_id = '$user_id' AND course_id = '$course_id' LIMIT 1";
    $enroll_result = mysqli_query($conn, $enroll_query);
    
    if (mysqli_num_rows($enroll_result) > 0) {
        $enroll_data = mysqli_fetch_assoc($enroll_result);
        if ($enroll_data['payment_status'] === 'paid') {
            $has_paid = true;
        }
    }
    
    // DENY ACCESS if student hasn't paid
    if (!$has_paid) {
        $_SESSION['error'] = "You must complete payment to access this course.";
        header("Location: ../auth/payment.php?course_id={$course_id}");
        exit;
    }
}

// --- 3. Handle Progress Update (Mark Module Complete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_module_id'])) {
    
    $module_id = (int)$_POST['complete_module_id'];

    // INSECURE SQL Query to INSERT or UPDATE progress (uses REPLACE INTO for simplicity)
    // REPLACE INTO attempts to insert; if user_id and module_id exist (unique key), it updates.
    $progress_query = "REPLACE INTO progress (user_id, module_id, status, completion_date)
                       VALUES ('$user_id', '$module_id', 'completed', NOW())";
    
    if (mysqli_query($conn, $progress_query)) {
        $_SESSION['success'] = "Module marked as complete!";
    } else {
        $_SESSION['error'] = "Failed to update progress: " . mysqli_error($conn);
    }
    // Redirect to clear POST data
    header("Location: course_view.php?id={$course_id}");
    exit;
}

// --- 4. Fetch All Modules and Student Progress ---
// Retrieves all modules for the course AND the student's progress for each.
// INSECURE Query with JOIN
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
?>

<?php 
// Calculate overall progress for the display
$total_modules = mysqli_num_rows($modules_progress_result);
$completed_modules = 0;
// Need to reset the pointer to iterate again in the HTML, or store results in an array
$modules_list = mysqli_fetch_all($modules_progress_result, MYSQLI_ASSOC);
mysqli_data_seek($modules_progress_result, 0); // Reset pointer for HTML loop

foreach ($modules_list as $module) {
    if (isset($module['progress_status']) && $module['progress_status'] === 'completed') {
        $completed_modules++;
    }
}
$progress_percentage = ($total_modules > 0) ? round(($completed_modules / $total_modules) * 100) : 0;
?>

<body>
    <div class="container mx-auto p-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-blue-800"><?php echo $course_title; ?></h1>
            <p class="text-lg text-gray-600">Course Content and Progress Tracker</p>
        </header>

        <div class="bg-gray-100 p-4 rounded-lg shadow mb-8">
            <h2 class="text-xl font-semibold mb-2">Your Progress: <?php echo $progress_percentage; ?>% Complete</h2>
            <div class="w-full bg-gray-300 rounded-full h-4">
                <div class="bg-green-600 h-4 rounded-full transition-all duration-500" style="width: <?php echo $progress_percentage; ?>%"></div>
            </div>
            <p class="text-sm mt-2 text-gray-700"><?php echo $completed_modules; ?> of <?php echo $total_modules; ?> modules completed.</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <section id="course-modules" class="space-y-4">
            <h2 class="text-2xl font-bold mb-4">Course Modules</h2>
            
            <?php foreach ($modules_list as $module): ?>
                <?php $is_completed = (isset($module['progress_status']) && $module['progress_status'] === 'completed'); ?>
                <div class="flex items-center justify-between p-5 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <span class="text-xl font-bold mr-4 text-blue-600"><?php echo $module['module_order']; ?>.</span>
                        <h3 class="text-lg font-medium <?php echo $is_completed ? 'text-gray-500 line-through' : 'text-gray-900'; ?>">
                            <?php echo htmlspecialchars($module['module_title']); ?>
                            </h3>
                    </div>
                    
                    <div>
                        <?php if ($is_completed): ?>
                            <span class="py-1 px-3 text-xs font-semibold bg-green-100 text-green-600 rounded-full flex items-center">
                                <i class="fas fa-check-circle mr-1"></i> Completed
                            </span>
                        <?php else: ?>
                            <form action="course_view.php?id=<?php echo $course_id; ?>" method="POST" class="inline">
                                <input type="hidden" name="complete_module_id" value="<?php echo $module['module_id']; ?>">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 text-xs font-semibold rounded transition">
                                    Mark Complete
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($total_modules == 0): ?>
                 <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 rounded-lg">
                    This course has no modules yet. Please check back later!
                </div>
            <?php endif; ?>
        </section>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>

<?php mysqli_close($conn); ?>