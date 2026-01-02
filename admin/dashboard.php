<?php
session_start();
include("../connection.php");
include("../header.php");

// --- 1. Security Check ---
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '../auth/login.php';</script>";
    exit(); 
}

if ($_SESSION['user_role'] !== 'admin') {
    echo "<script>window.location.href = '../index.php';</script>";
    exit();
}

// Check for status parameter
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $alert_message = '';
    $alert_type = 'success';

    if ($status === 'success') {
        $alert_message = 'User deleted successfully.';
    } elseif ($status === 'error') {
        $alert_message = 'Error deleting user.';
        $alert_type = 'error';
    } elseif ($status === 'no_id') {
        $alert_message = 'No user specified for deletion.';
        $alert_type = 'warning';
    }

    if ($alert_message) {
        // Javascript alert and clean URL redirect
        echo "<script>
            alert('" . addslashes($alert_message) . "');
            window.history.replaceState(null, null, window.location.pathname);
        </script>";
    }
}

// --- Pagination and Search for Students ---
$students_per_page = 10;
$student_page = isset($_GET['student_page']) ? max(1, (int)$_GET['student_page']) : 1;
$student_search = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';
$student_offset = ($student_page - 1) * $students_per_page;

$student_where = "role = 'student'";
if (!empty($student_search)) {
    $student_search_safe = $conn->real_escape_string($student_search);
    $student_where .= " AND name LIKE '%{$student_search_safe}%'";
}

$count_sql = "SELECT COUNT(*) as total FROM users WHERE {$student_where}";
$count_result = $conn->query($count_sql);
$total_students = $count_result->fetch_assoc()['total'];
$total_student_pages = ceil($total_students / $students_per_page);

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
$lecturer_page = isset($_GET['lecturer_page']) ? max(1, (int)$_GET['lecturer_page']) : 1;
$lecturer_search = isset($_GET['lecturer_search']) ? trim($_GET['lecturer_search']) : '';
$lecturer_offset = ($lecturer_page - 1) * $lecturers_per_page;

$lecturer_where = "role = 'lecturer'";
if (!empty($lecturer_search)) {
    $lecturer_search_safe = $conn->real_escape_string($lecturer_search);
    $lecturer_where .= " AND name LIKE '%{$lecturer_search_safe}%'";
}

$count_sql_lect = "SELECT COUNT(*) as total FROM users WHERE {$lecturer_where}";
$count_result_lect = $conn->query($count_sql_lect);
$total_lecturers = $count_result_lect->fetch_assoc()['total'];
$total_lecturer_pages = ceil($total_lecturers / $lecturers_per_page);

