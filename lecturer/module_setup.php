<?php
session_start();
include("../header.php");
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
// Note: Material submission is moved to material_handler.php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['module_title'])) {
    
    $module_title = mysqli_real_escape_string($conn, $_POST['module_title']);
    $module_order = (int)$_POST['module_order'];

    $insert_query = "INSERT INTO modules (course_id, title, module_order) 
                     VALUES ('$course_id', '$module_title', '$module_order')";

    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "Module '{$module_title}' added successfully.";
        // Redirect to clear POST data
        header("Location: module_setup.php?course_id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add module: " . mysqli_error($conn);
    }
}

// --- 4. Fetch Existing Modules ---
$modules_query = "SELECT id, title, module_order FROM modules WHERE course_id = '$course_id' ORDER BY module_order ASC";
$modules_result = mysqli_query($conn, $modules_query);

// --- 5. Fetch Learning Materials and Organize by Module ID (NEW) ---
$materials_by_module = [];
if (mysqli_num_rows($modules_result) > 0) {
    // Collect all module IDs for an efficient single query
    $module_ids = [];
    $temp_modules_result = mysqli_query($conn, $modules_query); // Re-run query to process list again
    while ($m = mysqli_fetch_assoc($temp_modules_result)) {
        $module_ids[] = $m['id'];
    }
    
    if (!empty($module_ids)) {
        $module_ids_str = implode(',', $module_ids);
        
        $materials_query = "
            SELECT 
                id, module_id, title, content_type, material_order
            FROM 
                learning_materials
            WHERE 
                module_id IN ($module_ids_str)
            ORDER BY 
                module_id, material_order ASC";

        $materials_result = mysqli_query($conn, $materials_query);

        // Organize materials by module_id for easy display
        while ($material = mysqli_fetch_assoc($materials_result)) {
            $materials_by_module[$material['module_id']][] = $material;
        }
        // Reset pointer for main modules loop
        mysqli_data_seek($modules_result, 0); 
    }
}

?>

<body>
    <div class="container mx-auto p-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Module Management: <?php echo $course_title; ?></h1>
            <p class="text-md text-gray-600">Define the lessons and structure of your course, including learning materials.</p>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-1 p-6 bg-white rounded-lg shadow-xl h-fit">
                <h2 class="text-xl font-semibold mb-4 text-blue-600">Add New Module</h2>
                <form action="module_setup.php?course_id=<?php echo $course_id; ?>" method="POST">

                    <div class="mb-4">
                        <label for="module_title" class="block text-gray-700 font-semibold mb-2">Module Title:</label>
                        <input type="text" id="module_title" name="module_title" required
                            class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <div class="mb-6">
                        <label for="module_order" class="block text-gray-700 font-semibold mb-2">Order/Sequence (e.g.,
                            1, 2, 3):</label>
                        <input type="number" id="module_order" name="module_order" min="1" required
                            class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                        Save Module
                    </button>
                </form>
            </div>

            <div class="md:col-span-2">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Existing Course Structure</h2>
                <?php if (mysqli_num_rows($modules_result) > 0): ?>
                <ul class="space-y-6">
                    <?php while ($row = mysqli_fetch_assoc($modules_result)): 
                        $module_id = $row['id']; 
                    ?>
                    <li class="p-4 bg-white border rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-xl font-bold text-gray-800">
                                <?php echo $row['module_order']; ?>. <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                            <div class="space-x-2 flex items-center">
                                <button onclick="document.getElementById('material_form_<?php echo $module_id; ?>').classList.toggle('hidden')" 
                                    class="text-sm bg-green-600 text-white py-1 px-3 rounded hover:bg-green-700 transition">
                                    + Add Material
                                </button>
                                <a href="edit_module.php?module_id=<?php echo $module_id; ?>"
                                    class="text-sm text-purple-600 hover:text-purple-800" title="Edit Module"><i class="fas fa-edit"></i></a>
                                <a href="delete_module.php?module_id=<?php echo $module_id; ?>&course_id=<?php echo $course_id; ?>"
                                    class="text-sm text-red-600 hover:text-red-800" title="Delete Module"
                                    onclick="return confirm('Are you sure you want to delete this module and ALL its materials?');"><i
                                        class="fas fa-trash"></i></a>
                            </div>
                        </div>

                        <ul class="ml-4 border-l-2 border-gray-200 pl-4 space-y-2">
                            <?php 
                            // Display the materials associated with this module
                            if (isset($materials_by_module[$module_id])):
                                foreach ($materials_by_module[$module_id] as $material):
                            ?>
                            <li class="flex justify-between items-center text-gray-700 text-base py-1">
                                <span class="font-normal">
                                    <span class="font-mono text-sm mr-2">[<?php echo strtoupper(substr($material['content_type'], 0, 1)); ?>]</span> 
                                    <?php echo $material['material_order']; ?>. 
                                    <?php echo htmlspecialchars($material['title']); ?>
                                </span>
                                <div class="space-x-2">
                                    <a href="edit_material.php?material_id=<?php echo $material['id']; ?>"
                                        class="text-xs text-purple-400 hover:text-purple-600">Edit</a>
                                    <a href="delete_material.php?material_id=<?php echo $material['id']; ?>&course_id=<?php echo $course_id; ?>"
                                        class="text-xs text-red-400 hover:text-red-600"
                                        onclick="return confirm('Delete this material?');">Delete</a>
                                </div>
                            </li>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <li class="text-sm text-gray-500 py-1">No materials in this module yet.</li>
                            <?php endif; ?>
                        </ul>
                        
                        <div id="material_form_<?php echo $module_id; ?>" class="mt-4 p-4 bg-gray-100 rounded-lg border hidden">
                            <h4 class="font-semibold mb-3 border-b pb-1 text-md text-indigo-700">Add Material to Module <?php echo $row['module_order']; ?></h4>
                            <form action="material_handler.php" method="POST">
                                <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="mb-2 col-span-2">
                                        <label class="block text-xs font-semibold mb-1">Material Title:</label>
                                        <input type="text" name="material_title" required class="w-full px-2 py-1 border rounded-md text-sm">
                                    </div>
                                    <div class="mb-2">
                                        <label class="block text-xs font-semibold mb-1">Type:</label>
                                        <select name="content_type" required class="w-full px-2 py-1 border rounded-md text-sm">
                                            <option value="reading">Reading</option>
                                            <option value="video">Video</option>
                                            <option value="quiz">Quiz</option>
                                            <option value="link">External Link</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="block text-xs font-semibold mb-1">Order:</label>
                                        <input type="number" name="material_order" min="1" required class="w-full px-2 py-1 border rounded-md text-sm">
                                    </div>
                                </div>
                                
                                <div class="mb-4 mt-2">
                                    <label class="block text-xs font-semibold mb-1">URL/Content (Video link, file path, etc.):</label>
                                    <input type="text" name="content_url" class="w-full px-2 py-1 border rounded-md text-sm">
                                </div>

                                <button type="submit" class="bg-indigo-600 text-white text-sm py-1 px-3 rounded hover:bg-indigo-700 transition">Save Material</button>
                            </form>
                        </div>

                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php else: ?>
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 rounded-lg">
                    No modules defined yet. Start adding the first one!
                </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>

<?php mysqli_close($conn); ?>
<?php include("../footer.php"); ?>