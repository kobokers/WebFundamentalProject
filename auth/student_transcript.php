<?php
session_start();
include("../connection.php");

// --- 1. Authentication ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- 2. Helper Functions ---
function getSemester($dateString)
{
    $time = strtotime($dateString);
    $month = date('n', $time);
    $year = date('Y', $time);
    // Logic: Jan-Jun = Spring, Jul-Dec = Fall
    $term = ($month <= 6) ? "Spring" : "Fall";
    return "$term $year";
}

// --- 3. Fetch User Details ---
$user_sql = "SELECT name, email FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user_data = ($user_result) ? mysqli_fetch_assoc($user_result) : null;

if (!$user_data) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// --- 4. Fetch Real "Join Date" ---
$join_sql = "SELECT MIN(enroll_date) as first_join FROM enrollment WHERE user_id = '$user_id'";
$join_result = mysqli_query($conn, $join_sql);
$join_row = ($join_result) ? mysqli_fetch_assoc($join_result) : null;
$user_data['join_date'] = $join_row['first_join'] ?? date('Y-m-d');

// --- 5. Generate Student Metadata ---
$user_data['student_code'] = "ST-" . str_pad($user_id, 6, '0', STR_PAD_LEFT);

// --- 6. Fetch Admin for Signature ---
$admin_sql = "SELECT name FROM users WHERE role = 'admin' LIMIT 1";
$admin_result = mysqli_query($conn, $admin_sql);
$admin_row = ($admin_result) ? mysqli_fetch_assoc($admin_result) : null;
$signer_name = $admin_row ? $admin_row['name'] : "mcp";

// Signature Font Scaling
$name_len = strlen($signer_name);
$sig_font_size = ($name_len <= 10) ? "3rem" : (($name_len <= 20) ? "2.2rem" : "1.8rem");

// --- 7. Fetch Courses & Calculate Metrics (USER'S LOGIC) ---
$sql = "SELECT 
            c.id as course_id, 
            c.title, 
            u.name as lecturer_name,
            e.enroll_date,
            (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) as total_modules,
            (SELECT COUNT(*) FROM progress p JOIN modules m ON p.module_id = m.id WHERE m.course_id = c.id AND p.user_id = '$user_id' AND p.status = 'completed') as completed_modules
        FROM enrollment e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON c.lecturer_id = u.id
        WHERE e.user_id = '$user_id' AND e.payment_status = 'paid'
        ORDER BY e.enroll_date ASC";

$result = mysqli_query($conn, $sql);
$transcript_data = [];

$completed_courses_count = 0;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $c_id = $row['course_id'];

        // --- Calculate Quiz Average (User's Logic) ---
        $all_quizzes_sql = "SELECT q.id FROM quizzes q JOIN modules m ON q.module_id = m.id WHERE m.course_id = '$c_id'";
        $q_res = mysqli_query($conn, $all_quizzes_sql);
        $q_sum = 0;
        $q_count = 0;

        if ($q_res) {
            while ($q = mysqli_fetch_assoc($q_res)) {
                $qid = $q['id'];
                $attempt_sql = "SELECT MAX(score) as s FROM quiz_attempts WHERE quiz_id='$qid' AND user_id='$user_id'";
                $attempt_res_q = mysqli_query($conn, $attempt_sql);
                $attempt_res = ($attempt_res_q) ? mysqli_fetch_assoc($attempt_res_q) : null;

                if ($attempt_res && $attempt_res['s'] !== null) {
                    $q_sum += $attempt_res['s'];
                    $q_count++;
                }
            }
        }
        $avg_score = ($q_count > 0) ? round($q_sum / $q_count) : 'N/A';

        // --- Map to Friend's Design Fields ---
        $row['course_code'] = "CRS-" . (100 + $row['course_id']);
        $row['semester'] = getSemester($row['enroll_date']);
        $row['quiz_avg'] = ($avg_score !== 'N/A') ? $avg_score . '%' : 'N/A';

        // Status & Progress Calculation
        $percent = ($row['total_modules'] > 0) ? round(($row['completed_modules'] / $row['total_modules']) * 100) : 0;
        $row['progress_percent'] = $percent;
        $row['status'] = ($percent == 100) ? 'Completed' : 'In Progress';

        if ($row['status'] === 'Completed') {
            $completed_courses_count++;
        }

        $transcript_data[] = $row;
    }
}

