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

// --- 3b. Handle Rating Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
    $rating_value = (int)$_POST['rating'];
    $review_text = mysqli_real_escape_string($conn, $_POST['review'] ?? '');
    
    // Validate rating
    if ($rating_value < 1 || $rating_value > 5) {
        $_SESSION['error'] = "Please select a rating between 1 and 5 stars.";
    } else {
        // Use INSERT ON DUPLICATE KEY UPDATE to handle both new and existing ratings
        $rating_query = "INSERT INTO course_ratings (course_id, user_id, rating, review, created_at) 
                         VALUES ('$course_id', '$user_id', '$rating_value', '$review_text', NOW())
                         ON DUPLICATE KEY UPDATE rating = '$rating_value', review = '$review_text', created_at = NOW()";
        
        if (mysqli_query($conn, $rating_query)) {
            $_SESSION['success'] = "Thank you for your rating!";
        } else {
            $_SESSION['error'] = "Failed to submit rating: " . mysqli_error($conn);
        }
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
    
    // 4. Fetch quizzes for these modules and user's best attempt
    $quizzes_query = "
        SELECT 
            q.id as quiz_id,
            q.module_id,
            q.title as quiz_title,
            q.passing_score,
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
            (SELECT MAX(score) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = '$user_id') as best_score,
            (SELECT MAX(passed) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = '$user_id') as has_passed
        FROM 
            quizzes q
        WHERE 
            q.module_id IN ($module_ids_str)";
    
    $quizzes_result = mysqli_query($conn, $quizzes_query);
    
    // Add quiz info to each module
    while ($quiz = mysqli_fetch_assoc($quizzes_result)) {
        $m_id = $quiz['module_id'];
        if (isset($modules_list_assoc[$m_id])) {
            $modules_list_assoc[$m_id]['quiz'] = $quiz;
        }
    }
}

// Re-index $modules_list numerically for the final 'foreach' loop
$modules_list = array_values($modules_list_assoc); 

$progress_percentage = ($total_modules > 0) ? round(($completed_modules / $total_modules) * 100) : 0;

// --- 5. Fetch User's Existing Rating (if any) ---
$existing_rating = null;
$existing_review = '';
if ($user_role === 'student') {
    $user_rating_query = "SELECT rating, review FROM course_ratings WHERE course_id = '$course_id' AND user_id = '$user_id' LIMIT 1";
    $user_rating_result = mysqli_query($conn, $user_rating_query);
    if (mysqli_num_rows($user_rating_result) > 0) {
        $user_rating_data = mysqli_fetch_assoc($user_rating_result);
        $existing_rating = $user_rating_data['rating'];
        $existing_review = $user_rating_data['review'];
    }
}

// --- 6. Fetch Course Average Rating ---
$avg_rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count FROM course_ratings WHERE course_id = '$course_id'";
$avg_rating_result = mysqli_query($conn, $avg_rating_query);
$avg_rating_data = mysqli_fetch_assoc($avg_rating_result);
$avg_rating = round($avg_rating_data['avg_rating'], 1);
$rating_count = $avg_rating_data['rating_count'];

// NOW include header.php AFTER all redirects
include("../header.php");
?>

