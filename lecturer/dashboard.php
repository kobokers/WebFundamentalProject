<?php
session_start();
include("../connection.php");

// --- 1. Authentication and Authorization Check ---
// Redirect if user is not logged in or is not a lecturer
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

/* This query retrieves all courses linked to the current lecturer. */
$sql_courses = "
  SELECT
    c.id AS course_id,
    c.title AS course_title,
    c.level,
    c.language,
    c.fee
  FROM
    courses c
  WHERE
    c.lecturer_id = '{$lecturer_id}'
  ORDER BY
    c.title ASC";

$result = mysqli_query($conn, $sql_courses);

// NOW include header after authentication check
include("../header.php");
?>

<body>
    <div class="container mx-auto p-8">
        <header class="mb-8 flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <?php if (!empty($profile_picture)): ?>
                <img src="../uploads/avatars/<?php echo htmlspecialchars($profile_picture); ?>" 
                     alt="Avatar" class="w-16 h-16 rounded-full object-cover border-4 border-purple-200 dark:border-purple-700 flex-shrink-0">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center border-4 border-purple-200 dark:border-purple-700 flex-shrink-0">
                    <i class="fas fa-user text-2xl text-purple-400 dark:text-purple-500"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-purple-800 dark:text-purple-300">Lecturer Dashboard</h1>
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400">Welcome, <b><?php echo htmlspecialchars($lecturer_name); ?>!</b> Manage your content here.</p>
            </div>
        </header>

        <hr class="mb-8 border-gray-300 dark:border-gray-700">

        <section id="actions"
            class="mb-10 p-6 bg-purple-50 dark:bg-purple-900 border-l-4 border-purple-500 dark:border-purple-400 rounded-lg shadow-md flex justify-between items-center transition-colors duration-200">
            <h2 class="text-xl font-semibold text-purple-700 dark:text-purple-200">Ready to create new content?</h2>
            <a href="add_course_form.php"
                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-plus-circle mr-2"></i> Register New Course
            </a>
        </section>

        <section id="courses-taught">
            <h2 class="text-2xl font-semibold mb-4 flex items-center text-gray-800 dark:text-white"><i class="fas fa-chalkboard-teacher mr-2"></i> My
                Registered Courses</h2>

            <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 shadow-md rounded-lg transition-colors duration-200">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Course Title</th>
                            <th class="py-3 px-6 text-left">Level</th>
                            <th class="py-3 px-6 text-center">Fee</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                            <th class="py-3 px-6 text-center">Enrollments</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 dark:text-gray-400 text-sm font-light">
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="py-3 px-6 text-left whitespace-nowrap font-medium">
                                <?php echo htmlspecialchars($row['course_title']); ?>
                            </td>
                            <td class="py-3 px-6 text-left">
                                <?php echo htmlspecialchars($row['level']); ?>
                            </td>
                            <td class="py-3 px-6 text-center">
                                $<?php echo number_format($row['fee'], 2); ?>
                            </td>
                            <td class="py-3 px-6 text-center space-x-2">
                                <a href="course_discussion.php?course_id=<?php echo $row['course_id']; ?>"
                                    class="text-purple-500 hover:text-purple-700 font-medium text-xs">Discussion</a>
                                <a href="module_setup.php?course_id=<?php echo $row['course_id']; ?>"
                                    class="text-blue-500 hover:text-blue-700 font-medium text-xs">Manage Modules</a>
                                <a href="edit_course.php?course_id=<?php echo $row['course_id']; ?>"
                                    class="text-gray-500 hover:text-gray-700 font-medium text-xs dark:hover:text-white">Edit Details</a>
                            </td>
                            <td class="py-3 px-10 text-center">
                                <a href="view_enrollment.php?course_id=<?php echo $row['course_id']; ?>"
                                    class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded text-xs transition duration-200 ml-2">View
                                    Students</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-500 dark:border-yellow-400 text-yellow-700 dark:text-yellow-200 transition-colors duration-200">
                <p class="font-bold">No Courses Found!</p>
                <p>You have not registered any courses yet. Click the <b>Register New Course</b> button above to get
                    started.</p>
            </div>
            <?php endif; ?>
        </section><br><br>

        <section id="account-actions">
            <h2 class="text-2xl font-semibold mb-4 flex items-center text-gray-800 dark:text-white"><i class="fas fa-user-cog mr-2"></i> Account
                Actions</h2>
            <div class="flex space-x-4">
                <a href="edit_profile.php"
                    class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold py-2 px-4 rounded transition duration-200">Edit
                    Profile</a>
            </div>
        </section><br><br>

        <?php mysqli_close($conn); ?>

    </div>
</body>
<?php include("../footer.php"); ?>

</html>