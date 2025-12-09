<?php
session_start();
include("../header.php");
include("../connection.php");

// --- 1. Authentication Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

// Set user variables from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch profile picture
$profile_query = "SELECT profile_picture FROM users WHERE id = '$user_id' LIMIT 1";
$profile_result = mysqli_query($conn, $profile_query);
$profile_data = mysqli_fetch_assoc($profile_result);
$profile_picture = $profile_data['profile_picture'] ?? null;

// --- 2. SQL Query to Fetch Enrolled Courses and Progress  ---
$sql_enrolled_courses = "
    SELECT 
        c.id AS course_id,
        c.title AS course_title,
        u.name AS lecturer_name,
        e.enroll_date,
        e.payment_status,
        c.fee,
        -- Calculates progress based on completed modules vs. total modules for the course
        COALESCE(
            (SELECT ROUND((COUNT(p.id) * 100.0) / (SELECT COUNT(m.id) FROM modules m WHERE m.course_id = c.id))
             FROM progress p
             JOIN modules m ON p.module_id = m.id
             WHERE m.course_id = c.id AND p.user_id = '{$user_id}' AND p.status = 'completed'), 
            0
        ) AS progress_percentage
    FROM 
        enrollment e
    JOIN 
        courses c ON e.course_id = c.id
    JOIN 
        users u ON c.lecturer_id = u.id
    WHERE 
        e.user_id = '{$user_id}'
    ORDER BY 
        e.enroll_date DESC";

$result = mysqli_query($conn, $sql_enrolled_courses);
?>

<body>
    <div class="container mx-auto p-4">
        <header class="mb-8 flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <?php if (!empty($profile_picture)): ?>
                <img src="../uploads/avatars/<?php echo htmlspecialchars($profile_picture); ?>" 
                     alt="Avatar" class="w-16 h-16 rounded-full object-cover border-4 border-blue-200 dark:border-blue-700 flex-shrink-0">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center border-4 border-blue-200 dark:border-blue-700 flex-shrink-0">
                    <i class="fas fa-user text-2xl text-blue-400 dark:text-blue-500"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-blue-800 dark:text-blue-300">Hello, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400">Your Student Dashboard</p>
            </div>
        </header>

        <hr class="mb-8 border-gray-300 dark:border-gray-700">

        <section id="enrolled-courses" class="mb-10">
            <h2 class="text-2xl font-semibold mb-4 flex items-center text-gray-800 dark:text-white"><i class="fas fa-book-open mr-2"></i> My Learning
                Path</h2>

            <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 shadow-md rounded-lg transition-colors duration-200">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Course Title</th>
                            <th class="py-3 px-6 text-left">Instructor</th>
                            <th class="py-3 px-6 text-center">Progress</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 dark:text-gray-400 text-sm font-light">
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="py-3 px-6 text-left whitespace-nowrap font-medium">
                                <?php echo htmlspecialchars($row['course_title']); ?>
                            </td>
                            <td class="py-3 px-6 text-left">
                                <?php echo htmlspecialchars($row['lecturer_name']); ?>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                    <div class="bg-green-600 h-2.5 rounded-full"
                                        style="width: <?php echo $row['progress_percentage']; ?>%"></div>
                                </div>
                                <span class="text-xs mt-1 block"><?php echo (int)$row['progress_percentage']; ?>%
                                    Complete</span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <?php
                                        $status_class = ($row['payment_status'] == 'paid') ? 'bg-green-200 text-green-600' : 'bg-yellow-200 text-yellow-600';
                                        ?>
                                <span class="py-1 px-3 rounded-full text-xs font-semibold <?php echo $status_class; ?>">
                                    <?php echo ucfirst(htmlspecialchars($row['payment_status'])); ?>
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex flex-col space-y-2 items-center">
                                    <?php
                                            $progress = (int)$row['progress_percentage'];
                                            $is_paid = ($row['payment_status'] == 'paid');

                                            // --- 1. Primary Action Link (Continue/Certificate/Pay Now) ---
                                            if ($is_paid && $progress === 100) {
                                                // COURSE COMPLETE: SHOW CERTIFICATE LINK
                                                $primary_link = '../auth/student_actions.php?action=certificate&course_id=' . $row['course_id'];
                                                $primary_class = 'bg-purple-600 hover:bg-purple-700';
                                                $primary_text = 'View Certificate';
                                            } elseif ($is_paid) {
                                                // COURSE IN PROGRESS: SHOW CONTINUE LINK
                                                $primary_link = 'course_view.php?id=' . $row['course_id'];
                                                $primary_class = 'bg-blue-500 hover:bg-blue-600';
                                                $primary_text = 'Continue Course';
                                            } else {
                                                // PENDING PAYMENT: SHOW PAY NOW LINK
                                                $primary_link = '../auth/payment.php?course_id=' . $row['course_id'];
                                                $primary_class = 'bg-red-500 hover:bg-red-600';
                                                $primary_text = 'Pay Now ($' . number_format($row['fee'], 2) . ')';
                                            }
                                            ?>
                                    <a href="<?php echo $primary_link; ?>"
                                        class="<?php echo $primary_class; ?> text-white font-bold py-1 px-3 w-full rounded text-xs transition duration-200">
                                        <?php echo $primary_text; ?>
                                    </a>

                                    <a href="course_discussion.php?course_id=<?php echo $row['course_id']; ?>"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 w-full rounded text-xs transition duration-200 mt-1">
                                        <i class="fas fa-comments mr-1"></i> Discussion
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-4 bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-200 transition-colors duration-200">
                <p class="font-bold">No Courses Enrolled!</p>
                <p>You haven't signed up for any courses yet. <a href="../auth/catalog.php"
                        class="text-blue-500 hover:text-blue-700 font-medium">Browse our courses</a> to get started.</p>
            </div>
            <?php endif; ?>
        </section>

        <section id="account-actions">
            <h2 class="text-2xl font-semibold mb-4 flex items-center text-gray-800 dark:text-white"><i class="fas fa-user-cog mr-2"></i> Account
                Actions</h2>
            <div class="flex space-x-4">
                <a href="../auth/profile_edit.php"
                    class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold py-2 px-4 rounded transition duration-200">Edit
                    Profile</a>
                <a href="../auth/catalog.php"
                    class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold py-2 px-4 rounded transition duration-200">Browse
                    Catalog</a>
            </div>
        </section><br><br>

        <?php mysqli_close($conn); ?>

    </div>
</body>
<?php include("../footer.php"); ?>
</html>