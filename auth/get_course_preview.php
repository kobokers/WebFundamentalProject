<?php
/**
 * AJAX endpoint to get course preview (modules list)
 * Used by catalog.php preview modal
 */
include("../connection.php");

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if ($course_id <= 0) {
    echo '<p class="text-red-500">Invalid course ID.</p>';
    exit;
}

// Get course info
$course_query = "SELECT title, description, level, fee, category FROM courses WHERE id = $course_id";
$course_result = mysqli_query($conn, $course_query);

if (mysqli_num_rows($course_result) == 0) {
    echo '<p class="text-red-500">Course not found.</p>';
    exit;
}

$course = mysqli_fetch_assoc($course_result);

// Get modules
$modules_query = "SELECT title, module_order FROM modules WHERE course_id = $course_id ORDER BY module_order ASC";
$modules_result = mysqli_query($conn, $modules_query);

// Get material count per module
$materials_query = "
    SELECT m.id as module_id, COUNT(lm.id) as material_count 
    FROM modules m 
    LEFT JOIN learning_materials lm ON m.id = lm.module_id 
    WHERE m.course_id = $course_id 
    GROUP BY m.id";
$materials_result = mysqli_query($conn, $materials_query);

$material_counts = [];
while ($mat = mysqli_fetch_assoc($materials_result)) {
    $material_counts[$mat['module_id']] = $mat['material_count'];
}

mysqli_close($conn);
?>

<div class="space-y-4">
    <!-- Course Info -->
    <div class="border-b dark:border-gray-700 pb-4">
        <div class="flex flex-wrap gap-2 mb-2">
            <span class="inline-block bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 text-xs font-semibold px-2 py-1 rounded">
                <?php echo htmlspecialchars($course['level']); ?>
            </span>
            <?php if (!empty($course['category'])): ?>
            <span class="inline-block bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 text-xs font-semibold px-2 py-1 rounded">
                <?php echo htmlspecialchars($course['category']); ?>
            </span>
            <?php endif; ?>
            <span class="inline-block <?php echo ($course['fee'] > 0) ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'; ?> text-xs font-semibold px-2 py-1 rounded">
                <?php echo ($course['fee'] > 0) ? '$' . number_format($course['fee'], 2) : 'FREE'; ?>
            </span>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($course['description']); ?></p>
    </div>

    <!-- Modules List -->
    <div>
        <h4 class="font-semibold text-gray-800 dark:text-white mb-3">
            <i class="fas fa-list-ul mr-2"></i>Course Modules (<?php echo mysqli_num_rows($modules_result); ?>)
        </h4>
        
        <?php if (mysqli_num_rows($modules_result) > 0): ?>
            <ul class="space-y-2">
                <?php 
                mysqli_data_seek($modules_result, 0);
                while ($module = mysqli_fetch_assoc($modules_result)): 
                ?>
                    <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-gray-700 dark:text-gray-300">
                            <span class="font-medium text-gray-500 dark:text-gray-400 mr-2"><?php echo $module['module_order']; ?>.</span>
                            <?php echo htmlspecialchars($module['title']); ?>
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            <i class="fas fa-file-alt mr-1"></i>preview
                        </span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 dark:text-gray-400 italic text-sm">No modules available yet.</p>
        <?php endif; ?>
    </div>

    <div class="pt-4 border-t dark:border-gray-700 text-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <i class="fas fa-info-circle mr-1"></i> Enroll to access full course content
        </p>
    </div>
</div>
