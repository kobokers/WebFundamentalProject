<?php
session_start();
include("../connection.php");
include("../header.php");

// --- Authentication ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to take quizzes.";
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id <= 0) {
    $_SESSION['error'] = "Invalid quiz selected.";
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

// Get quiz details
$quiz_query = "SELECT q.*, m.title as module_title, m.course_id, c.title as course_title, c.lecturer_id 
               FROM quizzes q 
               JOIN modules m ON q.module_id = m.id 
               JOIN courses c ON m.course_id = c.id 
               WHERE q.id = '$quiz_id'";
$quiz_result = mysqli_query($conn, $quiz_query);

if (mysqli_num_rows($quiz_result) == 0) {
    $_SESSION['error'] = "Quiz not found.";
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$quiz = mysqli_fetch_assoc($quiz_result);

// Check if student is enrolled (or is admin/lecturer owner)
$is_lecturer = ($_SESSION['user_role'] === 'lecturer' && $_SESSION['user_id'] == $quiz['lecturer_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

if (!$is_lecturer && !$is_admin) {
    $enroll_check = "SELECT * FROM enrollment WHERE user_id = '$user_id' AND course_id = '{$quiz['course_id']}' AND payment_status = 'paid'";
    $enroll_result = mysqli_query($conn, $enroll_check);

    if (mysqli_num_rows($enroll_result) == 0) {
        $_SESSION['error'] = "You must be enrolled in this course to take the quiz.";
        echo "<script>window.location.href = 'dashboard.php';</script>";
        exit;
    }
}

// Get questions
$questions_query = "SELECT * FROM quiz_questions WHERE quiz_id = '$quiz_id' ORDER BY question_order ASC";
$questions_result = mysqli_query($conn, $questions_query);
$questions = [];
while ($q = mysqli_fetch_assoc($questions_result)) {
    $questions[] = $q;
}

$total_questions = count($questions);

// Check previous attempts
$attempts_query = "SELECT * FROM quiz_attempts WHERE quiz_id = '$quiz_id' AND user_id = '$user_id' ORDER BY attempted_at DESC";
$attempts_result = mysqli_query($conn, $attempts_query);
$previous_attempts = [];
while ($a = mysqli_fetch_assoc($attempts_result)) {
    $previous_attempts[] = $a;
}

// --- Handle Quiz Submission ---
$show_results = false;
$score = 0;
$passed = false;
$correct_count = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_quiz'])) {
    
    foreach ($questions as $q) {
        $answer_key = 'answer_' . $q['id'];
        if (isset($_POST[$answer_key]) && strtoupper($_POST[$answer_key]) === strtoupper($q['correct_answer'])) {
            $correct_count++;
        }
    }
    
    $score = ($total_questions > 0) ? round(($correct_count / $total_questions) * 100) : 0;
    $passed = ($score >= $quiz['passing_score']) ? 1 : 0;
    
    // Record attempt
    $record_query = "INSERT INTO quiz_attempts (quiz_id, user_id, score, passed) VALUES ('$quiz_id', '$user_id', '$score', '$passed')";
    mysqli_query($conn, $record_query);
    
    $show_results = true;
}

?>

<!-- Custom Style for Radio Selection -->
<style>
    .quiz-radio:checked + div {
        border-color: #0056D2; /* Coursera Blue */
        background-color: #EFF6FF; /* Blue-50 */
    }
    .dark .quiz-radio:checked + div {
        border-color: #60A5FA; /* Blue-400 */
        background-color: rgba(30, 58, 138, 0.3); /* Blue-900 with opacity */
    }
    .quiz-radio:checked + div .radio-circle {
        border-color: #0056D2;
        border-width: 5px;
    }
    .dark .quiz-radio:checked + div .radio-circle {
        border-color: #60A5FA;
    }
</style>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Quiz Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-[72px] z-40">
        <div class="container mx-auto px-4 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                        <a href="course_view.php?id=<?php echo $quiz['course_id']; ?>" class="hover:text-coursera-blue transition-colors">
                            <i class="fas fa-arrow-left mr-1"></i> <?php echo htmlspecialchars($quiz['course_title']); ?>
                        </a>
                        <span>|</span>
                        <span><?php echo htmlspecialchars($quiz['module_title']); ?></span>
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                </div>
                
                <?php if (!$show_results): ?>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Questions</p>
                        <p class="font-bold text-gray-900 dark:text-white"><?php echo $total_questions; ?></p>
                    </div>
                    <div class="border-l border-gray-300 dark:border-gray-600 h-8"></div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pass Score</p>
                        <p class="font-bold text-green-600 dark:text-green-400"><?php echo $quiz['passing_score']; ?>%</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Result State -->
            <?php if ($show_results): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-8 text-center p-12">
                <div class="mb-6 flex justify-center">
                    <?php if ($passed): ?>
                        <div class="w-24 h-24 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="fas fa-trophy text-5xl text-green-600 dark:text-green-400"></i>
                        </div>
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <i class="fas fa-times text-5xl text-red-600 dark:text-red-400"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    <?php echo $passed ? 'Excellent Work!' : 'Keep Practicing'; ?>
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                    <?php echo $passed 
                        ? "You've successfully passed the quiz. Your score has been recorded." 
                        : "Don't give up! Review the course material and try again to improve your score."; ?>
                </p>

                <div class="flex justify-center gap-8 mb-8">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Your Score</p>
                        <p class="text-4xl font-extrabold <?php echo $passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                            <?php echo $score; ?>%
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Correct Answers</p>
                        <p class="text-4xl font-extrabold text-gray-900 dark:text-white">
                            <?php echo $correct_count; ?><span class="text-lg text-gray-400 font-normal">/<?php echo $total_questions; ?></span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if (!$passed): ?>
                    <a href="take_quiz.php?quiz_id=<?php echo $quiz_id; ?>" 
                       class="inline-flex items-center justify-center gap-2 bg-coursera-blue hover:bg-coursera-blue-dark text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md">
                        <i class="fas fa-redo"></i> Retake Quiz
                    </a>
                    <?php endif; ?>
                    <a href="course_view.php?id=<?php echo $quiz['course_id']; ?>" 
                       class="inline-flex items-center justify-center gap-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-white font-bold py-3 px-8 rounded-xl transition-all">
                        Back to Content
                    </a>
                </div>
            </div>
            <?php else: ?>

            <!-- Previous attempts notice -->
            <?php if (count($previous_attempts) > 0): ?>
                <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-800 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-history text-blue-600 dark:text-blue-300"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Previous Attempt</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Last attempt: <?php echo date('M j, Y', strtotime($previous_attempts[0]['attempted_at'])); ?> 
                                • Score: <?php echo $previous_attempts[0]['score']; ?>%
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quiz Form -->
            <form method="POST" action="take_quiz.php?quiz_id=<?php echo $quiz_id; ?>">
                <input type="hidden" name="submit_quiz" value="1">
                
                <div class="space-y-6">
                    <?php foreach ($questions as $index => $q): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-start gap-3">
                                <span class="flex-shrink-0 w-8 h-8 bg-coursera-blue text-white rounded-lg flex items-center justify-center text-sm font-bold mt-1">
                                    <?php echo $index + 1; ?>
                                </span>
                                <span class="mt-1.5"><?php echo htmlspecialchars($q['question']); ?></span>
                            </h3>
                        </div>
                        
                        <div class="p-6 space-y-3">
                            <?php 
                            $options = [
                                'A' => $q['option_a'], 
                                'B' => $q['option_b'], 
                                'C' => $q['option_c'], 
                                'D' => $q['option_d']
                            ];
                            ?>
                            <?php foreach ($options as $key => $val): ?>
                            <label class="block cursor-pointer group">
                                <input type="radio" name="answer_<?php echo $q['id']; ?>" value="<?php echo $key; ?>" required class="hidden quiz-radio">
                                <div class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-200">
                                    <div class="radio-circle w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-500 flex-shrink-0"></div>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium"><?php echo htmlspecialchars($val); ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white text-lg font-bold py-4 px-12 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all flex items-center gap-3">
                        Submit Assessment <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
include("../footer.php"); 
?>
