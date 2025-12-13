<?php
session_start();

include("../connection.php");

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

// Set user variables from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Handle Enrollment Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_enrollment'])) {
    $c_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    
    // Allow cancellation of pending enrollments
    $delete_query = "DELETE FROM enrollment WHERE user_id = '$user_id' AND course_id = '$c_id' AND payment_status = 'pending'";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "Enrollment cancelled successfully.";
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to cancel enrollment.";
    }
}

// Fetch profile picture
$profile_query = "SELECT profile_picture FROM users WHERE id = '$user_id' LIMIT 1";
$profile_result = mysqli_query($conn, $profile_query);
$profile_data = mysqli_fetch_assoc($profile_result);
$profile_picture = $profile_data['profile_picture'] ?? null;

// Fetch enrolled courses and progress
$sql_enrolled_courses = "
    SELECT 
        c.id AS course_id,
        c.title AS course_title,
        u.name AS lecturer_name,
        e.enroll_date,
        e.payment_status,
        c.fee,
        c.category,
        c.level,
        c.course_image,
        -- Calculates progress based on completed modules vs. total modules for the course
        COALESCE(
            (SELECT ROUND((COUNT(p.id) * 100.0) / (SELECT COUNT(m.id) FROM modules m WHERE m.course_id = c.id))
             FROM progress p
             JOIN modules m ON p.module_id = m.id
             WHERE m.course_id = c.id AND p.user_id = '{$user_id}' AND p.status = 'completed'), 
            0
        ) AS progress_percentage,
        (SELECT COUNT(m.id) FROM modules m WHERE m.course_id = c.id) AS total_modules
    FROM 
        enrollment e
    JOIN 
        courses c ON e.course_id = c.id
    JOIN 
        users u ON c.lecturer_id = u.id
    WHERE 
        e.user_id = '{$user_id}'
    ORDER BY 
        e.enroll_date DESC";

$result = mysqli_query($conn, $sql_enrolled_courses);

// Stats
$total_courses = mysqli_num_rows($result);
$completed_courses = 0;
$in_progress_courses = 0;
$courses_data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $courses_data[] = $row;
    if ((int)$row['progress_percentage'] === 100) {
        $completed_courses++;
    } elseif ((int)$row['progress_percentage'] > 0) {
        $in_progress_courses++;
    }
}

