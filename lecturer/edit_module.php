<?php
session_start();
include("../header.php");
include("../connection.php"); 

// --- 1. Basic Validation ---
// (Authentication checks omitted for brevity but are required)
$module_id = isset($_GET['module_id']) ? $_GET['module_id'] : null;

if (!$module_id || !is_numeric($module_id)) {
    // Error handling
    header("Location: dashboard.php");
    exit;
}

// --- 2. Handle Form Submission (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_module'])) {
    
    $module_title = mysqli_real_escape_string($conn, $_POST['module_title']);
    $module_order = (int)$_POST['module_order'];
    $course_id = (int)$_POST['course_id'];

    // INSECURE SQL Query to UPDATE Module
    $update_query = "UPDATE modules 
                     SET title = '$module_title', module_order = '$module_order' 
                     WHERE id = '$module_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Module updated successfully.";
        header("Location: module_setup.php?course_id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update module: " . mysqli_error($conn);
    }
}

// --- 3. Fetch Existing Module Data (for display in form) ---
$fetch_query = "SELECT course_id, title, module_order FROM modules WHERE id = '$module_id'";
$fetch_result = mysqli_query($conn, $fetch_query);

if (mysqli_num_rows($fetch_result) == 0) {
    $_SESSION['error'] = "Module not found.";
    header("Location: dashboard.php");
    exit;
}

$module_data = mysqli_fetch_assoc($fetch_result);
$course_id = $module_data['course_id']; 
?>

<div class="container mx-auto p-8 max-w-lg">
    <h2 class="text-3xl font-bold mb-6">Edit Module: <?php echo htmlspecialchars($module_data['title']); ?></h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="edit_module.php?module_id=<?php echo $module_id; ?>" method="POST" class="p-6 bg-white rounded-lg shadow-xl">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        
        <div class="mb-4">
            <label for="module_title" class="block text-gray-700 font-semibold mb-2">Module Title:</label>
            <input type="text" id="module_title" name="module_title" value="<?php echo htmlspecialchars($module_data['title']); ?>" required class="w-full px-4 py-2 border rounded-lg">
        </div>
        
        <div class="mb-6">
            <label for="module_order" class="block text-gray-700 font-semibold mb-2">Order/Sequence:</label>
            <input type="number" id="module_order" name="module_order" value="<?php echo $module_data['module_order']; ?>" min="1" required class="w-full px-4 py-2 border rounded-lg">
        </div>

        <button type="submit" name="update_module" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition">
            Save Changes
        </button>
    </form>
    <a href="module_setup.php?course_id=<?php echo $course_id; ?>" class="mt-4 block text-center text-gray-500 hover:text-gray-700">Cancel</a>
</div>
<?php mysqli_close($conn); ?>
<?php include("../footer.php"); ?>