<?php
session_start();
include("../connection.php"); 

// --- 1. Authentication and Validation ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : null;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';


// Handle the certificate action
if ($action === 'certificate') {

    if (!$course_id || !is_numeric($course_id)) {
        $_SESSION['error'] = "Invalid request.";
        header("Location: ../dashboard.php"); 
        exit;
    }

    // --- 2. Fetch Course Data and Calculate Progress ---

    // 1) Get Total Modules 
    $total_modules_query = "SELECT COUNT(id) AS total_modules 
                            FROM modules 
                            WHERE course_id = '$course_id'";
    $total_modules_result = mysqli_query($conn, $total_modules_query);
    $total_modules = mysqli_fetch_assoc($total_modules_result)['total_modules'];

    // 2) Get Completed Modules 
    $completed_modules_query = "SELECT COUNT(p.id) AS completed_modules
                                FROM progress p
                                JOIN modules m ON p.module_id = m.id
                                WHERE m.course_id = '$course_id' AND p.user_id = '$user_id' AND p.status = 'completed'";
    $completed_modules_result = mysqli_query($conn, $completed_modules_query);
    $completed_modules = mysqli_fetch_assoc($completed_modules_result)['completed_modules'];

    // 3) Calculate completion status
    $is_complete = ($total_modules > 0 && $completed_modules == $total_modules);

    // 4) Fetch Course Title and Lecturer Name (INSECURE)
    $course_data_query = "SELECT c.title AS course_title, u.name AS lecturer_name
                          FROM courses c
                          JOIN users u ON c.lecturer_id = u.id
                          WHERE c.id = '$course_id'";
    $course_data_result = mysqli_query($conn, $course_data_query);
    $course_data = mysqli_fetch_assoc($course_data_result);

    $course_title = htmlspecialchars($course_data['course_title']);
    $lecturer_name = htmlspecialchars($course_data['lecturer_name']);
    $completion_date = date('F j, Y'); // Use current date as completion date

    mysqli_close($conn);
    
    // --- 3. HTML Output ---
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Certificate of Completion</title>
        <link href="../css/tailwind.min.css" rel="stylesheet"> 
        <style>
            /* CSS remains the same as previously drafted */
            .certificate-container {
                width: 100%;
                max-width: 1000px;
                height: 700px;
                margin: 50px auto;
                border: 10px solid gold;
                padding: 50px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
                background-color: #fff;
                background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 100"><text x="50" y="50" font-size="20" font-family="Arial" fill="#f0f0f0" text-anchor="middle" dominant-baseline="central">CERTIFICATE</text></svg>');
                background-repeat: no-repeat;
                background-position: center;
            }
            @media print {
                .certificate-container {
                    margin: 0;
                    border: none;
                    box-shadow: none;
                }
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="certificate-container text-center">
            <?php if (!$is_complete): ?>
                <div class="p-6 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <h2 class="text-3xl font-bold mb-4">Course Not Yet Completed!</h2>
                    <p class="text-lg">You must complete all **<?php echo $total_modules; ?>** modules to receive your certificate. You currently have **<?php echo $completed_modules; ?>** complete.</p>
                    <a href="../course_view.php?id=<?php echo $course_id; ?>" class="mt-4 inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 no-print">
                        Return to Course
                    </a>
                </div>
            <?php else: ?>
                <h1 class="text-5xl font-extrabold text-blue-800 mb-8 mt-10">CERTIFICATE OF COMPLETION</h1>

                <p class="text-2xl text-gray-700 mb-4">This acknowledges that</p>
                
                <h2 class="text-6xl font-serif font-bold text-teal-600 mb-10 border-b-4 border-teal-200 inline-block px-10">
                    <?php echo htmlspecialchars($user_name); ?>
                </h2>

                <p class="text-2xl text-gray-700 mb-4">Has successfully completed the training course</p>
                
                <h3 class="text-4xl font-bold text-gray-800 mb-12">
                    "<?php echo $course_title; ?>"
                </h3>

                <div class="flex justify-around items-center mt-16">
                    <div class="w-1/3">
                        <p class="text-lg font-semibold border-t border-gray-400 pt-2">
                            <?php echo $lecturer_name; ?><br>
                            Instructor Signature
                        </p>
                    </div>
                    <div class="w-1/3">
                        <p class="text-xl font-semibold text-gray-800">Date Completed:</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $completion_date; ?></p>
                    </div>
                </div>

                <button onclick="window.print()" class="no-print mt-12 bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 shadow-xl">
                    <i class="fas fa-print mr-2"></i> Print Certificate
                </button>
            <?php endif; ?>
        </div>
    </body>
    </html>

    <?php
} else {
    // If no specific action is set, redirect to dashboard
    header("Location: ../auth/dashboard.php");
    exit;
}
?>