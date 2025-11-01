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

// --- 2. SQL Query to Fetch Enrolled Courses and Progress  ---
// (This section remains unchanged, as the progress calculation is correct)
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
        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-blue-800">Hello, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p class="text-lg text-gray-600">Your Student Dashboard</p>
        </header>

        <hr class="mb-8">

        <section id="enrolled-courses" class="mb-10">
            <h2 class="text-2xl font-semibold mb-4 flex items-center"><i class="fas fa-book-open mr-2"></i> My Learning Path</h2>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white shadow-md rounded-lg">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Course Title</th>
                                <th class="py-3 px-6 text-left">Instructor</th>
                                <th class="py-3 px-6 text-center">Progress</th>
                                <th class="py-3 px-6 text-center">Status</th>
                                <th class="py-3 px-6 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6 text-left whitespace-nowrap font-medium">
                                        <?php echo htmlspecialchars($row['course_title']); ?>
                                    </td>
                                    <td class="py-3 px-6 text-left">
                                        <?php echo htmlspecialchars($row['lecturer_name']); ?>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $row['progress_percentage']; ?>%"></div>
                                        </div>
                                        <span class="text-xs mt-1 block"><?php echo (int)$row['progress_percentage']; ?>% Complete</span>
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
                                        <?php 
                                            $progress = (int)$row['progress_percentage'];
                                            $is_paid = ($row['payment_status'] == 'paid');

                                            // Determine the correct action button
                                            if ($is_paid && $progress === 100) {
                                                // ---  COURSE COMPLETE: SHOW CERTIFICATE LINK ---
                                                // Link to the action file with the certificate parameter
                                                ?>
                                                <a href="../auth/student_actions.php?action=certificate&course_id=<?php echo $row['course_id']; ?>" 
                                                   class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-1 px-3 rounded text-xs transition duration-200">
                                                    View Certificate
                                                </a>
                                                <?php
                                            } elseif ($is_paid) {
                                                // --- COURSE IN PROGRESS: SHOW CONTINUE LINK ---
                                                ?>
                                                <a href="course_view.php?id=<?php echo $row['course_id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded text-xs transition duration-200">
                                                    Continue Course
                                                </a>
                                                <?php
                                            } else {
                                                // --- PENDING PAYMENT: SHOW PAY NOW LINK ---
                                                ?>
                                                <a href="../auth/payment.php?course_id=<?php echo $row['course_id']; ?>" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-xs transition duration-200">
                                                    Pay Now ($<?php echo number_format($row['fee'], 2); ?>)
                                                </a>
                                                <?php
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700">
                    <p class="font-bold">No Courses Enrolled!</p>
                    <p>You haven't signed up for any courses yet. <a href="../auth/catalog.php" class="text-blue-500 hover:text-blue-700 font-medium">Browse our courses</a> to get started.</p>
                </div>
            <?php endif; ?>
        </section>

        <section id="account-actions">
            <h2 class="text-2xl font-semibold mb-4 flex items-center"><i class="fas fa-user-cog mr-2"></i> Account Actions</h2>
            <div class="flex space-x-4">
                <a href="../auth/profile_edit.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition duration-200">Edit Profile</a>
                <a href="../auth/catalog.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition duration-200">Browse Catalog</a>
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-200">Logout</a>
            </div>
        </section>
        
        <?php mysqli_close($conn); ?>

    </div>
</body>
<?php include("../footer.php"); ?>
</html>