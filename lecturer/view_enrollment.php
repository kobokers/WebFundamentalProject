<?php
session_start();
include("../connection.php"); 
include("../header.php"); 

// --- 1. Authentication and Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    header("Location: ../auth/login.php");
    exit;
}

$lecturer_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course ID.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Verify Lecturer Owns the Course  ---
$course_check_query = "SELECT title FROM courses WHERE id = '{$course_id}' AND lecturer_id = '{$lecturer_id}'";
$course_check_result = mysqli_query($conn, $course_check_query);

if (mysqli_num_rows($course_check_result) == 0) {
    $_SESSION['error'] = "You are not authorized to view the enrollment for this course.";
    header("Location: dashboard.php");
    exit;
}

$course_title = mysqli_fetch_assoc($course_check_result)['title'];

// --- 3. Fetch Enrolled Students ---
$sql_students = "
    SELECT 
        u.name AS student_name,
        u.email AS student_email,
        e.enroll_date,
        e.payment_status,
        -- Calculates progress for each student in the course
        COALESCE(
            (SELECT ROUND((COUNT(p.id) * 100.0) / (SELECT COUNT(m.id) FROM modules m WHERE m.course_id = c.id))
             FROM progress p
             JOIN modules m ON p.module_id = m.id
             WHERE m.course_id = c.id AND p.user_id = u.id AND p.status = 'completed'), 
            0
        ) AS progress_percentage
    FROM 
        enrollment e
    JOIN 
        users u ON e.user_id = u.id
    JOIN
        courses c ON e.course_id = c.id
    WHERE 
        e.course_id = '{$course_id}'
    ORDER BY 
        e.enroll_date ASC";

$result = mysqli_query($conn, $sql_students);

?>

<div class="container mx-auto p-4">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-blue-800 dark:text-blue-300">Enrollment for: <?php echo htmlspecialchars($course_title); ?></h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">Total Students: <?php echo mysqli_num_rows($result); ?></p>
        <a href="dashboard.php" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block">← Back to Dashboard</a>
    </header>

    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 shadow-md rounded-lg">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Student Name</th>
                    <th class="py-3 px-6 text-left">Email</th>
                    <th class="py-3 px-6 text-center">Enrollment Date</th>
                    <th class="py-3 px-6 text-center">Payment Status</th>
                    <th class="py-3 px-6 text-center">Progress</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 dark:text-gray-300 text-sm font-light">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <td class="py-3 px-6 text-left font-medium"><?php echo htmlspecialchars($row['student_name']); ?>
                    </td>
                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['student_email']); ?></td>
                    <td class="py-3 px-6 text-center"><?php echo date('Y-m-d', strtotime($row['enroll_date'])); ?></td>
                    <td class="py-3 px-6 text-center">
                        <?php 
                                    $status_class = ($row['payment_status'] == 'paid') ? 'bg-green-200 text-green-600 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-200 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300';
                                ?>
                        <span class="py-1 px-3 rounded-full text-xs font-semibold <?php echo $status_class; ?>">
                            <?php echo ucfirst(htmlspecialchars($row['payment_status'])); ?>
                        </span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-green-600 dark:bg-green-500 h-2.5 rounded-full"
                                style="width: <?php echo $row['progress_percentage']; ?>%"></div>
                        </div>
                        <span class="text-xs mt-1 block"><?php echo (int)$row['progress_percentage']; ?>%</span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-500 text-yellow-700 dark:text-yellow-300">
        <p class="font-bold">No Students Enrolled Yet.</p>
        <p>Wait for students to sign up to start tracking their progress.</p>
    </div>
    <?php endif; ?>

    <?php mysqli_close($conn); ?>
</div>
<?php include("../footer.php"); ?>