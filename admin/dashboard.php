<?php
session_start();
include("../connection.php");
include("../header.php");

// --- 1. Security Check ---
if (!isset($_SESSION['user_id'])) {
    echo "<script>";
    echo "alert('Please log in.');";
    echo "window.location.href = '../auth/login.php';";
    echo "</script>";
    exit(); // Stop execution if not logged in
}

if ($_SESSION['user_role'] !== 'admin') {
    echo "<script>";
    echo "alert('Access denied. You are not authorized to view this page.');";
    echo "window.location.href = '../index.php';"; // Redirect to a different page
    echo "</script>";
    exit();
}

//check for status parameter
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $alert_message = '';

    if ($status === 'success') {
        $alert_message = 'User deleted successfully.';
    } elseif ($status === 'error') {
        $alert_message = 'Error deleting user.';
    } elseif ($status === 'no_id') {
        $alert_message = 'No user specified for deletion.';
    }

    if ($alert_message) {
        // Get the clean URL (current page without parameters)
        $clean_url = strtok($_SERVER["REQUEST_URI"], '?');

        // Use JavaScript to display the alert, and then redirect to the clean URL
        echo "<script>";
        // 1. Show the alert
        echo "alert('{$alert_message}');";
        // 2. Redirect to the clean URL to prevent the alert on refresh
        echo "window.location.replace('{$clean_url}');";
        echo "</script>";

        exit();
    }
}

// --- Pagination and Search for Students ---
$students_per_page = 10;
$student_page = isset($_GET['student_page']) ? (int)$_GET['student_page'] : 1;
$student_page = max(1, $student_page); // Ensure page is at least 1
$student_search = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';
$student_offset = ($student_page - 1) * $students_per_page;

