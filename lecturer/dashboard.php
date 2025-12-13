<?php
session_start();
include("../connection.php");

// Authentication and Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
  $_SESSION['error'] = "Access denied. Please log in as a lecturer.";
  header("Location: ../auth/login.php");
  exit;
}

// Set lecturer variables from session
$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['user_name'];

// Fetch profile picture
$profile_query = "SELECT profile_picture FROM users WHERE id = '$lecturer_id' LIMIT 1";
$profile_result = mysqli_query($conn, $profile_query);
$profile_data = mysqli_fetch_assoc($profile_result);
$profile_picture = $profile_data['profile_picture'] ?? null;

// Get stats
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM courses WHERE lecturer_id = '$lecturer_id') as total_courses,
    (SELECT COUNT(DISTINCT e.user_id) FROM enrollment e JOIN courses c ON e.course_id = c.id WHERE c.lecturer_id = '$lecturer_id' AND e.payment_status = 'paid') as total_students,
    (SELECT COUNT(*) FROM modules m JOIN courses c ON m.course_id = c.id WHERE c.lecturer_id = '$lecturer_id') as total_modules";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch courses
$sql_courses = "
  SELECT
    c.id AS course_id,
    c.title AS course_title,
    c.level,
    c.language,
    c.fee,
    c.category,
    c.course_image,
    (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) as module_count,
    (SELECT COUNT(*) FROM enrollment e WHERE e.course_id = c.id AND e.payment_status = 'paid') as student_count
  FROM
    courses c
  WHERE
    c.lecturer_id = '{$lecturer_id}'
  ORDER BY
    c.title ASC";

$result = mysqli_query($conn, $sql_courses);

// Get first name
$first_name = explode(' ', $lecturer_name)[0];

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 dark:from-gray-800 dark:to-gray-900">
        <div class="container mx-auto px-4 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <!-- Welcome Message -->
                <div class="flex items-center gap-5">
                    <?php if (!empty($profile_picture)): ?>
                        <img src="../uploads/avatars/<?php echo htmlspecialchars($profile_picture); ?>" 
                             alt="Avatar" class="w-20 h-20 rounded-2xl object-cover border-4 border-white/30 shadow-lg">
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center border-4 border-white/30">
                            <i class="fas fa-chalkboard-teacher text-3xl text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-white mb-1">Welcome back, <?php echo htmlspecialchars($first_name); ?>!</h1>
                        <p class="text-purple-200 dark:text-gray-400">Lecturer Dashboard</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="flex gap-3">
                    <a href="edit_profile.php" class="bg-white/20 hover:bg-white/30 text-white font-medium px-5 py-2.5 rounded-xl transition-all flex items-center gap-2">
                        <i class="fas fa-user-cog"></i>
                        <span class="hidden sm:inline">Edit Profile</span>
                    </a>
                    <a href="add_course_form.php" class="bg-white text-purple-700 hover:bg-purple-50 font-semibold px-5 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-lg">
                        <i class="fas fa-plus-circle"></i>
                        <span>Create Course</span>
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
                    <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-book text-2xl text-purple-600"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_courses']; ?></div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Total Courses</div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-graduate text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_students']; ?></div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Enrolled Students</div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <i class="fas fa-cube text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_modules']; ?></div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Total Modules</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Courses Section -->
        <section class="mb-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fas fa-chalkboard text-purple-600"></i>
                    My Courses
                </h2>
            </div>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">
                            <!-- Course Thumbnail Area -->
                            <div class="h-36 bg-gradient-to-br from-purple-100 to-purple-50 dark:from-gray-700 dark:to-gray-600 relative flex items-center justify-center overflow-hidden">
                                <?php if (!empty($row['course_image'])): ?>
                                    <img src="../uploads/courses/<?php echo htmlspecialchars($row['course_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($row['course_title']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-book-open text-5xl text-purple-300 dark:text-white/20"></i>
                                <?php endif; ?>
                                
                                <?php if (!empty($row['category'])): ?>
                                <span class="absolute top-3 left-3 bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1 rounded-full shadow-sm z-10">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                                <?php endif; ?>
                                
                                <span class="absolute top-3 right-3 bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm z-10">
                                    $<?php echo number_format($row['fee'], 2); ?>
                                </span>
                            </div>
                            
                            <!-- Course Info -->
                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($row['course_title']); ?>
                                </h3>
                                
                                <!-- Meta Info -->
                                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-signal"></i> <?php echo htmlspecialchars($row['level']); ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-cube"></i> <?php echo $row['module_count']; ?> modules
                                    </span>
                                </div>
                                
                                <!-- Students Count -->
                                <div class="flex items-center gap-2 mb-4 mt-auto">
                                    <div class="flex -space-x-2">
                                        <?php for($i = 0; $i < min(3, $row['student_count']); $i++): ?>
                                            <div class="w-8 h-8 bg-purple-<?php echo 100 + ($i * 100); ?> dark:bg-purple-<?php echo 900 - ($i * 100); ?> rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center">
                                                <i class="fas fa-user text-xs text-purple-600 dark:text-purple-400"></i>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-semibold"><?php echo $row['student_count']; ?></span> students enrolled
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="p-5 pt-0 space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="module_setup.php?course_id=<?php echo $row['course_id']; ?>" 
                                       class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2.5 rounded-xl text-center flex items-center justify-center gap-2 transition-all text-sm">
                                        <i class="fas fa-cubes"></i> Modules
                                    </a>
                                    <a href="edit_course.php?course_id=<?php echo $row['course_id']; ?>" 
                                       class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium py-2.5 rounded-xl text-center flex items-center justify-center gap-2 transition-all text-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="view_enrollment.php?course_id=<?php echo $row['course_id']; ?>" 
                                       class="bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 font-medium py-2.5 rounded-xl text-center flex items-center justify-center gap-2 transition-all text-sm">
                                        <i class="fas fa-users"></i> Students
                                    </a>
                                    <a href="course_discussion.php?course_id=<?php echo $row['course_id']; ?>" 
                                       class="bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 font-medium py-2.5 rounded-xl text-center flex items-center justify-center gap-2 transition-all text-sm">
                                        <i class="fas fa-comments"></i> Forum
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700">
                    <div class="w-24 h-24 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chalkboard-teacher text-4xl text-purple-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Courses Yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                        You haven't created any courses yet. Start sharing your knowledge with students!
                    </p>
                    <a href="add_course_form.php" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg">
                        <i class="fas fa-plus-circle"></i> Create Your First Course
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