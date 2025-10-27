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

//check for status parameter (bugged)
// if (isset($_GET['status'])) {
//     $status = $_GET['status'];
//     if ($status === 'success') {
//         echo "<script>alert('User deleted successfully.');</script>";
//     } elseif ($status === 'error') {
//         echo "<script>alert('Error deleting user.');</script>";
//     } elseif ($status === 'no_id') {
//         echo "<script>alert('No user specified for deletion.');</script>";
//     }
// }

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

// --- Fetch Students ---
$users = [];
$sql = "SELECT id, name, email, role, status FROM users WHERE role = 'student' ORDER BY name ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    echo "<p><b>No student users found.</p></b>";
}

// --- Fetch Lecturers ---
$users_lect = [];
$sql_state = "SELECT id, name, email, role, status FROM users WHERE role = 'lecturer' ORDER BY name ASC";
$result_state = $conn->query($sql_state);
if ($result_state && $result_state->num_rows > 0) {
    while ($row = $result_state->fetch_assoc()) {
        $users_lect[] = $row;
    }
} else {
    echo "<p><b>No lecturer users found.</p></b>";
}
?>

<div class="container mx-auto p-4">
    <?php
    echo "Welcome, <strong>" . htmlspecialchars($_SESSION['user_name']) . "</strong>";
    echo "<br>";
    echo "Your role is <strong>" . htmlspecialchars($_SESSION['user_role']) . "</strong>";
    echo '<br>';
    echo "Welcome Master Controll User";
    ?>
</div>

<body>
    <main class="container mx-auto p-4">
        <h1 class="text-3xl font-extrabold mb-6 text-gray-800">Admin Dashboard</h1>
        <p class="mb-8 text-gray-600">This is the admin dashboard. Here you can manage users and site settings.</p>

        <section class="mb-12">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-2xl font-semibold mb-4 text-blue-700">Manage Students</h2>

                <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-16 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="w-56 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="w-32 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['id']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700"><?= htmlspecialchars($user['name']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700"><?= htmlspecialchars($user['email']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
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
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No student users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section>
            <div class="max-w-6xl mx-auto">
                <h2 class="text-2xl font-semibold mb-4 text-blue-700">Manage Lecturers</h2>

                <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-16 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="w-56 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="w-24 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="w-32 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($users_lect) > 0): ?>
                                <?php foreach ($users_lect as $lecturer): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($lecturer['id']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700"><?= htmlspecialchars($lecturer['name']) ?></td>
                                        <td class="px-6 py-4 truncate text-sm text-gray-700"><?= htmlspecialchars($lecturer['email']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
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
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No lecturer users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
<br>
<br>
<br>
<?php include("../footer.php"); ?>