<?php
session_start();
include("../connection.php");

// --- 1. Authentication and Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../auth/login.php");
    exit;
}

// Get required IDs
$lecturer_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course ID provided.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Verify Lecturer Owns the Course ---
$verify_query = "SELECT title FROM courses WHERE id = '$course_id' AND lecturer_id = '$lecturer_id'";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Course not found or you do not have permission to edit it.";
    header("Location: dashboard.php");
    exit;
}

$course = mysqli_fetch_assoc($verify_result);
$course_title = htmlspecialchars($course['title']);

// --- 3. Handle Form Submission (Add New Module) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['module_title'])) {
    
    $module_title = mysqli_real_escape_string($conn, $_POST['module_title']);
    $module_order = (int)$_POST['module_order'];

    $insert_query = "INSERT INTO modules (course_id, title, module_order) 
                     VALUES ('$course_id', '$module_title', '$module_order')";

    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "Module added successfully.";
        header("Location: module_setup.php?course_id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add module: " . mysqli_error($conn);
    }
}

// --- 4. Fetch Existing Modules ---
$modules_query = "SELECT id, title, module_order FROM modules WHERE course_id = '$course_id' ORDER BY module_order ASC";
$modules_result = mysqli_query($conn, $modules_query);

// --- 5. Fetch Learning Materials and Organize by Module ID ---
$materials_by_module = [];
$quizzes_by_module = [];