<div class="container mx-auto p-6 max-w-5xl">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Course Header -->
    <div class="mb-8">
        <h1 class="text-3xl mb-3 font-bold text-gray-800 dark:text-gray-100"><?php echo $course_title; ?></h1>
        <a href="dashboard.php" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mb-3 inline-block">
            ← Back to Dashboard
        </a>
        
        <!-- Course Rating Display -->
        <div class="flex items-center mt-2 mb-4">
            <div class="flex text-yellow-400">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= floor($avg_rating)): ?>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    <?php else: ?>
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                <?php echo $avg_rating > 0 ? $avg_rating . ' out of 5' : 'No ratings yet'; ?>
                <?php if ($rating_count > 0): ?>
                    <span class="text-gray-400">(<?php echo $rating_count; ?> rating<?php echo $rating_count > 1 ? 's' : ''; ?>)</span>
                <?php endif; ?>
            </span>
        </div>
        
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

                    <!-- Quiz Section -->
                    <?php if (isset($module['quiz'])): ?>
                        <div class="mt-4 p-4 bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-700 rounded-lg">
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <div>
                                    <h4 class="font-semibold text-purple-800 dark:text-purple-300">
                                        <i class="fas fa-clipboard-check mr-2"></i><?php echo htmlspecialchars($module['quiz']['quiz_title']); ?>
                                    </h4>
                                    <p class="text-sm text-purple-600 dark:text-purple-400 mt-1">
                                        <?php echo $module['quiz']['question_count']; ?> Questions • Passing Score: <?php echo $module['quiz']['passing_score']; ?>%
                                    </p>
                                    <?php if ($module['quiz']['best_score'] !== null): ?>
                                        <p class="text-sm mt-1 <?php echo $module['quiz']['has_passed'] ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'; ?>">
                                            <?php if ($module['quiz']['has_passed']): ?>
                                                <i class="fas fa-check-circle mr-1"></i>Passed! Best Score: <?php echo $module['quiz']['best_score']; ?>%
                                            <?php else: ?>
                                                <i class="fas fa-clock mr-1"></i>Best Score: <?php echo $module['quiz']['best_score']; ?>% (Not Passed Yet)
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <a href="take_quiz.php?quiz_id=<?php echo $module['quiz']['quiz_id']; ?>" 
                                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition text-sm shadow">
                                    <i class="fas fa-play mr-2"></i>
                                    <?php echo ($module['quiz']['best_score'] !== null) ? 'Retake Quiz' : 'Take Quiz'; ?>
                                </a>
                            </div>
                        </div>
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

    <!-- Rating Section (Students Only) -->
    <?php if ($user_role === 'student'): ?>
    <div class="mt-10 p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg transition-colors duration-200">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-star mr-2 text-yellow-400"></i>
            <?php echo $existing_rating ? 'Update Your Rating' : 'Rate This Course'; ?>
        </h2>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="submit_rating" value="1">
            
            <!-- Star Rating Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Rating</label>
                <div class="flex space-x-1" id="star-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden peer" <?php echo ($existing_rating == $i) ? 'checked' : ''; ?>>
                            <svg class="w-10 h-10 transition-colors duration-150 star-icon
                                        <?php echo ($existing_rating && $i <= $existing_rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'; ?>
                                        hover:text-yellow-400 peer-checked:text-yellow-400" 
                                 fill="currentColor" viewBox="0 0 20 20" data-star="<?php echo $i; ?>">
                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                            </svg>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Review Textarea -->
            <div>
                <label for="review" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Review (Optional)
                </label>
                <textarea name="review" id="review" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                          placeholder="Share your thoughts about this course..."><?php echo htmlspecialchars($existing_review); ?></textarea>
            </div>
            
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200 shadow">
                <i class="fas fa-paper-plane mr-2"></i>
                <?php echo $existing_rating ? 'Update Rating' : 'Submit Rating'; ?>
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Module accordion toggle
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

        // Star rating hover effect
        const starContainer = document.getElementById('star-rating');
        if (starContainer) {
            const stars = starContainer.querySelectorAll('.star-icon');
            const radioInputs = starContainer.querySelectorAll('input[type="radio"]');
            
            stars.forEach((star, index) => {
                star.addEventListener('mouseenter', () => {
                    // Highlight all stars up to this one
                    stars.forEach((s, i) => {
                        if (i <= index) {
                            s.classList.add('text-yellow-400');
                            s.classList.remove('text-gray-300', 'dark:text-gray-600');
                        } else {
                            s.classList.remove('text-yellow-400');
                            s.classList.add('text-gray-300', 'dark:text-gray-600');
                        }
                    });
                });
                
                star.addEventListener('click', () => {
                    // Check the corresponding radio
                    radioInputs[index].checked = true;
                });
            });
            
            starContainer.addEventListener('mouseleave', () => {
                // Reset to selected state
                let selectedIndex = -1;
                radioInputs.forEach((radio, i) => {
                    if (radio.checked) selectedIndex = i;
                });
                
                stars.forEach((s, i) => {
                    if (i <= selectedIndex) {
                        s.classList.add('text-yellow-400');
                        s.classList.remove('text-gray-300', 'dark:text-gray-600');
                    } else {
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-300', 'dark:text-gray-600');
                    }
                });
            });
        }
    });
</script>
</main>
<?php include("../footer.php"); ?>