$users_lect = [];
$sql_state = "SELECT id, name, email, role, status FROM users WHERE {$lecturer_where} ORDER BY name ASC LIMIT {$lecturers_per_page} OFFSET {$lecturer_offset}";
$result_state = $conn->query($sql_state);
if ($result_state && $result_state->num_rows > 0) {
    while ($row = $result_state->fetch_assoc()) {
        $users_lect[] = $row;
    }
}
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white">
        <div class="container mx-auto px-4 lg:px-8 py-10">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-user-shield text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Admin Dashboard</h1>
                    <p class="text-gray-400">Manage users, roles, and system settings</p>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 flex items-center gap-4 border border-white/5">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-400">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold"><?php echo $total_students; ?></div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Students</div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 flex items-center gap-4 border border-white/5">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center text-purple-400">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold"><?php echo $total_lecturers; ?></div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Lecturers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8 space-y-12">
        
        <!-- Students Section -->
        <section>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-graduate text-blue-600"></i>
                        Manage Students
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">View and manage student accounts</p>
                </div>
                
                <!-- Student Search -->
                <form method="GET" class="flex w-full md:w-auto gap-2">
                    <div class="relative flex-1 md:w-64">
                        <input type="text" name="student_search" 
                               value="<?php echo htmlspecialchars($student_search); ?>" 
                               placeholder="Search students..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition shadow-md">
                        Search
                    </button>
                    <?php if (!empty($student_search)): ?>
                        <a href="?student_page=1" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-medium transition hover:bg-gray-300 dark:hover:bg-gray-600">
                            Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <th class="px-6 py-4 font-semibold">ID</th>
                                <th class="px-6 py-4 font-semibold">Name</th>
                                <th class="px-6 py-4 font-semibold">Email</th>
                                <th class="px-6 py-4 font-semibold text-center">Status</th>
                                <th class="px-6 py-4 font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-sm">#<?php echo $user['id']; ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
                                                    <?php echo substr($user['name'], 0, 1); ?>
                                                </div>
                                                <span class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                <?php echo ($user['status'] == 'active') 
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' 
                                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if($user['status'] !== 'active'): ?>
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors" title="Activate User">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="javascript:void(0)" 
                                                   onclick="openDeleteModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>')" 
                                                   class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete User">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No students found matching your search.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Student Pagination -->
                <?php if ($total_student_pages > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-center">
                        <div class="flex gap-2">
                            <?php if ($student_page > 1): ?>
                                <a href="?student_page=<?php echo $student_page - 1; ?>&student_search=<?php echo urlencode($student_search); ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_student_pages; $i++): ?>
                                <a href="?student_page=<?php echo $i; ?>&student_search=<?php echo urlencode($student_search); ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-lg border <?php echo ($i == $student_page) ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'; ?> transition-colors font-semibold">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($student_page < $total_student_pages): ?>
                                <a href="?student_page=<?php echo $student_page + 1; ?>&student_search=<?php echo urlencode($student_search); ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Lecturers Section -->
        <section>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-chalkboard-teacher text-purple-600"></i>
                        Manage Lecturers
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">View and manage lecturer accounts</p>
                </div>
                
                <!-- Lecturer Search -->
                <form method="GET" class="flex w-full md:w-auto gap-2">
                    <div class="relative flex-1 md:w-64">
                        <input type="text" name="lecturer_search" 
                               value="<?php echo htmlspecialchars($lecturer_search); ?>" 
                               placeholder="Search lecturers..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-medium transition shadow-md">
                        Search
                    </button>
                    <?php if (!empty($lecturer_search)): ?>
                        <a href="?lecturer_page=1" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-medium transition hover:bg-gray-300 dark:hover:bg-gray-600">
                            Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <th class="px-6 py-4 font-semibold">ID</th>
                                <th class="px-6 py-4 font-semibold">Name</th>
                                <th class="px-6 py-4 font-semibold">Email</th>
                                <th class="px-6 py-4 font-semibold text-center">Status</th>
                                <th class="px-6 py-4 font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            <?php if (count($users_lect) > 0): ?>
                                <?php foreach ($users_lect as $lecturer): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-sm">#<?php echo $lecturer['id']; ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold text-xs">
                                                    <?php echo substr($lecturer['name'], 0, 1); ?>
                                                </div>
                                                <span class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($lecturer['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 text-sm"><?php echo htmlspecialchars($lecturer['email']); ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                <?php echo ($lecturer['status'] == 'active') 
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' 
                                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'; ?>">
                                                <?php echo ucfirst($lecturer['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if($lecturer['status'] !== 'active'): ?>
                                                <a href="edit_user.php?id=<?php echo $lecturer['id']; ?>" class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors" title="Activate User">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="javascript:void(0)" 
                                                   onclick="openDeleteModal(<?php echo $lecturer['id']; ?>, '<?php echo addslashes($lecturer['name']); ?>')" 
                                                   class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete User">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No lecturers found matching your search.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Lecturer Pagination -->
                <?php if ($total_lecturer_pages > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-center">
                        <div class="flex gap-2">
                            <?php if ($lecturer_page > 1): ?>
                                <a href="?lecturer_page=<?php echo $lecturer_page - 1; ?>&lecturer_search=<?php echo urlencode($lecturer_search); ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_lecturer_pages; $i++): ?>
                                <a href="?lecturer_page=<?php echo $i; ?>&lecturer_search=<?php echo urlencode($lecturer_search); ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-lg border <?php echo ($i == $lecturer_page) ? 'bg-purple-600 border-purple-600 text-white' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'; ?> transition-colors font-semibold">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($lecturer_page < $total_lecturer_pages): ?>
                                <a href="?lecturer_page=<?php echo $lecturer_page + 1; ?>&lecturer_search=<?php echo urlencode($lecturer_search); ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php include("../footer.php"); ?>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>

        <!-- Modal panel -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Delete User
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                This action cannot be undone. This will permanently delete the user <span id="deleteUserName" class="font-bold text-gray-900 dark:text-white"></span> and all associated data.
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Please type <span class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded select-all" id="expectedUsername"></span> to confirm.
                            </p>
                            <input type="text" id="confirmUsernameInput" 
                                   class="mt-3 w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   placeholder="Type the username here">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmDeleteBtn" disabled
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Delete
                </button>
                <button type="button" onclick="closeDeleteModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let userToDeleteId = null;
let expectedName = '';

function openDeleteModal(userId, userName) {
    userToDeleteId = userId;
    expectedName = userName;
    
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('expectedUsername').textContent = userName;
    document.getElementById('confirmUsernameInput').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;
    document.getElementById('deleteModal').classList.remove('hidden');
    // small delay to let the modal show up before focusing
    setTimeout(() => {
        document.getElementById('confirmUsernameInput').focus();
    }, 100);
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    userToDeleteId = null;
    expectedName = '';
}

document.getElementById('confirmUsernameInput').addEventListener('input', function(e) {
    const btn = document.getElementById('confirmDeleteBtn');
    if (this.value === expectedName) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!document.getElementById('confirmDeleteBtn').disabled && userToDeleteId) {
        window.location.href = 'delete_user.php?id=' + userToDeleteId;
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>