if (mysqli_num_rows($modules_result) > 0) {
    $module_ids = [];
    $temp_modules_result = mysqli_query($conn, $modules_query);
    while ($m = mysqli_fetch_assoc($temp_modules_result)) {
        $module_ids[] = $m['id'];
    }
    
    if (!empty($module_ids)) {
        $module_ids_str = implode(',', $module_ids);
        
        // Fetch materials
        $materials_query = "SELECT id, module_id, title, content_type, material_order
                           FROM learning_materials WHERE module_id IN ($module_ids_str) ORDER BY module_id, material_order ASC";
        $materials_result = mysqli_query($conn, $materials_query);
        while ($material = mysqli_fetch_assoc($materials_result)) {
            $materials_by_module[$material['module_id']][] = $material;
        }
        
        // Fetch quizzes
        $quizzes_query = "SELECT id, module_id, title FROM quizzes WHERE module_id IN ($module_ids_str)";
        $quizzes_result = mysqli_query($conn, $quizzes_query);
        while ($quiz = mysqli_fetch_assoc($quizzes_result)) {
            $quizzes_by_module[$quiz['module_id']] = $quiz;
        }
        
        mysqli_data_seek($modules_result, 0); 
    }
}

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-brand-blue transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 dark:text-white"><?php echo $course_title; ?></span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <i class="fas fa-cubes text-purple-600"></i>
                        Module Management
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Define the structure and content of your course</p>
                </div>
                <a href="edit_course.php?course_id=<?php echo $course_id; ?>" 
                   class="inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium px-4 py-2 rounded-xl transition-all">
                    <i class="fas fa-edit"></i> Edit Course Details
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-green-700 dark:text-green-300"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <span class="text-red-700 dark:text-red-300"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Module Form -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-purple-600"></i>
                        Add New Module
                    </h2>
                    
                    <form action="module_setup.php?course_id=<?php echo $course_id; ?>" method="POST" class="space-y-4">
                        <div>
                            <label for="module_title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Module Title</label>
                            <input type="text" id="module_title" name="module_title" required
                                   placeholder="e.g., Introduction to Python"
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                        </div>

                        <div>
                            <label for="module_order" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Order/Sequence</label>
                            <input type="number" id="module_order" name="module_order" min="1" required
                                   placeholder="1"
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                        </div>

                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i> Add Module
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modules List -->
            <div class="lg:col-span-2">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-list-ol text-purple-600"></i>
                    Course Modules
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(<?php echo mysqli_num_rows($modules_result); ?>)</span>
                </h2>
                
                <?php if (mysqli_num_rows($modules_result) > 0): ?>
                    <div class="space-y-4">
                        <?php while ($row = mysqli_fetch_assoc($modules_result)): 
                            $module_id = $row['id'];
                            $has_quiz = isset($quizzes_by_module[$module_id]);
                            $material_count = isset($materials_by_module[$module_id]) ? count($materials_by_module[$module_id]) : 0;
                        ?>
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <!-- Module Header -->
                                <div class="p-5 flex flex-wrap items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold text-lg">
                                            <?php echo $row['module_order']; ?>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-white text-lg">
                                                <?php echo htmlspecialchars($row['title']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo $material_count; ?> materials
                                                <?php if ($has_quiz): ?>
                                                    • <span class="text-purple-600 dark:text-purple-400">Quiz included</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <button onclick="toggleMaterialForm('<?php echo $module_id; ?>')" 
                                                class="bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 font-medium px-3 py-2 rounded-lg transition-all text-sm flex items-center gap-1">
                                            <i class="fas fa-plus"></i> Material
                                        </button>
                                        <a href="add_quiz.php?module_id=<?php echo $module_id; ?>"
                                           class="bg-purple-100 dark:bg-purple-900/30 hover:bg-purple-200 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-400 font-medium px-3 py-2 rounded-lg transition-all text-sm flex items-center gap-1">
                                            <i class="fas fa-question-circle"></i> Quiz
                                        </a>
                                        <a href="edit_module.php?module_id=<?php echo $module_id; ?>"
                                           class="p-2 text-gray-500 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 transition-colors" title="Edit Module">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_module.php?module_id=<?php echo $module_id; ?>&course_id=<?php echo $course_id; ?>"
                                           class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors" title="Delete Module"
                                           onclick="return confirm('Are you sure you want to delete this module and ALL its materials?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Materials List -->
                                <?php if (isset($materials_by_module[$module_id]) && count($materials_by_module[$module_id]) > 0): ?>
                                    <div class="border-t border-gray-100 dark:border-gray-700 px-5 py-3 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="space-y-2">
                                            <?php foreach ($materials_by_module[$module_id] as $material): 
                                                $icon = 'fa-file-alt text-blue-500';
                                                if ($material['content_type'] === 'video') $icon = 'fa-play-circle text-red-500';
                                                elseif ($material['content_type'] === 'link') $icon = 'fa-link text-green-500';
                                            ?>
                                                <div class="flex items-center justify-between py-2 px-3 bg-white dark:bg-gray-800 rounded-lg">
                                                    <div class="flex items-center gap-3">
                                                        <i class="fas <?php echo $icon; ?>"></i>
                                                        <span class="text-gray-700 dark:text-gray-300 text-sm"><?php echo htmlspecialchars($material['title']); ?></span>
                                                        <span class="text-xs text-gray-400 uppercase"><?php echo $material['content_type']; ?></span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <a href="edit_material.php?material_id=<?php echo $material['id']; ?>" class="text-xs text-purple-600 hover:text-purple-800 dark:text-purple-400">Edit</a>
                                                        <a href="delete_material.php?material_id=<?php echo $material['id']; ?>&course_id=<?php echo $course_id; ?>" 
                                                           class="text-xs text-red-500 hover:text-red-700" onclick="return confirm('Delete this material?');">Delete</a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Quiz Info -->
                                <?php if ($has_quiz): ?>
                                    <div class="border-t border-gray-100 dark:border-gray-700 px-5 py-3 bg-purple-50 dark:bg-purple-900/20">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-clipboard-check text-purple-600 dark:text-purple-400"></i>
                                                <span class="text-purple-700 dark:text-purple-300 font-medium"><?php echo htmlspecialchars($quizzes_by_module[$module_id]['title']); ?></span>
                                            </div>
                                            <a href="edit_quiz.php?quiz_id=<?php echo $quizzes_by_module[$module_id]['id']; ?>" 
                                               class="text-xs text-purple-600 hover:text-purple-800 dark:text-purple-400 font-medium">Edit Quiz</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Add Material Form (Hidden) -->
                                <div id="material_form_<?php echo $module_id; ?>" class="hidden border-t border-gray-100 dark:border-gray-700 p-5 bg-gray-50 dark:bg-gray-700/30">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Add Material to this Module</h4>
                                    <form action="material_handler.php" method="POST" class="space-y-4">
                                        <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Title</label>
                                                <input type="text" name="material_title" required 
                                                       class="w-full px-3 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500">
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Type</label>
                                                    <select name="content_type" required class="w-full px-3 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                        <option value="reading">Reading</option>
                                                        <option value="video">Video</option>
                                                        <option value="link">Link</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Order</label>
                                                    <input type="number" name="material_order" min="1" required 
                                                           class="w-full px-3 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">URL/Content</label>
                                            <input type="text" name="content_url" placeholder="https://..."
                                                   class="w-full px-3 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </div>

                                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-all">
                                            Save Material
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700">
                        <div class="w-20 h-20 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-cube text-3xl text-amber-500"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">No Modules Yet</h3>
                        <p class="text-gray-500 dark:text-gray-400">Start by adding your first module using the form on the left.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-10 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-purple-600 dark:text-gray-400 font-medium inline-flex items-center gap-2 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
function toggleMaterialForm(moduleId) {
    const form = document.getElementById('material_form_' + moduleId);
    form.classList.toggle('hidden');
}
</script>

<?php 
mysqli_close($conn);
include("../footer.php"); 
?>