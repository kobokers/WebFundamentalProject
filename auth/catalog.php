<?php
session_start();
include("../header.php"); 
include("../connection.php"); 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';

// --- 1. SQL Query to Fetch All Courses and Current Enrollment Status (INSECURE) ---
// LEFT JOIN allows listing all courses even if the current user isn't enrolled.
$catalog_query = "
    SELECT 
        c.id AS course_id,
        c.title AS course_title,
        c.level,
        c.fee,
        u.name AS lecturer_name,
        e.payment_status,
        c.description
    FROM 
        courses c
    JOIN 
        users u ON c.lecturer_id = u.id 
    LEFT JOIN 
        enrollment e ON c.id = e.course_id AND e.user_id = '{$user_id}'
    ORDER BY 
        c.title ASC";

$result = mysqli_query($conn, $catalog_query);
?>

<body>
    <div class="container mx-auto p-8 max-w-6xl">
        <header class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-teal-700">Course Catalog</h1>
            <p class="text-lg text-gray-600">Browse and enroll in our available courses.</p>
        </header>

        <section id="course-list">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php 
                        $is_enrolled = !is_null($row['payment_status']);
                        $is_paid = ($row['payment_status'] === 'paid');
                        $is_pending = ($row['payment_status'] === 'pending');
                    ?>
                    
                    <div class="bg-white border border-gray-200 rounded-xl shadow-lg hover:shadow-xl transition flex flex-col justify-between">
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($row['course_title']); ?></h3>
                            <p class="text-sm text-gray-500 mb-3">Lecturer: <?php echo htmlspecialchars($row['lecturer_name']); ?></p>
                            
                            <div class="flex justify-between text-sm mb-4">
                                <span class="text-indigo-600 font-semibold"><?php echo htmlspecialchars($row['level']); ?></span>
                                <span class="font-bold <?php echo ($row['fee'] > 0) ? 'text-red-500' : 'text-green-500'; ?>">
                                    $<?php echo number_format($row['fee'], 2); ?>
                                </span>
                            </div>

                            <p class="text-gray-600 text-sm italic mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
                        </div>

                        <div class="p-5 pt-0 border-t border-gray-100">
                            <?php if ($user_role === 'guest'): ?>
                                <a href="./login.php" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-blue-600">
                                    Login to Enroll
                                </a>
                            <?php elseif ($user_role !== 'student'): ?>
                                <button disabled class="w-full bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg">
                                    Accessing as Lecturer/Admin
                                </button>
                            <?php elseif ($is_paid): ?>
                                <a href="./course_view.php?id=<?php echo $row['course_id']; ?>" class="w-full bg-green-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-green-600">
                                    Go to Course
                                </a>
                            <?php elseif ($is_pending): ?>
                                <a href="./payment.php?course_id=<?php echo $row['course_id']; ?>" class="w-full bg-red-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-red-600">
                                    Complete Payment
                                </a>
                            <?php else: ?>
                                <a href="./enroll_course.php?course_id=<?php echo $row['course_id']; ?>" class="w-full bg-teal-500 text-white font-bold py-2 px-4 rounded-lg text-center block hover:bg-teal-600">
                                    Enroll Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <div class="mt-8 text-center">
             <a href="./dashboard.php" class="text-gray-500 hover:text-gray-700 font-medium">← Go to Dashboard</a>
        </div>
    </div>
</body>
</html>
<?php include("../footer.php"); ?>