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
$course_query = "SELECT title, lecturer_id, description, category, level, duration FROM courses WHERE id = '$course_id'";
$course_result = mysqli_query($conn, $course_query);

if (mysqli_num_rows($course_result) == 0) {
    $_SESSION['error'] = "Course does not exist.";
    header("Location: dashboard.php");
    exit;
}
$course_data = mysqli_fetch_assoc($course_result);
$course_title = htmlspecialchars($course_data['title']);
$course_description = htmlspecialchars($course_data['description'] ?? '');
$course_category = htmlspecialchars($course_data['category'] ?? '');
$course_level = htmlspecialchars($course_data['level'] ?? '');
$course_duration = $course_data['duration'] ?? '';
$lecturer_id = $course_data['lecturer_id'];

// Fetch lecturer name
$lecturer_query = "SELECT name FROM users WHERE id = '$lecturer_id'";
$lecturer_result = mysqli_query($conn, $lecturer_query);
$lecturer_data = mysqli_fetch_assoc($lecturer_result);
$lecturer_name = htmlspecialchars($lecturer_data['name'] ?? 'Unknown');

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
    
    if ($rating_value < 1 || $rating_value > 5) {
        $_SESSION['error'] = "Please select a rating between 1 and 5 stars.";
    } else {
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

while ($module = mysqli_fetch_assoc($modules_progress_result)) {
    $total_modules++;
    $module_ids[] = $module['module_id'];

    if (isset($module['progress_status']) && $module['progress_status'] === 'completed') {
        $completed_modules++;
    }
    
    $module['materials'] = []; 
    $modules_list_assoc[$module['module_id']] = $module; 
}

// Fetch all materials and quizzes
if (!empty($module_ids)) {
    $module_ids_str = implode(',', $module_ids);
    
    $materials_query = "
        SELECT id, module_id, title, content_type, content_url, material_order
        FROM learning_materials
        WHERE module_id IN ($module_ids_str)
        ORDER BY material_order ASC";
    $materials_result = mysqli_query($conn, $materials_query);

    while ($material = mysqli_fetch_assoc($materials_result)) {
        $m_id = $material['module_id'];
        if (isset($modules_list_assoc[$m_id])) {
            $modules_list_assoc[$m_id]['materials'][] = $material;
        }
    }
    
    $quizzes_query = "
        SELECT 
            q.id as quiz_id, q.module_id, q.title as quiz_title, q.passing_score,
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
            (SELECT MAX(score) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = '$user_id') as best_score,
            (SELECT MAX(passed) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = '$user_id') as has_passed
        FROM quizzes q
        WHERE q.module_id IN ($module_ids_str)";
    $quizzes_result = mysqli_query($conn, $quizzes_query);
    
    while ($quiz = mysqli_fetch_assoc($quizzes_result)) {
        $m_id = $quiz['module_id'];
        if (isset($modules_list_assoc[$m_id])) {
            $modules_list_assoc[$m_id]['quiz'] = $quiz;
        }
    }
}

$modules_list = array_values($modules_list_assoc); 
$progress_percentage = ($total_modules > 0) ? round(($completed_modules / $total_modules) * 100) : 0;

// --- 5. Fetch User's Existing Rating ---
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

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Course Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
                <a href="dashboard.php" class="hover:text-brand-blue transition-colors">My Learning</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 dark:text-white"><?php echo $course_title; ?></span>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span class="text-green-700 dark:text-green-300"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="text-red-700 dark:text-red-300"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $course_title; ?></h1>
                    
                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center gap-4 text-sm mb-4">
                        <?php if (!empty($course_category)): ?>
                        <span class="bg-brand-blue-light dark:bg-blue-900/30 text-brand-blue dark:text-blue-300 px-3 py-1 rounded-full font-medium">
                            <?php echo $course_category; ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($course_level)): ?>
                        <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            <i class="fas fa-signal"></i> <?php echo $course_level; ?>
                        </span>
                        <?php endif; ?>
                        
                        <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            <i class="fas fa-book"></i> <?php echo $total_modules; ?> modules
                        </span>
                        
                        <?php if (!empty($course_duration)): ?>
                        <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            <i class="fas fa-clock"></i> <?php echo $course_duration; ?> hours
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-gray-600 dark:text-gray-400 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-tie"></i>
                        Instructed by <span class="font-semibold text-gray-900 dark:text-white"><?php echo $lecturer_name; ?></span>
                    </p>
                    
                    <!-- Rating -->
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($avg_rating)): ?>
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white"><?php echo $avg_rating > 0 ? $avg_rating : 'New'; ?></span>
                        <?php if ($rating_count > 0): ?>
                            <span class="text-gray-500 dark:text-gray-400">(<?php echo $rating_count; ?> ratings)</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Progress Card -->
                <div class="lg:w-80 bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-6">
                    <div class="text-center mb-4">
                        <div class="text-4xl font-bold text-brand-blue mb-1"><?php echo $progress_percentage; ?>%</div>
                        <div class="text-gray-500 dark:text-gray-400">Course Progress</div>
                    </div>
                    
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-3 mb-4 overflow-hidden">
                        <div class="h-3 rounded-full transition-all duration-500 <?php echo ($progress_percentage == 100) ? 'bg-green-500' : 'bg-brand-blue'; ?>" 
                             style="width: <?php echo $progress_percentage; ?>%"></div>
                    </div>
                    
                    <div class="text-center text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <?php echo $completed_modules; ?> of <?php echo $total_modules; ?> modules completed
                    </div>
                    
                    <?php if ($progress_percentage == 100): ?>
                        <a href="student_actions.php?action=certificate&course_id=<?php echo $course_id; ?>" 
                           class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                            <i class="fas fa-certificate"></i> Get Certificate
                        </a>
                    <?php endif; ?>
                    
                    <a href="course_discussion.php?course_id=<?php echo $course_id; ?>" 
                       class="w-full mt-3 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 text-gray-700 dark:text-white font-medium py-2.5 rounded-xl text-center flex items-center justify-center gap-2 transition-all">
                        <i class="fas fa-comments"></i> Discussion Forum
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Content -->
    <div class="container mx-auto px-4 lg:px-8 py-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
            <i class="fas fa-list-ul text-brand-blue"></i>
            Course Content
        </h2>
        
        <div class="space-y-4">
            <?php foreach ($modules_list as $index => $module): ?>
                <?php $is_completed = isset($module['progress_status']) && $module['progress_status'] === 'completed'; ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <!-- Module Header -->
                    <div class="module-header p-5 cursor-pointer flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" 
                         data-module-id="<?php echo $module['module_id']; ?>">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 <?php echo $is_completed ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-700'; ?>">
                                <?php if ($is_completed): ?>
                                    <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                                <?php else: ?>
                                    <span class="text-gray-600 dark:text-gray-400 font-semibold"><?php echo $index + 1; ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($module['module_title']); ?>
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo count($module['materials']); ?> materials
                                    <?php if (isset($module['quiz'])): ?>
                                        • 1 quiz
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <?php if ($is_completed): ?>
                                <span class="text-green-600 dark:text-green-400 text-sm font-medium hidden sm:block">Completed</span>
                            <?php endif; ?>
                            <i class="fas fa-chevron-down module-chevron text-gray-400 transition-transform duration-300"></i>
                        </div>
                    </div>

                    <!-- Module Content -->
                    <div id="content-<?php echo $module['module_id']; ?>" class="hidden border-t border-gray-100 dark:border-gray-700">
                        <div class="p-5">
                            <?php if (!empty($module['materials'])): ?>
                                <div class="space-y-3 mb-4">
                                    <?php foreach ($module['materials'] as $material): ?>
                                        <?php
                                            $icon = 'fa-file-alt';
                                            $icon_bg = 'bg-blue-100 dark:bg-blue-900/30';
                                            $icon_color = 'text-blue-600 dark:text-blue-400';
                                            if ($material['content_type'] === 'video') {
                                                $icon = 'fa-play-circle';
                                                $icon_bg = 'bg-red-100 dark:bg-red-900/30';
                                                $icon_color = 'text-red-600 dark:text-red-400';
                                            } elseif ($material['content_type'] === 'pdf') {
                                                $icon = 'fa-file-pdf';
                                                $icon_bg = 'bg-orange-100 dark:bg-orange-900/30';
                                                $icon_color = 'text-orange-600 dark:text-orange-400';
                                            }
                                        ?>
                                        <a href="view_material.php?material_id=<?php echo $material['id']; ?>" 
                                           class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-brand-blue-light dark:hover:bg-blue-900/30 transition-all group">
                                            <div class="w-10 h-10 <?php echo $icon_bg; ?> rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas <?php echo $icon; ?> <?php echo $icon_color; ?>"></i>
                                            </div>
                                            <div class="flex-1">
                                                <span class="font-medium text-gray-900 dark:text-white group-hover:text-brand-blue transition-colors">
                                                    <?php echo htmlspecialchars($material['title']); ?>
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2 uppercase">
                                                    <?php echo $material['content_type']; ?>
                                                </span>
                                            </div>
                                            <i class="fas fa-arrow-right text-gray-400 group-hover:text-brand-blue transition-colors"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 dark:text-gray-400 italic mb-4">No materials in this module yet.</p>
                            <?php endif; ?>

                            <!-- Quiz Section -->
                            <?php if (isset($module['quiz'])): ?>
                                <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl">
                                    <div class="flex items-center justify-between flex-wrap gap-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-clipboard-check text-purple-600 dark:text-purple-400"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-purple-900 dark:text-purple-200">
                                                    <?php echo htmlspecialchars($module['quiz']['quiz_title']); ?>
                                                </h4>
                                                <p class="text-sm text-purple-600 dark:text-purple-400">
                                                    <?php echo $module['quiz']['question_count']; ?> questions • Pass: <?php echo $module['quiz']['passing_score']; ?>%
                                                </p>
                                                <?php if ($module['quiz']['best_score'] !== null): ?>
                                                    <p class="text-sm mt-1 <?php echo $module['quiz']['has_passed'] ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'; ?>">
                                                        <?php if ($module['quiz']['has_passed']): ?>
                                                            <i class="fas fa-check-circle mr-1"></i>Passed! Best: <?php echo $module['quiz']['best_score']; ?>%
                                                        <?php else: ?>
                                                            <i class="fas fa-clock mr-1"></i>Best: <?php echo $module['quiz']['best_score']; ?>%
                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <a href="take_quiz.php?quiz_id=<?php echo $module['quiz']['quiz_id']; ?>" 
                                           class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-md flex items-center gap-2">
                                            <i class="fas fa-play"></i>
                                            <?php echo ($module['quiz']['best_score'] !== null) ? 'Retry' : 'Start Quiz'; ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Mark Complete Button -->
                            <?php if (!$is_completed): ?>
                                <form method="POST" class="mt-4">
                                    <input type="hidden" name="complete_module_id" value="<?php echo $module['module_id']; ?>">
                                    <button type="submit" class="w-full sm:w-auto bg-brand-blue hover:bg-brand-blue-dark text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-md flex items-center justify-center gap-2">
                                        <i class="fas fa-check"></i> Mark as Complete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Rating Section (Students Only) -->
        <?php if ($user_role === 'student'): ?>
        <div class="mt-10 bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                <i class="fas fa-star text-yellow-400"></i>
                <?php echo $existing_rating ? 'Update Your Rating' : 'Rate This Course'; ?>
            </h2>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="submit_rating" value="1">
                
                <!-- Star Rating Input -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Your Rating</label>
                    <div class="flex gap-2" id="star-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" class="sr-only peer" <?php echo ($existing_rating == $i) ? 'checked' : ''; ?>>
                                <svg class="w-10 h-10 transition-all duration-150 star-icon
                                            <?php echo ($existing_rating && $i <= $existing_rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'; ?>
                                            hover:text-yellow-400 hover:scale-110 peer-checked:text-yellow-400" 
                                     fill="currentColor" viewBox="0 0 20 20" data-star="<?php echo $i; ?>">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <!-- Review Textarea -->
                <div>
                    <label for="review" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        Review (Optional)
                    </label>
                    <textarea name="review" id="review" rows="4" 
                              class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-brand-blue transition-colors resize-none"
                              placeholder="Share your experience with this course..."><?php echo htmlspecialchars($existing_review); ?></textarea>
                </div>
                
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-8 rounded-xl transition-all shadow-md flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    <?php echo $existing_rating ? 'Update Rating' : 'Submit Rating'; ?>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Module accordion toggle
    const moduleHeaders = document.querySelectorAll('.module-header');

    moduleHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            if (e.target.closest('form') || e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            const moduleId = this.getAttribute('data-module-id');
            const content = document.getElementById(`content-${moduleId}`);
            const icon = this.querySelector('.module-chevron');

            content.classList.toggle('hidden');
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
                radioInputs[index].checked = true;
            });
        });
        
        starContainer.addEventListener('mouseleave', () => {
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

<?php include("../footer.php"); ?>