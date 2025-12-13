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
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

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
if (!empty($search_term)) {
    $where_conditions[] = "(c.title LIKE '%$search_term%' OR c.description LIKE '%$search_term%')";
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
        c.duration,
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
$total_courses = mysqli_num_rows($result);
$has_filters = !empty($filter_level) || !empty($filter_instructor) || !empty($filter_category) || !empty($search_term);
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-coursera-blue via-blue-600 to-blue-700 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900">
        <div class="container mx-auto px-4 lg:px-8 py-16">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">Explore Our Courses</h1>
                <p class="text-blue-100 dark:text-gray-400 text-lg mb-8">
                    Discover hundreds of courses taught by expert instructors
                </p>
                
                <!-- Search Bar -->
                <form method="GET" class="relative max-w-2xl mx-auto">
                    <div class="relative">
                        <input type="text" name="search" 
                               value="<?php echo htmlspecialchars($search_term); ?>" 
                               placeholder="Search for courses, topics, or instructors..."
                               class="w-full px-6 py-4 pl-14 rounded-2xl text-gray-900 dark:text-white bg-white dark:bg-gray-800 border-0 shadow-xl focus:outline-none focus:ring-4 focus:ring-white/30 text-lg">
                        <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                        <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-coursera-blue hover:bg-coursera-blue-dark text-white font-semibold px-6 py-2.5 rounded-xl transition-all">
                            Search
                        </button>
                    </div>
                    
                    <!-- Hidden fields to preserve other filters -->
                    <?php if (!empty($filter_level)): ?>
                        <input type="hidden" name="level" value="<?php echo htmlspecialchars($filter_level); ?>">
                    <?php endif; ?>
                    <?php if (!empty($filter_instructor)): ?>
                        <input type="hidden" name="instructor" value="<?php echo htmlspecialchars($filter_instructor); ?>">
                    <?php endif; ?>
                    <?php if (!empty($filter_category)): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($filter_category); ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 sticky top-24">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-filter text-coursera-blue"></i>
                            Filters
                        </h3>
                        <?php if ($has_filters): ?>
                            <a href="catalog.php" class="text-sm text-red-500 hover:text-red-600 font-medium transition-colors">
                                Clear all
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <form method="GET" id="filterForm">
                        <!-- Preserve search term -->
                        <?php if (!empty($search_term)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <?php endif; ?>
                        
                        <!-- Level Filter -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Level</label>
                            <select name="level" onchange="document.getElementById('filterForm').submit()"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-coursera-blue transition-colors">
                                <option value="">All Levels</option>
                                <?php 
                                mysqli_data_seek($levels_result, 0);
                                while ($level = mysqli_fetch_assoc($levels_result)): ?>
                                    <option value="<?php echo htmlspecialchars($level['level']); ?>" 
                                            <?php echo ($filter_level == $level['level']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($level['level']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Category</label>
                            <select name="category" onchange="document.getElementById('filterForm').submit()"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-coursera-blue transition-colors">
                                <option value="">All Categories</option>
                                <?php 
                                mysqli_data_seek($categories_result, 0);
                                while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                            <?php echo ($filter_category == $cat['category']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Instructor Filter -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Instructor</label>
                            <select name="instructor" onchange="document.getElementById('filterForm').submit()"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-coursera-blue transition-colors">
                                <option value="">All Instructors</option>
                                <?php 
                                mysqli_data_seek($instructors_result, 0);
                                while ($instructor = mysqli_fetch_assoc($instructors_result)): ?>
                                    <option value="<?php echo $instructor['id']; ?>" 
                                            <?php echo ($filter_instructor == $instructor['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($instructor['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Course Grid -->
            <div class="flex-1">
                <!-- Results Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">
                            Showing <span class="font-semibold text-gray-900 dark:text-white"><?php echo $total_courses; ?></span> courses
                            <?php if ($has_filters): ?>
                                <span class="text-coursera-blue">(filtered)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php 
                        mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)): 
                            $is_enrolled = !is_null($row['payment_status']);
                            $is_paid = ($row['payment_status'] === 'paid');
                            $is_pending = ($row['payment_status'] === 'pending');
                            $avg_rating = round($row['avg_rating'], 1);
                            $rating_count = $row['rating_count'];
                            
                            // Level badge color
                            $level_class = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
                            if ($row['level'] === 'Intermediate') {
                                $level_class = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                            } elseif ($row['level'] === 'Advanced') {
                                $level_class = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                            }
                        ?>
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col group">
                                <!-- Course Thumbnail -->
                                <div class="h-40 bg-gradient-to-br from-coursera-blue-light to-blue-100 dark:from-gray-700 dark:to-gray-600 relative flex items-center justify-center overflow-hidden">
                                    <i class="fas fa-book-open text-6xl text-coursera-blue/20 dark:text-white/10 group-hover:scale-110 transition-transform duration-300"></i>
                                    
                                    <?php if (!empty($row['category'])): ?>
                                    <span class="absolute top-3 left-3 bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1 rounded-full">
                                        <?php echo htmlspecialchars($row['category']); ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['fee'] == 0): ?>
                                    <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                        FREE
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Course Info -->
                                <div class="p-5 flex-1 flex flex-col">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-2 line-clamp-2 group-hover:text-coursera-blue transition-colors">
                                        <?php echo htmlspecialchars($row['course_title']); ?>
                                    </h3>
                                    
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-3 flex items-center gap-2">
                                        <i class="fas fa-user-tie text-gray-400"></i>
                                        <?php echo htmlspecialchars($row['lecturer_name']); ?>
                                    </p>
                                    
                                    <!-- Star Rating -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="flex">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= floor($avg_rating)): ?>
                                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                    </svg>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo $avg_rating > 0 ? $avg_rating : 'New'; ?>
                                            <?php if ($rating_count > 0): ?>
                                                <span class="text-gray-400">(<?php echo $rating_count; ?>)</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Meta Info -->
                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500 mb-4">
                                        <span class="<?php echo $level_class; ?> px-2 py-1 rounded-full font-medium">
                                            <?php echo htmlspecialchars($row['level']); ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-book"></i> <?php echo $row['module_count']; ?> modules
                                        </span>
                                        <?php if (!empty($row['duration'])): ?>
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-clock"></i> <?php echo $row['duration']; ?>h
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 mb-4 flex-1">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </p>
                                </div>
                                
                                <!-- Footer -->
                                <div class="p-5 pt-0 space-y-3 border-t border-gray-100 dark:border-gray-700 mt-auto">
                                    <!-- Price -->
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl font-bold <?php echo ($row['fee'] > 0) ? 'text-gray-900 dark:text-white' : 'text-green-600'; ?>">
                                            <?php echo ($row['fee'] > 0) ? '$' . number_format($row['fee'], 2) : 'FREE'; ?>
                                        </span>
                                        
                                        <!-- Preview Button -->
                                        <button onclick="openPreview(<?php echo $row['course_id']; ?>, '<?php echo htmlspecialchars(addslashes($row['course_title'])); ?>')" 
                                                class="text-coursera-blue hover:text-coursera-blue-dark font-medium text-sm flex items-center gap-1 transition-colors">
                                            <i class="fas fa-eye"></i> Preview
                                        </button>
                                    </div>
                                    
                                    <!-- Action Button -->
                                    <?php if ($user_role === 'guest'): ?>
                                        <a href="./login.php" class="w-full bg-coursera-blue hover:bg-coursera-blue-dark text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                                            <i class="fas fa-sign-in-alt"></i> Login to Enroll
                                        </a>
                                    <?php elseif ($user_role !== 'student'): ?>
                                        <button disabled class="w-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-semibold py-3 rounded-xl cursor-not-allowed">
                                            Viewing as <?php echo ucfirst($user_role); ?>
                                        </button>
                                    <?php elseif ($is_paid): ?>
                                        <a href="./course_view.php?id=<?php echo $row['course_id']; ?>" 
                                           class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                                            <i class="fas fa-play-circle"></i> Go to Course
                                        </a>
                                    <?php elseif ($is_pending): ?>
                                        <a href="./payment.php?course_id=<?php echo $row['course_id']; ?>" 
                                           class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                                            <i class="fas fa-credit-card"></i> Complete Payment
                                        </a>
                                    <?php else: ?>
                                        <a href="./enroll_course.php?course_id=<?php echo $row['course_id']; ?>" 
                                           class="w-full bg-coursera-blue hover:bg-coursera-blue-dark text-white font-semibold py-3 rounded-xl text-center flex items-center justify-center gap-2 transition-all shadow-md">
                                            <i class="fas fa-plus-circle"></i> Enroll Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700">
                        <div class="w-24 h-24 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-search text-4xl text-amber-500"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Courses Found</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            Try adjusting your filters or search terms to find more courses.
                        </p>
                        <a href="catalog.php" class="inline-flex items-center gap-2 bg-coursera-blue hover:bg-coursera-blue-dark text-white font-semibold px-6 py-3 rounded-xl transition-all">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Back to Dashboard Links -->
        <div class="mt-10 text-center">
            <?php if ($user_role === 'student'): ?>
                <a href="./dashboard.php" class="text-gray-500 hover:text-coursera-blue dark:text-gray-400 font-medium inline-flex items-center gap-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Back to My Learning
                </a>
            <?php elseif ($user_role === 'lecturer'): ?>
                <a href="../lecturer/dashboard.php" class="text-gray-500 hover:text-coursera-blue dark:text-gray-400 font-medium inline-flex items-center gap-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            <?php elseif ($user_role === 'admin'): ?>
                <a href="../admin/dashboard.php" class="text-gray-500 hover:text-coursera-blue dark:text-gray-400 font-medium inline-flex items-center gap-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>       
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Course Preview Modal -->
<div id="previewModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 id="previewTitle" class="text-xl font-bold text-gray-900 dark:text-white">Course Preview</h3>
            <button onclick="closePreview()" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="previewContent" class="p-6 overflow-y-auto text-gray-600 dark:text-gray-300">
            <div class="flex items-center justify-center py-12">
                <i class="fas fa-spinner fa-spin text-2xl text-coursera-blue"></i>
            </div>
        </div>
    </div>
</div>

<script>
function openPreview(courseId, courseTitle) {
    document.getElementById('previewModal').classList.remove('hidden');
    document.getElementById('previewTitle').textContent = courseTitle;
    
    // Fetch course modules via AJAX
    fetch('get_course_preview.php?course_id=' + courseId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('previewContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('previewContent').innerHTML = '<p class="text-red-500 text-center py-8">Failed to load preview.</p>';
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

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePreview();
    }
});
</script>

<?php include("../footer.php"); ?>