$total_courses = count($transcript_data);
$active_courses = $total_courses - $completed_courses_count;

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - <?php echo $user_data['student_code']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&family=Great+Vibes&display=swap"
        rel="stylesheet">

    <style>
        body {
            background-color: #525659;
            font-family: 'Inter', sans-serif;
        }

        .paper-sheet {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            margin: 40px auto;
            padding: 20mm;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .serif-font {
            font-family: 'Playfair Display', serif;
        }

        .formal-header {
            font-family: 'Cinzel', serif;
        }

        .transcript-table th {
            border-bottom: 2px solid #000;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding-bottom: 8px;
        }

        .transcript-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 0;
            font-size: 0.9rem;
        }

        .transcript-table tr:last-child td {
            border-bottom: none;
        }

        .signature-text {
            font-family: 'Great Vibes', cursive;
            color: #1e40af;
            line-height: 1.2;
        }

        @media print {
            body {
                background-color: white;
                margin: 0;
            }

            .paper-sheet {
                width: 100%;
                margin: 0;
                box-shadow: none;
                padding: 10mm;
            }

            .no-print {
                display: none !important;
            }

            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>

<body>

    <div class="fixed top-0 left-0 w-full bg-white shadow-md p-4 flex justify-between z-50 no-print">
        <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 font-semibold flex items-center gap-2">
            <i class="fas fa-chevron-left"></i> Back to Dashboard
        </a>
        <button onclick="window.print()"
            class="bg-blue-800 text-white px-6 py-2 rounded shadow hover:bg-blue-900 transition flex items-center gap-2">
            <i class="fas fa-file-pdf"></i> Print / Save as PDF
        </button>
    </div>

    <div class="paper-sheet">

        <div class="flex justify-between items-end border-b-4 border-gray-800 pb-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 bg-blue-900 text-white flex items-center justify-center rounded-full text-3xl">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold uppercase tracking-widest text-gray-900 formal-header">OLMS</h1>
                    <p class="text-gray-600 text-sm">Office of Academic Records</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800 serif-font">Official Transcript</h2>
                <p class="text-sm font-semibold text-gray-500">Date Issued: <?php echo date('F d, Y'); ?></p>
            </div>
        </div>

        <div class="mb-8 text-sm">
            <h3 class="font-bold text-gray-400 uppercase text-xs mb-2 border-b">Student Information</h3>
            <div class="grid grid-cols-2 gap-y-2">
                <div>
                    <span class="font-semibold text-gray-700 block">Name:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($user_data['name']); ?></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700 block">Student ID:</span>
                    <span class="text-gray-900 font-mono"><?php echo $user_data['student_code']; ?></span>
                </div>
                <div class="col-span-2">
                    <span class="font-semibold text-gray-700 block">Email:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($user_data['email']); ?></span>
                </div>
            </div>
        </div>

        <!-- Metric Box: Replacing GPA with Course Counts -->
        <?php if ($total_courses > 0): ?>
            <div class="bg-gray-50 border border-gray-200 rounded p-4 mb-8 flex justify-between items-center">
                <div class="text-center w-1/3 border-r border-gray-300">
                    <p class="text-xs text-gray-500 uppercase">Total Courses</p>
                    <p class="text-2xl font-bold text-blue-900"><?php echo $total_courses; ?></p>
                </div>
                <div class="text-center w-1/3 border-r border-gray-300">
                    <p class="text-xs text-gray-500 uppercase">Completed</p>
                    <p class="text-2xl font-bold text-green-700"><?php echo $completed_courses_count; ?></p>
                </div>
                <div class="text-center w-1/3">
                    <p class="text-xs text-gray-500 uppercase">Active</p>
                    <p class="text-2xl font-bold text-purple-700"><?php echo $active_courses; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-10">
            <h3 class="font-bold text-gray-800 text-lg mb-4 serif-font">Academic Course Record</h3>
            <table class="w-full text-left transcript-table border-collapse">
                <thead>
                    <tr>
                        <th class="w-1/6">Code</th>
                        <th class="w-1/3">Course Title</th>
                        <th class="w-1/6">Semester</th>
                        <th class="w-1/6 text-center">Progress</th>
                        <th class="w-1/12 text-center">Quiz Avg</th>
                        <th class="w-1/12 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_courses > 0): ?>
                        <?php foreach ($transcript_data as $course): ?>
                            <tr>
                                <td class="font-mono text-gray-600 text-xs"><?php echo $course['course_code']; ?></td>
                                <td>
                                    <div class="font-bold text-gray-800"><?php echo htmlspecialchars($course['title']); ?></div>
                                    <div class="text-xs text-gray-500">Instructor:
                                        <?php echo htmlspecialchars($course['lecturer_name']); ?>
                                    </div>
                                </td>
                                <td class="text-sm text-gray-600"><?php echo $course['semester']; ?></td>
                                <td class="text-center">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                        <div class="bg-blue-900 h-1.5 rounded-full"
                                            style="width: <?php echo $course['progress_percent']; ?>%"></div>
                                    </div>
                                    <span
                                        class="text-xs text-gray-500 font-semibold"><?php echo $course['progress_percent']; ?>%</span>
                                </td>
                                <td class="text-center font-bold text-gray-800">
                                    <?php echo $course['quiz_avg']; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($course['status'] === 'Completed'): ?>
                                        <span class="text-xs font-bold text-green-700 uppercase">Passed</span>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-yellow-600 uppercase">Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500 italic">No academic records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>




        <div class="mt-auto pt-10 flex justify-between items-end">

            <div class="w-1/3">
                <div class="relative w-24 h-24 flex items-center justify-center text-yellow-600 mb-4">
                    <i class="fas fa-certificate text-6xl drop-shadow-md relative z-10"></i>
                    <div
                        class="absolute z-20 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-inner">
                        <i class="fas fa-star text-yellow-500 text-lg"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 text-justify leading-relaxed">
                    This document is a computer-generated transcript from the OLMS.
                    It is valid without a physical signature.
                </p>
            </div>

            <div class="text-center w-1/2">
                <div class="pb-2 mb-2 border-b border-gray-400 inline-block w-full">
                    <div class="signature-text transform -rotate-3 whitespace-nowrap"
                        style="font-size: <?php echo $sig_font_size; ?>;">
                        <?php echo htmlspecialchars($signer_name); ?>
                    </div>
                </div>
                <p class="text-xs font-bold uppercase text-gray-600 tracking-wider">System Administrator / Registrar</p>
            </div>
        </div>

        <div class="absolute bottom-5 left-0 w-full text-center">
            <p class="text-[10px] text-gray-300 uppercase tracking-widest">Official Academic Record • Page 1 of 1</p>
        </div>

    </div>
</body>

</html>