// Get first name for greeting
$first_name = explode(' ', $user_name)[0];

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-brand-blue to-blue-600 dark:from-gray-800 dark:to-gray-900">
        <div class="container mx-auto px-4 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <!-- Welcome Message -->
                <div class="flex items-center gap-5">
                    <?php if (!empty($profile_picture)): ?>
                        <img src="../uploads/avatars/<?php echo htmlspecialchars($profile_picture); ?>" 
                             alt="Avatar" class="w-20 h-20 rounded-2xl object-cover border-4 border-white/30 shadow-lg">
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center border-4 border-white/30">
                            <i class="fas fa-user text-3xl text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-white mb-1">Welcome back, <?php echo htmlspecialchars($first_name); ?>!</h1>
                        <p class="text-blue-100 dark:text-gray-400">Continue your learning journey</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="flex gap-3">
                    <a href="catalog.php" class="bg-white/20 hover:bg-white/30 text-white font-medium px-5 py-2.5 rounded-xl transition-all flex items-center gap-2">
                        <i class="fas fa-compass"></i>
                        <span class="hidden sm:inline">Explore Courses</span>
                    </a>
                    <a href="profile_edit.php" class="bg-white text-brand-blue hover:bg-blue-50 font-semibold px-5 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-lg">
                        <i class="fas fa-user-cog"></i>
                        <span class="hidden sm:inline">Edit Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 -mt-16 mb-10">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-book text-2xl text-brand-blue"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $total_courses; ?></div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Enrolled Courses</div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-spinner text-2xl text-amber-500"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $in_progress_courses; ?></div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">In Progress</div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl text-green-500"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $completed_courses; ?></div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Learning Section -->
        <section class="mb-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fas fa-book-open text-brand-blue"></i>
                    My Learning
                </h2>
                <?php if ($total_courses > 0): ?>
                <a href="catalog.php" class="text-brand-blue hover:text-brand-blue-dark font-medium text-sm flex items-center gap-1 transition-colors">
                    Browse more courses <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <?php endif; ?>
            </div>

            <?php if (count($courses_data) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($courses_data as $row): ?>
                        <?php 
                            $progress = (int)$row['progress_percentage'];
                            $is_paid = ($row['payment_status'] == 'paid');
                            $is_complete = ($progress === 100);
                        ?>
                        <!-- course card -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">
                            <!-- Course Thumbnail Area -->
                            <div class="h-36 bg-gradient-to-br from-brand-blue-light to-blue-100 dark:from-gray-700 dark:to-gray-600 relative flex items-center justify-center overflow-hidden">
                                <?php if (!empty($row['course_image'])): ?>
                                    <img src="../uploads/courses/<?php echo htmlspecialchars($row['course_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($row['course_title']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-book-open text-5xl text-brand-blue/30 dark:text-white/20"></i>
                                <?php endif; ?>
                                
                                <?php if (!empty($row['category'])): ?>
                                <span class="absolute top-3 left-3 bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1 rounded-full shadow-sm z-10">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                                <?php endif; ?>
                                
                                <?php if ($is_complete): ?>
                                <div class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shadow-sm z-10">
                                    <i class="fas fa-check-circle"></i> Complete
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Course Info -->
                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($row['course_title']); ?>
                                </h3>
                                
                                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                                    By <?php echo htmlspecialchars($row['lecturer_name']); ?>
                                </p>
                                
                                <!-- Progress Bar -->
                                <div class="mb-4 mt-auto">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Progress</span>
                                        <span class="text-sm font-bold <?php echo $is_complete ? 'text-green-600' : 'text-brand-blue'; ?>">
                                            <?php echo $progress; ?>%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-2.5 rounded-full transition-all duration-500 <?php echo $is_complete ? 'bg-green-500' : 'bg-brand-blue'; ?>" 
                                             style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                </div>
                                
                                <!-- Payment Status -->
                                <?php if (!$is_paid): ?>
                                    <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                                        <p class="text-amber-700 dark:text-amber-300 text-sm font-medium flex items-center gap-2">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Payment pending
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="p-5 pt-0 space-y-2">
                                <?php if ($is_paid && $is_complete): ?>
                                    <a href="student_actions.php?action=certificate&course_id=<?php echo $row['course_id']; ?>" 
                                       class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                                        <i class="fas fa-certificate"></i> View Certificate
                                    </a>
                                <?php elseif ($is_paid): ?>
                                    <a href="course_view.php?id=<?php echo $row['course_id']; ?>" 
                                       class="w-full bg-brand-blue hover:bg-brand-blue-dark text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                                        <i class="fas fa-play"></i> Continue Course
                                    </a>
                                <?php else: ?>
                                    <a href="payment.php?course_id=<?php echo $row['course_id']; ?>" 
                                       class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                                        <i class="fas fa-credit-card"></i> Complete Payment ($<?php echo number_format($row['fee'], 2); ?>)
                                    </a>
                                    
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this enrollment?');">
                                        <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                                        <button type="submit" name="cancel_enrollment" class="w-full text-red-500 hover:text-red-700 text-sm font-medium py-2 hover:underline transition-all">
                                            Cancel Enrollment
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="course_discussion.php?course_id=<?php echo $row['course_id']; ?>" 
                                   class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium py-2.5 rounded-xl text-center flex items-center justify-center gap-2 transition-all text-sm">
                                    <i class="fas fa-comments"></i> Discussion Forum
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700">
                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-book-open text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Courses Yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                        You haven't enrolled in any courses yet. Start your learning journey by exploring our catalog.
                    </p>
                    <a href="catalog.php" class="inline-flex items-center gap-2 bg-brand-blue hover:bg-brand-blue-dark text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg">
                        <i class="fas fa-compass"></i> Explore Courses
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php 
mysqli_close($conn);
include("../footer.php"); 
?>