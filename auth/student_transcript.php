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

// --- Generate Student Transcript ---

// 1. Fetch Enrolled Courses & Progress
$sql = "SELECT 
            c.id as course_id, 
            c.title, 
            u.name as lecturer_name,
            e.enroll_date,
            (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) as total_modules,
            (SELECT COUNT(*) FROM progress p JOIN modules m ON p.module_id = m.id WHERE m.course_id = c.id AND p.user_id = '$user_id' AND p.status = 'completed') as completed_modules,
            (SELECT MAX(qa.attempted_at) FROM quiz_attempts qa JOIN quizzes q ON qa.quiz_id = q.id JOIN modules m ON q.module_id = m.id WHERE m.course_id = c.id AND qa.user_id = '$user_id') as last_activity
        FROM enrollment e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON c.lecturer_id = u.id
        WHERE e.user_id = '$user_id' AND e.payment_status = 'paid'
        ORDER BY e.enroll_date DESC";

$result = mysqli_query($conn, $sql);
$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

// 2. Fetch Quiz Averages per Course
$quiz_scores = [];
foreach ($courses as $course) {
    $c_id = $course['course_id'];
    // Get all quizzes for this course
    $q_sql = "SELECT q.id FROM quizzes q JOIN modules m ON q.module_id = m.id WHERE m.course_id = '$c_id'";
    $q_result = mysqli_query($conn, $q_sql);

    $total_score_sum = 0;
    $quiz_count = 0;

    while ($q_row = mysqli_fetch_assoc($q_result)) {
        $q_id = $q_row['id'];
        // Get max score for this quiz by user
        $s_sql = "SELECT MAX(score) as max_score FROM quiz_attempts WHERE quiz_id = '$q_id' AND user_id = '$user_id'";
        $s_result = mysqli_query($conn, $s_sql);
        $s_row = mysqli_fetch_assoc($s_result);

        if ($s_row['max_score'] !== null) {
            $total_score_sum += $s_row['max_score'];
            $quiz_count++;
        }
    }

    $quiz_scores[$c_id] = ($quiz_count > 0) ? round($total_score_sum / $quiz_count) . '%' : 'N/A';
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress Report</title>
    <link href="../css/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto max-w-4xl p-8 bg-white shadow-xl my-10 min-h-[800px]">

        <!-- Header -->
        <div class="border-b-2 border-gray-200 pb-8 mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-bold text-brand-blue mb-2">Student Progress Report</h1>
                <p class="text-gray-500">Generated on <?php echo date('F j, Y'); ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user_name); ?></h2>
                <p class="text-gray-600">Student ID: #<?php echo str_pad($user_id, 6, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-3 gap-6 mb-10">
            <div class="bg-blue-50 p-6 rounded-xl border border-blue-100">
                <p class="text-blue-600 font-semibold mb-1">Total Courses</p>
                <p class="text-3xl font-bold text-blue-900"><?php echo count($courses); ?></p>
            </div>
            <?php
            $completed_count = 0;
            foreach ($courses as $c) {
                if ($c['total_modules'] > 0 && $c['completed_modules'] == $c['total_modules'])
                    $completed_count++;
            }
            ?>
            <div class="bg-green-50 p-6 rounded-xl border border-green-100">
                <p class="text-green-600 font-semibold mb-1">Completed</p>
                <p class="text-3xl font-bold text-green-900"><?php echo $completed_count; ?></p>
            </div>
            <div class="bg-purple-50 p-6 rounded-xl border border-purple-100">
                <p class="text-purple-600 font-semibold mb-1">Active</p>
                <p class="text-3xl font-bold text-purple-900"><?php echo count($courses) - $completed_count; ?></p>
            </div>
        </div>

        <!-- Course Table -->
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="py-4 font-bold text-gray-600">Course Name</th>
                    <th class="py-4 font-bold text-gray-600">Progress</th>
                    <th class="py-4 font-bold text-gray-600">Avg. Quiz Score</th>
                    <th class="py-4 font-bold text-gray-600">Status</th>
                    <th class="py-4 font-bold text-gray-600">Completion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course):
                    $percent = ($course['total_modules'] > 0) ? round(($course['completed_modules'] / $course['total_modules']) * 100) : 0;
                    $is_done = ($percent == 100);
                    ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-4 pr-4">
                            <div class="font-bold text-gray-800"><?php echo htmlspecialchars($course['title']); ?></div>
                            <div class="text-sm text-gray-500">By <?php echo htmlspecialchars($course['lecturer_name']); ?>
                            </div>
                        </td>
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <span class="text-sm font-semibold"><?php echo $percent; ?>%</span>
                            </div>
                        </td>
                        <td class="py-4 font-medium">
                            <?php echo $quiz_scores[$course['course_id']]; ?>
                        </td>
                        <td class="py-4">
                            <?php if ($is_done): ?>
                                <span
                                    class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Completed</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold">In
                                    Progress</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4">
                            <?php if ($is_done): ?>
                                <i class="fas fa-medal text-xl text-yellow-500" title="Badge Earned"></i>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="mt-20 pt-8 border-t border-gray-200 text-center text-gray-500 text-sm">
            <p>This report is generated automatically by the Learning Management System.</p>
            <p>© <?php echo date('Y'); ?> Education Platform. All rights reserved.</p>
        </div>

        <!-- Print Button -->
        <div class="fixed bottom-8 right-8 no-print">
            <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform hover:scale-105">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
            <a href="dashboard.php"
                class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 px-6 rounded-full shadow-lg flex items-center gap-2 mt-4 transition-transform hover:scale-105 justify-center">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>

</html>