// Build student query with search
$student_where = "role = 'student'";
if (!empty($student_search)) {
    $student_search_safe = $conn->real_escape_string($student_search);
    $student_where .= " AND name LIKE '%{$student_search_safe}%'";
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM users WHERE {$student_where}";
$count_result = $conn->query($count_sql);
$total_students = $count_result->fetch_assoc()['total'];
$total_student_pages = ceil($total_students / $students_per_page);

// Fetch students for current page
$users = [];
$sql = "SELECT id, name, email, role, status FROM users WHERE {$student_where} ORDER BY name ASC LIMIT {$students_per_page} OFFSET {$student_offset}";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// --- Pagination and Search for Lecturers ---
$lecturers_per_page = 10;
$lecturer_page = isset($_GET['lecturer_page']) ? (int)$_GET['lecturer_page'] : 1;
$lecturer_page = max(1, $lecturer_page); // Ensure page is at least 1
$lecturer_search = isset($_GET['lecturer_search']) ? trim($_GET['lecturer_search']) : '';
$lecturer_offset = ($lecturer_page - 1) * $lecturers_per_page;

// Build lecturer query with search
$lecturer_where = "role = 'lecturer'";
if (!empty($lecturer_search)) {
    $lecturer_search_safe = $conn->real_escape_string($lecturer_search);
    $lecturer_where .= " AND name LIKE '%{$lecturer_search_safe}%'";
}

// Get total count for pagination
$count_sql_lect = "SELECT COUNT(*) as total FROM users WHERE {$lecturer_where}";
$count_result_lect = $conn->query($count_sql_lect);
$total_lecturers = $count_result_lect->fetch_assoc()['total'];
$total_lecturer_pages = ceil($total_lecturers / $lecturers_per_page);

// Fetch lecturers for current page
$users_lect = [];
$sql_state = "SELECT id, name, email, role, status FROM users WHERE {$lecturer_where} ORDER BY name ASC LIMIT {$lecturers_per_page} OFFSET {$lecturer_offset}";
$result_state = $conn->query($sql_state);
if ($result_state && $result_state->num_rows > 0) {
    while ($row = $result_state->fetch_assoc()) {
        $users_lect[] = $row;
    }
}
?>

<body>
    <main class="container mx-auto p-4">
        <h1 class="text-3xl font-extrabold mb-6 text-gray-800 dark:text-gray-100">Admin Dashboard</h1>
        <p class="mb-8 text-gray-600 dark:text-gray-400">This is the admin dashboard. Here you can manage users and site settings.</p>

        <section class="mb-12">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-2xl font-semibold mb-4 text-blue-700 dark:text-blue-400">Manage Students</h2>

                <!-- Search Box for Students -->
                <div class="mb-4">
                    <form method="GET" action="" class="flex gap-2">
                        <input type="text" 
                               name="student_search" 
                               value="<?= htmlspecialchars($student_search) ?>" 
                               placeholder="Search by name..." 
                               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Search</button>
                        <?php if (!empty($student_search)): ?>
                            <a href="?student_page=1" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="overflow-x-auto shadow border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-16 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="w-56 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="w-32 px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($user['id']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($user['name']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($user['email']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?= htmlspecialchars($user['role']) ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo ($user['status'] == 'active') ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?= htmlspecialchars($user['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm font-medium">
                                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Activate</a>
                                            <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="text-red-600 hover:text-red-900">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No student users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Students -->
                <?php if ($total_student_pages > 1): ?>
                    <div class="mt-4 flex justify-center items-center gap-2">
                        <?php if ($student_page > 1): ?>
                            <a href="?student_page=<?= $student_page - 1 ?><?= !empty($student_search) ? '&student_search=' . urlencode($student_search) : '' ?>" 
                               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_student_pages; $i++): ?>
                            <?php if ($i == $student_page): ?>
                                <span class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?student_page=<?= $i ?><?= !empty($student_search) ? '&student_search=' . urlencode($student_search) : '' ?>" 
                                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($student_page < $total_student_pages): ?>
                            <a href="?student_page=<?= $student_page + 1 ?><?= !empty($student_search) ? '&student_search=' . urlencode($student_search) : '' ?>" 
                               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section>
            <div class="max-w-6xl mx-auto">
                <h2 class="text-2xl font-semibold mb-4 text-blue-700 dark:text-blue-400">Manage Lecturers</h2>

                <!-- Search Box for Lecturers -->
                <div class="mb-4">
                    <form method="GET" action="" class="flex gap-2">
                        <input type="text" 
                               name="lecturer_search" 
                               value="<?= htmlspecialchars($lecturer_search) ?>" 
                               placeholder="Search by name..." 
                               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Search</button>
                        <?php if (!empty($lecturer_search)): ?>
                            <a href="?lecturer_page=1" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="overflow-x-auto shadow border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-16 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="w-56 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="w-32 px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (count($users_lect) > 0): ?>
                                <?php foreach ($users_lect as $lecturer): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($lecturer['id']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($lecturer['name']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($lecturer['email']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?= htmlspecialchars($lecturer['role']) ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo ($lecturer['status'] == 'active') ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?= htmlspecialchars($lecturer['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm font-medium">
                                            <a href="edit_user.php?id=<?= $lecturer['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Activate</a>
                                            <a href="delete_user.php?id=<?= $lecturer['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="text-red-600 hover:text-red-900">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No lecturer users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Lecturers -->
                <?php if ($total_lecturer_pages > 1): ?>
                    <div class="mt-4 flex justify-center items-center gap-2">
                        <?php if ($lecturer_page > 1): ?>
                            <a href="?lecturer_page=<?= $lecturer_page - 1 ?><?= !empty($lecturer_search) ? '&lecturer_search=' . urlencode($lecturer_search) : '' ?>" 
                               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_lecturer_pages; $i++): ?>
                            <?php if ($i == $lecturer_page): ?>
                                <span class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?lecturer_page=<?= $i ?><?= !empty($lecturer_search) ? '&lecturer_search=' . urlencode($lecturer_search) : '' ?>" 
                                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($lecturer_page < $total_lecturer_pages): ?>
                            <a href="?lecturer_page=<?= $lecturer_page + 1 ?><?= !empty($lecturer_search) ? '&lecturer_search=' . urlencode($lecturer_search) : '' ?>" 
                               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
<br>
<br>
<br>
<?php include("../footer.php"); ?>