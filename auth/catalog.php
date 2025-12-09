<?php
session_start();
include("../header.php"); 
include("../connection.php"); 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';

// --- Filter Parameters ---
$filter_level = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : '';
$filter_instructor = isset($_GET['instructor']) ? mysqli_real_escape_string($conn, $_GET['instructor']) : '';
$filter_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// --- Build WHERE Clause ---
$where_conditions = [];
if (!empty($filter_level)) {
    $where_conditions[] = "c.level = '$filter_level'";
}
if (!empty($filter_instructor)) {
    $where_conditions[] = "c.lecturer_id = '$filter_instructor'";
}
if (!empty($filter_category)) {
    $where_conditions[] = "c.category = '$filter_category'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// --- Fetch Filter Options ---
// Get all levels
$levels_query = "SELECT DISTINCT level FROM courses WHERE level IS NOT NULL ORDER BY level";
$levels_result = mysqli_query($conn, $levels_query);

// Get all instructors
$instructors_query = "SELECT DISTINCT u.id, u.name FROM users u JOIN courses c ON u.id = c.lecturer_id ORDER BY u.name";
$instructors_result = mysqli_query($conn, $instructors_query);

// Get all categories
$categories_query = "SELECT DISTINCT category FROM courses WHERE category IS NOT NULL ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);

// --- Main Query with Ratings ---
$catalog_query = "
    SELECT 
        c.id AS course_id,
        c.title AS course_title,
        c.level,
        c.fee,
        c.category,
        u.name AS lecturer_name,
        u.id AS lecturer_id,
        e.payment_status,
        c.description,
        COALESCE(AVG(cr.rating), 0) AS avg_rating,
        COUNT(DISTINCT cr.id) AS rating_count,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS module_count
    FROM 
        courses c
    JOIN 
        users u ON c.lecturer_id = u.id 
    LEFT JOIN 
        enrollment e ON c.id = e.course_id AND e.user_id = '$user_id'
    LEFT JOIN
        course_ratings cr ON c.id = cr.course_id
    $where_clause
    GROUP BY c.id
    ORDER BY 
        c.title ASC";

$result = mysqli_query($conn, $catalog_query);
?>

    <div class="container mx-auto p-8 max-w-6xl">
        <header class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-teal-700 dark:text-teal-300">Course Catalog</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Browse and enroll in our available courses.</p>
        </header>

        <!-- Filter Section -->
        <section id="filters" class="mb-8">
            <form method="GET" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 transition-colors duration-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <!-- Level Filter -->
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level</label>
                        <select name="level" id="level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                            <option value="">All Levels</option>
                            <?php 
                            mysqli_data_seek($levels_result, 0);
                            while ($level = mysqli_fetch_assoc($levels_result)): ?>
                                <option value="<?php echo htmlspecialchars($level['level']); ?>" <?php echo ($filter_level == $level['level']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($level['level']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Instructor Filter -->
                    <div>
                        <label for="instructor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instructor</label>
                        <select name="instructor" id="instructor" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                            <option value="">All Instructors</option>
                            <?php 
                            mysqli_data_seek($instructors_result, 0);
                            while ($instructor = mysqli_fetch_assoc($instructors_result)): ?>
                                <option value="<?php echo $instructor['id']; ?>" <?php echo ($filter_instructor == $instructor['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($instructor['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select name="category" id="category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                            <option value="">All Categories</option>
                            <?php 
                            mysqli_data_seek($categories_result, 0);
                            while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo ($filter_category == $cat['category']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <?php if (!empty($filter_level) || !empty($filter_instructor) || !empty($filter_category)): ?>
                            <a href="catalog.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </section>

        <section id="course-list">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php 
                        $is_enrolled = !is_null($row['payment_status']);
                        $is_paid = ($row['payment_status'] === 'paid');
                        $is_pending = ($row['payment_status'] === 'pending');
                        $avg_rating = round($row['avg_rating'], 1);
                        $rating_count = $row['rating_count'];
                    ?>
                    
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg hover:shadow-xl transition flex flex-col justify-between transition-colors duration-200">
                        <div class="p-5">
                            <!-- Category Badge -->
                            <?php if (!empty($row['category'])): ?>
                            <span class="inline-block bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 text-xs font-semibold px-2 py-1 rounded-full mb-2">
                                <?php echo htmlspecialchars($row['category']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2"><?php echo htmlspecialchars($row['course_title']); ?></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Lecturer: <?php echo htmlspecialchars($row['lecturer_name']); ?></p>
                            
                            <!-- Star Rating Display -->
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($avg_rating)): ?>
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                        <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" fill-opacity="0.5"/></svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    <?php echo $avg_rating > 0 ? $avg_rating : 'No ratings'; ?>
                                    <?php if ($rating_count > 0): ?>
                                        <span class="text-gray-400">(<?php echo $rating_count; ?>)</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between text-sm mb-3">
                                <span class="text-indigo-600 dark:text-indigo-400 font-semibold"><?php echo htmlspecialchars($row['level']); ?></span>
                                <span class="font-bold <?php echo ($row['fee'] > 0) ? 'text-red-500' : 'text-green-500'; ?>">
                                    <?php echo ($row['fee'] > 0) ? '$' . number_format($row['fee'], 2) : 'FREE'; ?>
                                </span>
                            </div>

                            <p class="text-gray-600 dark:text-gray-400 text-sm italic mb-3 line-clamp-2"><?php echo htmlspecialchars($row['description']); ?></p>
                            
                            <!-- Module Count Preview -->
                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                <i class="fas fa-book mr-1"></i> <?php echo $row['module_count']; ?> modules
                            </p>
                        </div>

                        <div class="p-5 pt-0 border-t border-gray-100 dark:border-gray-700 space-y-2">
                            <!-- Preview Button -->
                            <button onclick="openPreview(<?php echo $row['course_id']; ?>, '<?php echo htmlspecialchars(addslashes($row['course_title'])); ?>')" 
                                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 rounded-lg text-center block hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm">
                                <i class="fas fa-eye mr-1"></i> Preview Course
                            </button>
                            
                            <?php if ($user_role === 'guest'): ?>
                                <a href="./login.php" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-blue-600">
                                    Login to Enroll
                                </a>
                            <?php elseif ($user_role !== 'student'): ?>
                                <button disabled class="w-full bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-2 px-4 rounded-lg">
                                    Accessing as Lecturer/Admin
                                </button>
                            <?php elseif ($is_paid): ?>
                                <a href="./course_view.php?id=<?php echo $row['course_id']; ?>" class="w-full bg-green-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-green-600">
                                    Go to Course
                                </a>
                            <?php elseif ($is_pending): ?>
                                <a href="./payment.php?course_id=<?php echo $row['course_id']; ?>" class="w-full bg-red-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-red-600">
                                    Complete Payment
                                </a>
                            <?php else: ?>
                                <a href="./enroll_course.php?course_id=<?php echo $row['course_id']; ?>" class="w-full bg-teal-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-teal-600">
                                    Enroll Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full p-8 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-500 text-yellow-700 dark:text-yellow-200 rounded-lg">
                        <p class="font-bold">No Courses Found!</p>
                        <p>Try adjusting your filters or check back later for new courses.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <?php if ($user_role === 'student'): ?>
            <div class="mt-8 text-center">
             <a href="./dashboard.php" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Go to Dashboard</a>
            </div>
        <?php elseif ($user_role === 'lecturer'): ?>
            <div class="mt-8 text-center">
             <a href="../lecturer/dashboard.php" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Go to Dashboard</a>
            </div>
        <?php elseif ($user_role === 'admin'): ?>
            <div class="mt-8 text-center">
             <a href="../admin/dashboard.php" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Go to Dashboard</a>
            </div>       
        <?php else: ?>
            <div class="mt-8 text-center">
             <a href="../index.php" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Go back</a>
            </div>   
        <?php endif; ?>
    </div>

    <!-- Course Preview Modal -->
    <div id="previewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="previewTitle" class="text-xl font-bold text-gray-800 dark:text-white">Course Preview</h3>
                    <button onclick="closePreview()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="previewContent" class="text-gray-600 dark:text-gray-300">
                    <p class="text-center py-8"><i class="fas fa-spinner fa-spin"></i> Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openPreview(courseId, courseTitle) {
        document.getElementById('previewModal').classList.remove('hidden');
        document.getElementById('previewTitle').textContent = courseTitle + ' - Preview';
        
        // Fetch course modules via AJAX
        fetch('get_course_preview.php?course_id=' + courseId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('previewContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('previewContent').innerHTML = '<p class="text-red-500">Failed to load preview.</p>';
            });
    }
    
    function closePreview() {
        document.getElementById('previewModal').classList.add('hidden');
    }
    
    // Close modal on outside click
    document.getElementById('previewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePreview();
        }
    });
    </script>
</main>
<?php include("../footer.php"); ?>